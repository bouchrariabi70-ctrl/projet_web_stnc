<?php
 
require_once __DIR__ . '/../models/soutenance.php';
require_once __DIR__ . '/../models/salle.php';
require_once __DIR__ . '/../models/configuration.php';
require_once __DIR__ . '/../config/database.php';
 
class SoutenanceController {
    private $soutenanceModel;
    private $salleModel;
    private $configModel;
    private PDO $pdo;
 
    // ---------------------------------------------------------------
    // Cache des configs (évite N requêtes redondantes)
    // ---------------------------------------------------------------
    private array $config = [];
 
    // Calendrier d'occupation en mémoire pour la session d'affectation
    // Structure : $planning[date][id_salle][]   = ['debut'=>'HH:MM','fin'=>'HH:MM']
    //             $planning[date]['prof'][id][]  = ['debut'=>'HH:MM','fin'=>'HH:MM']
    private array $planning = [];
 
    public function __construct(PDO $pdo) {
        $this->pdo            = $pdo;
        $this->soutenanceModel = new Soutenance($pdo);
        $this->salleModel      = new salle($pdo);
        $this->configModel     = new configuration($pdo);
    }
    //fonction d'affichage du formulaire de planification automatique

     public function afficherConfirmationPlanification(): void
{
    require_once __DIR__ . '/../views/soutenance/plannification.php';
}
public function planifierAutomatiquement(): void
{
    $jours = $_POST['jours'] ?? [];
    $duree = (int)($_POST['duree'] ?? 60);

    $rapport = $this->affecterHorairesAutomatiquement($jours, $duree);

    require_once __DIR__ . '/../views/soutenance/resultat_planification.php';
}
 
    // ================================================================
    //  UTILITAIRES JSON / HTTP
    // ================================================================
    private function sendJson($data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
 
    // ================================================================
    //  CRUD DE BASE (inchangé)
    // ================================================================
    public function index(): void {
        $soutenances = $this->soutenanceModel->getAllSoutenances($_GET);
        $this->sendJson($soutenances);
    }
 
    public function show($id): void {
        $soutenance = $this->soutenanceModel->getSoutenanceById((int) $id);
        if ($soutenance) {
            $this->sendJson($soutenance);
        } else {
            $this->sendJson(["error" => "Soutenance introuvable"], 404);
        }
    }
 
    public function store(): void {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!is_array($data)) {
            $this->sendJson(["error" => "Données JSON invalides"], 400);
            return;
        }
 
        $requiredFields = ['date_soutenance', 'id_salle', 'id_etudiant'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $this->sendJson(["error" => "Le champ $field est requis"], 400);
                return;
            }
        }
 
        $newSoutenance = $this->soutenanceModel->createSoutenance($data);
        $this->sendJson($newSoutenance, 201);
    }

public function update($id): void {

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $soutenance = $this->soutenanceModel->getSoutenanceById((int)$id);

        require_once __DIR__ . '/../views/soutenance/modifier.php';
        return;
    }

    $data = $_POST;

    if (empty($data['date']) && empty($data['date_soutenance'])) {
        echo "Champ date manquant";
        return;
    }

    if (empty($data['id_salle']) || empty($data['etudiant_id'])) {
        echo "Champs salle ou étudiant manquants";
        return;
    }

    $updated = $this->soutenanceModel->updateSoutenance((int)$id, $data);

    if ($updated) {
        header("Location: /web/index.php?controller=soutenance&page=liste&updated=1");
        exit;
    }

    echo "Erreur lors de la modification";
}
    public function liste(): void {
        $soutenances = $this->soutenanceModel->getAllSoutenances([]);
        require_once $_SERVER['DOCUMENT_ROOT'] . '/web/views/soutenance/liste.php';
    }
    public function destroy($id): void {

    $deleted = $this->soutenanceModel->deleteSoutenance((int)$id);

    if ($deleted) {
        header("Location: /web/index.php?controller=soutenance&page=liste&deleted=1");
        exit;
    }

    echo "Erreur lors de la suppression";
}
 
    // ================================================================
    //  FORMULAIRES AJOUT / TRAITEMENT
    // ================================================================
    public function afficherFormulaireAjout(): void {
        $salles    = $this->salleModel->listersalles();
        $filiere   = $_GET['filiere'] ?? '';
        $etudiants = $this->getEtudiantsDisponibles($filiere);
        $profs     = $this->getProfesseursDisponibles();
        $errors    = [];
        require_once __DIR__ . '/../views/soutenance/ajouter.php';
    }
 
    public function ajouter(): void {
        $errors = [];
 
        if (empty($_POST['etudiant_id']))   $errors[] = "L'étudiant est obligatoire.";
        if (empty($_POST['encadrant_id']))  $errors[] = "L'encadrant est obligatoire.";
        if (empty($_POST['president_id']))  $errors[] = "Le président du jury est obligatoire.";
        if (!empty($_POST['encadrant_id']) && $_POST['encadrant_id'] == $_POST['president_id'])
            $errors[] = "L'encadrant et le président du jury ne peuvent pas être la même personne.";
 
        if (!empty($errors)) { $this->afficherFormulaireAjout(); return; }
 
        $data = [
            'date_soutenance' => $_POST['date_soutenance'],
            'heure_debut'     => $_POST['heure_debut'],
            'heure_fin'       => $_POST['heure_fin'],
            'id_salle'        => $_POST['id_salle'],
            'id_etudiant'     => $_POST['etudiant_id'],
        ];
 
        $result = $this->soutenanceModel->createSoutenance($data);
        if ($result) {
            $this->creerMembresJury(
                $result['id_stnc'],
                $_POST['encadrant_id'],
                $_POST['president_id'],
                $_POST['jury_members'] ?? []
            );
            echo "<div class='alert alert-success'>✅ Soutenance planifiée avec succès</div>";
            $this->liste();
        } else {
            echo "<div class='alert alert-danger'>❌ Erreur lors de la planification</div>";
            $this->afficherFormulaireAjout();
        }
    }
 
    private function creerMembresJury(int $id_soutenance, int $encadrant_id, int $president_id, array $jury_members): void {
        require_once __DIR__ . '/../models/jury.php';
        $juryModel = new jury($this->pdo);
        $juryModel->insert($id_soutenance, $encadrant_id, 'encadrant');
        $juryModel->insert($id_soutenance, $president_id, 'president');
        foreach ($jury_members as $member_id) {
            if ($member_id != $encadrant_id && $member_id != $president_id) {
                $juryModel->insert($id_soutenance, (int) $member_id, 'membre');
            }
        }
    }
 
    // ================================================================
    //  HELPERS INTERNES
    // ================================================================
    private function getEtudiantsDisponibles(string $filiere = ''): array {
        $sql = "SELECT e.* FROM etudiant e
                LEFT JOIN soutenance s ON e.id_etudiant = s.etudiant_id
                WHERE s.etudiant_id IS NULL";
        $params = [];
        if (!empty($filiere)) { $sql .= " AND e.filiere = ?"; $params[] = $filiere; }
        $sql .= " ORDER BY e.nom, e.prenom";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
 
    private function getProfesseursDisponibles(): array {
        $stmt = $this->pdo->query("SELECT * FROM professeur ORDER BY nom, prenom");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
 
    // ================================================================
    //  CHARGEMENT CONFIG (avec cache)
    // ================================================================
    private function chargerConfig(): void {
        if (!empty($this->config)) return;
 
        $cles = [
            'heure_debut_matin', 'heure_fin_matin',
            'heure_debut_aprem', 'heure_fin_aprem',
            'seuil_min_encadrement', 'seuil_max_encadrement',
            'repos_min_entre_soutenances',
        ];
 
        foreach ($cles as $cle) {
            $this->config[$cle] = $this->configModel->getValeurByCle($cle);
        }
    }
 
    // ================================================================
    //  VÉRIFICATIONS CRÉNEAU / DISPONIBILITÉ  (outils internes)
    // ================================================================
 
    /**
     * Vérifie qu'un créneau tombe dans la matinée ou l'après-midi configurée.
     */
    private function estDansCreneauValide(string $heure_debut, string $heure_fin): bool {
        $c = $this->config;
        $dans_matin      = ($heure_debut >= $c['heure_debut_matin'] && $heure_fin <= $c['heure_fin_matin']);
        $dans_apres_midi = ($heure_debut >= $c['heure_debut_aprem'] && $heure_fin <= $c['heure_fin_aprem']);
        return $dans_matin || $dans_apres_midi;
    }
 
    /**
     * Deux intervalles [a1,a2[ et [b1,b2[ se chevauchent-ils ?
     */
    private function seChevauche(string $debut1, string $fin1, string $debut2, string $fin2): bool {
        return $debut1 < $fin2 && $fin1 > $debut2;
    }
 
    /**
     * Ajoute N minutes à une heure "HH:MM".
     */
    private function ajouterMinutes(string $heure, int $minutes): string {
        [$h, $m] = explode(':', $heure);
        $total = (int)$h * 60 + (int)$m + $minutes;
        return sprintf('%02d:%02d', intdiv($total, 60), $total % 60);
    }
 
    /**
     * Différence en minutes entre deux heures "HH:MM".
     */
    private function diffMinutes(string $debut, string $fin): int {
        [$h1, $m1] = explode(':', $debut);
        [$h2, $m2] = explode(':', $fin);
        return ((int)$h2 * 60 + (int)$m2) - ((int)$h1 * 60 + (int)$m1);
    }
 
    // ================================================================
    //  SALLE DISPONIBLE  (planning en mémoire + BD pour les anciennes)
    // ================================================================
    private function salleDisponible(int $id_salle, string $date, string $heure_debut, string $heure_fin, int $id_sout_exclure = 0): bool {
        // Vérif en mémoire (affectations de cette session)
        foreach ($this->planning[$date]['salle'][$id_salle] ?? [] as $occ) {
            if ($this->seChevauche($heure_debut, $heure_fin, $occ['debut'], $occ['fin'])) return false;
        }
 
        // Vérif en base (affectations existantes)
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM soutenance
            WHERE id_salle    = :id_salle
              AND date         = :date
              AND heure_debut  < :heure_fin
              AND heure_fin    > :heure_debut
              AND id_stnc     != :id_exclure
        ");
        $stmt->execute([
            ':id_salle'    => $id_salle,
            ':date'        => $date,
            ':heure_debut' => $heure_debut,
            ':heure_fin'   => $heure_fin,
            ':id_exclure'  => $id_sout_exclure,
        ]);
        return (int)$stmt->fetchColumn() === 0;
    }
 
    // ================================================================
    //  PROF DISPONIBLE  (repos + planning mémoire + BD)
    // ================================================================
 
    /**
     * Retourne true si le prof n'a aucun conflit (ni chevauchement,
     * ni trop proche de sa soutenance précédente/suivante).
     */
    private function profDisponible(int $id_prof, string $date, string $heure_debut, string $heure_fin): bool {
        $repos = (int)$this->config['repos_min_entre_soutenances']; // ex. 60 min
 
        // --- Planning mémoire ---
        foreach ($this->planning[$date]['prof'][$id_prof] ?? [] as $occ) {
            // chevauchement strict
            if ($this->seChevauche($heure_debut, $heure_fin, $occ['debut'], $occ['fin'])) return false;
 
            // trop proche (moins de $repos minutes de pause)
            $gap1 = $this->diffMinutes($occ['fin'],  $heure_debut); // écart après l'occ existante
            $gap2 = $this->diffMinutes($heure_fin,   $occ['debut']); // écart avant l'occ existante
 
            if ($gap1 >= 0 && $gap1 < $repos) return false; // nouveau créneau trop proche après
            if ($gap2 >= 0 && $gap2 < $repos) return false; // nouveau créneau trop proche avant
        }
 
        // --- BD (soutenances déjà enregistrées) ---
        // On récupère toutes les soutenances du prof ce jour-là
        $stmt = $this->pdo->prepare("
            SELECT s.heure_debut, s.heure_fin
            FROM soutenance s
            INNER JOIN jury j ON j.id_soutenance = s.id_stnc
            WHERE j.id_prof = :id_prof
              AND s.date    = :date
        ");
        $stmt->execute([':id_prof' => $id_prof, ':date' => $date]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $occ) {
            if ($this->seChevauche($heure_debut, $heure_fin, $occ['heure_debut'], $occ['heure_fin'])) return false;
 
            $gap1 = $this->diffMinutes($occ['heure_fin'],  $heure_debut);
            $gap2 = $this->diffMinutes($heure_fin,          $occ['heure_debut']);
            if ($gap1 >= 0 && $gap1 < $repos) return false;
            if ($gap2 >= 0 && $gap2 < $repos) return false;
        }
 
        return true;
    }
 
    // ================================================================
    //  CHARGE D'UN PROF sur un jour donné (nombre de soutenances)
    // ================================================================
    private function chargeProf(int $id_prof, string $date): int {
        $enMemoire = count($this->planning[$date]['prof'][$id_prof] ?? []);
 
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM soutenance s
            INNER JOIN jury j ON j.id_soutenance = s.id_stnc
            WHERE j.id_prof = :id_prof AND s.date = :date
        ");
        $stmt->execute([':id_prof' => $id_prof, ':date' => $date]);
 
        return $enMemoire + (int)$stmt->fetchColumn();
    }
 
    // ================================================================
    //  GÉNÉRATION DES CRÉNEAUX LIBRES DANS UNE JOURNÉE
    // ================================================================
 
    /**
     * Génère tous les créneaux possibles (pas de X minutes) pour une journée,
     * dans les plages matin/après-midi configurées.
     *
     * @param int $duree_minutes  Durée d'une soutenance en minutes
     * @param int $pas_minutes    Pas de recherche (ex. 30 min)
     * @return array              Liste de ['debut'=>..., 'fin'=>...]
     */
    private function generCreneaux(int $duree_minutes, int $pas_minutes = 30): array {
        $c = $this->config;
        $plages = [
            [$c['heure_debut_matin'],  $c['heure_fin_matin']],
            [$c['heure_debut_aprem'],  $c['heure_fin_aprem']],
        ];
 
        $creneaux = [];
        foreach ($plages as [$debut_plage, $fin_plage]) {
            $courant = $debut_plage;
            while (true) {
                $fin_creneau = $this->ajouterMinutes($courant, $duree_minutes);
                if ($fin_creneau > $fin_plage) break;
                $creneaux[] = ['debut' => $courant, 'fin' => $fin_creneau];
                $courant = $this->ajouterMinutes($courant, $pas_minutes);
            }
        }
        return $creneaux;
    }
 
    // ================================================================
    //  ENREGISTREMENT D'UNE AFFECTATION EN MÉMOIRE
    // ================================================================
    private function enregistrerPlanningMemoire(string $date, int $id_salle, string $debut, string $fin, array $id_profs): void {
        $this->planning[$date]['salle'][$id_salle][] = ['debut' => $debut, 'fin' => $fin];
        foreach ($id_profs as $id_prof) {
            $this->planning[$date]['prof'][$id_prof][] = ['debut' => $debut, 'fin' => $fin];
        }
    }
 
    // ================================================================
    //  RÉCUPÉRATION DES MEMBRES DU JURY D'UNE SOUTENANCE
    // ================================================================
    private function getMembresJury(int $id_stnc): array {
        $stmt = $this->pdo->prepare("
            SELECT id_prof, role FROM jury WHERE id_soutenance = :id
        ");
        $stmt->execute([':id' => $id_stnc]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
 
    // ================================================================
    //  RÉPARTITION ÉQUITABLE DES SOUTENANCES SUR 3 JOURS PAR FILIÈRE
    // ================================================================
 
    /**
     * Retourne toutes les soutenances sans horaire affecté,
     * groupées par filière et réparties équitablement sur $jours.
     *
     * @param  array  $jours     ex. ['2025-06-02','2025-06-03','2025-06-04']
     * @return array             [['soutenance'=>..., 'jour'=>'YYYY-MM-DD'], ...]
     */
   private function repartirParFiliere(array $jours): array {

    // 🔥 récupérer les soutenances existantes non planifiées qui ont des membres de jury
    $stmt = $this->pdo->query("
        SELECT DISTINCT s.id_stnc, s.etudiant_id, e.filiere
        FROM soutenance s
        JOIN etudiant e ON e.id_etudiant = s.etudiant_id
        JOIN jury j ON j.id_soutenance = s.id_stnc
        WHERE (s.date IS NULL OR s.id_salle IS NULL)
        ORDER BY e.filiere, s.id_stnc
    ");

    $soutenances = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // regroupement par filière
    $parFiliere = [];
    foreach ($soutenances as $s) {
        $parFiliere[$s['filiere']][] = $s;
    }

    $repartition = [];

    foreach ($parFiliere as $liste) {

        $nbJours = count($jours);
        $total   = count($liste);

        $base  = intdiv($total, $nbJours);
        $reste = $total % $nbJours;

        $quotas = [];
        for ($i = 0; $i < $nbJours; $i++) {
            $quotas[$i] = $base + ($i < $reste ? 1 : 0);
        }

        $idx = 0;
        $j = 0;

        foreach ($jours as $jour) {

            for ($k = 0; $k < ($quotas[$j] ?? 0); $k++) {

                if (!isset($liste[$idx])) break;

                $repartition[] = [
                    'soutenance' => $liste[$idx],
                    'jour' => $jour
                ];

                $idx++;
            }

            $j++;
        }
    }

    return $repartition;
}
 
    // ================================================================
    //  ALGORITHME PRINCIPAL D'AFFECTATION AUTOMATIQUE DES HORAIRES
    // ================================================================
 
    /**
     * Affecte automatiquement un horaire à chaque soutenance en tenant compte :
     *   1. Disponibilité des salles
     *   2. Disponibilité du jury (encadrant + président + membres)
     *   3. Temps de repos minimum entre deux soutenances pour un prof (config)
     *   4. Charge max par prof par jour (seuil_max_encadrement)
     *   5. Répartition équitable par filière sur 3 jours
     *
     * @param  array  $jours         Tableau de 3 dates 'YYYY-MM-DD'
     * @param  int    $duree_minutes Durée d'une soutenance en minutes (défaut 60)
     * @return array  Rapport {success, affectees, non_affectees, conflits}
     */
    public function affecterHorairesAutomatiquement(array $jours, int $duree_minutes = 60): array {
        $this->chargerConfig();
        $this->planning = []; // reset du planning mémoire
 
        if (count($jours) !== 3) {
            return ['success' => false, 'error' => '3 jours sont requis.'];
        }
 
        $salles      = $this->salleModel->listersalles();
        $creneaux    = $this->generCreneaux($duree_minutes);
        $repartition = $this->repartirParFiliere($jours);
 
        $rapport = [
            'success'       => true,
            'affectees'     => 0,
            'non_affectees' => 0,
            'conflits'      => [],
            'detail'        => [],
        ];
 
        $seuil_max = (int)$this->config['seuil_max_encadrement'];
 
        foreach ($repartition as $item) {
            $sout = $item['soutenance'];
            $jour = $item['jour'];
            $id   = (int)$sout['id_stnc'];
 
            $membres = $this->getMembresJury($id);
            $id_profs = array_column($membres, 'id_prof');
 
            // Vérifier qu'il y a des membres de jury
            if (empty($id_profs)) {
                $rapport['non_affectees']++;
                $rapport['conflits'][] = [
                    'id_stnc' => $id,
                    'filiere' => $sout['filiere'],
                    'jour' => $jour,
                    'raison' => 'Aucun membre de jury assigné',
                ];
                continue;
            }

            $affecte = false;

            foreach ($salles as $salle) {
                $id_salle = $salle->getId_salle();

                foreach ($creneaux as $creneau) {
                    $debut = $creneau['debut'];
                    $fin   = $creneau['fin'];
 
                    // 1. Créneau dans les plages autorisées
                    if (!$this->estDansCreneauValide($debut, $fin)) continue;
 
                    // 2. Salle disponible
                    if (!$this->salleDisponible($id_salle, $jour, $debut, $fin, $id)) continue;
 
                    // 3. Disponibilité ET charge de chaque prof du jury
                    $profsOk = true;
                    foreach ($id_profs as $id_prof) {
                        if (!$this->profDisponible($id_prof, $jour, $debut, $fin)) {
                            $profsOk = false; break;
                        }
                        // Vérification charge max par jour
                        if ($this->chargeProf($id_prof, $jour) >= $seuil_max) {
                            $profsOk = false; break;
                        }
                    }
                    if (!$profsOk) continue;
 
                    // ✅ Créneau valide trouvé → on affecte le planning en base
                    $this->soutenanceModel->affecterPlanning(
                        $id,
                        $id_salle,
                        $jour,
                        $debut,
                        $fin,
                        'planifiée'
                    );
 
                    // Mémorisation pour les prochaines itérations
                    $this->enregistrerPlanningMemoire($jour, $id_salle, $debut, $fin, $id_profs);
 
                    $rapport['affectees']++;
                    $rapport['detail'][] = [
                        'id_stnc'   => $id,
                        'filiere'   => $sout['filiere'],
                        'jour'      => $jour,
                        'debut'     => $debut,
                        'fin'       => $fin,
                        'id_salle'  => $id_salle,
                        'profs'     => $id_profs,
                    ];
                    $affecte = true;
                    break 2; // on sort des deux foreach (salles + créneaux)
                }
            }
 
            if (!$affecte) {
                $rapport['non_affectees']++;
                $rapport['conflits'][] = [
                    'id_stnc' => $id,
                    'filiere' => $sout['filiere'],
                    'jour'    => $jour,
                    'raison'  => 'Aucun créneau compatible trouvé (salle, jury, repos, charge)',
                ];
            }
        }
 
        return $rapport;
    }
 
    // ================================================================
    //  ANCIENNE MÉTHODE (affectation salle seule, conservée)
    // ================================================================
    public function affecterSalles(): array {
        $this->chargerConfig();
        $soutenances = $this->soutenanceModel->soutenancesSansAffectation();
        $salles      = $this->salleModel->listersalles();
        $conflits    = [];
 
        foreach ($soutenances as $sout) {
            if (!$this->estDansCreneauValide($sout['heure_debut'], $sout['heure_fin'])) {
                $conflits[] = ['soutenance' => $sout, 'raison' => 'heure hors créneau autorisé'];
                continue;
            }
            $salle_trouvee = null;
            foreach ($salles as $s) {
                if ($this->salleDisponible($s->getId_salle(), $sout['date'], $sout['heure_debut'], $sout['heure_fin'], $sout['id_stnc'])) {
                    $salle_trouvee = $s->getId_salle();
                    break;
                }
            }
            if ($salle_trouvee) {
                $this->soutenanceModel->affecterSalles($sout['id_stnc'], $salle_trouvee);
            } else {
                $conflits[] = ['soutenance' => $sout, 'raison' => 'aucune salle disponible à ce créneau'];
            }
        }
 
        return [
            'success'  => true,
            'affectees' => count($soutenances) - count($conflits),
            'conflits'  => $conflits,
        ];
    }

    // ================================================================
    //  POINT D'ENTRÉE HTTP POUR L'AFFECTATION AUTOMATIQUE
    //  POST /soutenance/planifier-auto
    //  Body JSON : { "jours": ["2025-06-02","2025-06-03","2025-06-04"], "duree": 60 }
    // ================================================================
    public function planifierAuto(): void {
        $data = json_decode(file_get_contents("php://input"), true);
 
        if (!isset($data['jours']) || !is_array($data['jours']) || count($data['jours']) !== 3) {
            $this->sendJson(["error" => "Fournissez exactement 3 dates dans le champ 'jours'."], 400);
            return;
        }
 
        // Validation format date
        foreach ($data['jours'] as $j) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $j)) {
                $this->sendJson(["error" => "Format de date invalide : $j (attendu YYYY-MM-DD)"], 400);
                return;
            }
        }
 
        $duree   = isset($data['duree']) ? (int)$data['duree'] : 60;
        $rapport = $this->affecterHorairesAutomatiquement($data['jours'], $duree);
 
        $status = ($rapport['non_affectees'] === 0) ? 200 : 207; // 207 = partial success
        $this->sendJson($rapport, $status);
    }
}
?>