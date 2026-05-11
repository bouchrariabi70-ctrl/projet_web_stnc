<?php
//Importe le fichier du modèle Soutenance une seule fois. __DIR__ donne le chemin absolu du dossier courant, ce qui évite les problèmes de chemins relatifs.

require_once __DIR__ . '/../models/soutenance.php';
require_once __DIR__ . '/../models/salle.php';           
require_once __DIR__ . '/../models/configuration.php';
require_once __DIR__ . '/../config/database.php';

class SoutenanceController {
    private $soutenanceModel;
    private $salleModel;        
    private $configModel;
    private PDO  $pdo;
//Instancie le modèle Soutenance et le stocke dans $this->soutenanceModel
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->soutenanceModel = new Soutenance($pdo);
        $this->salleModel      = new salle($pdo);       
        $this->configModel     = new configuration($pdo); 
    }

    private function sendJson($data, int $status = 200): void {
        //fixe le code HTTP 
        http_response_code($status);
        //déclare le Content-Type: application/json
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
//Récupère tous les paramètres $_GET comme filtres et les passe directement au modèle.
    public function index(): void {
           $filters = $_GET;
        $soutenances = $this->soutenanceModel->getAllSoutenances($filters);
        $this->sendJson($soutenances);
    }

//Cherche une soutenance par ID . Si elle n'existe pas, renvoie une erreur 404.
    public function show($id): void {
        //(casté en int pour éviter les injections)
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

        $requiredFields = ['date_soutenance', 'id_salle', 'id_etudiant', 'id_jury'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $this->sendJson(["error" => "Le champ $field est requis"], 400);
                return;
            }
        }

        $apiUrl = "https://api.chaymae.com/checkSalle";
        $query = http_build_query([
            'salle_id' => $data['id_salle'], 
            'date' => $data['date_soutenance'], 
            'heure_debut' => $data['heure_debut'] ?? ''
        ]);
        $response = @file_get_contents($apiUrl . "?" . $query);
        if ($response === false) {
            $this->sendJson(["error" => "Impossible de vérifier la disponibilité de la salle"], 503);
            return;
        }

        $result = json_decode($response, true);
        if (!is_array($result) || !array_key_exists('conflict', $result)) {
            $this->sendJson(["error" => "Réponse de l'API de disponibilité invalide"], 502);
            return;
        }

        if ($result['conflict'] === true) {
            $this->sendJson(["error" => "Salle déjà réservée"], 409);
            return;
        }

        $newSoutenance = $this->soutenanceModel->createSoutenance($data);
        $this->sendJson($newSoutenance, 201);
    }

    // Appelle updateSoutenance() sur le modèle. Retourne une erreur 400 si aucune ligne n'a été modifiée
   public function update($id): void {

    // =========================
    // AFFICHER LE FORMULAIRE
    // =========================
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        $soutenance = $this->soutenanceModel
            ->getSoutenanceById((int) $id);

        if (!$soutenance) {

            echo "Soutenance introuvable";

            return;
        }

        require_once $_SERVER['DOCUMENT_ROOT']
            . '/web/views/soutenance/modifier.php';

        return;
    }

    // =========================
    // TRAITEMENT JSON
    // =========================

    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    if (!is_array($data)) {

        $this->sendJson([
            "error" => "Données JSON invalides"
        ], 400);

        return;
    }

    $requiredFields = [
        'date',
        'id_salle',
        'etudiant_id'
    ];

    foreach ($requiredFields as $field) {

        if (empty($data[$field])) {

            $this->sendJson([
                "error" => "Champ $field requis"
            ], 400);

            return;
        }
    }

    $updated = $this->soutenanceModel
        ->updateSoutenance((int)$id, $data);

    $this->sendJson([
        "success" => $updated,
        "message" => $updated
            ? "Mis à jour OK"
            : "Aucun changement"
    ]);
}

//Supprime la soutenance par ID. Simple délégation au modèle avec gestion de l'échec.
    public function destroy($id): void {
        $deleted = $this->soutenanceModel->deleteSoutenance((int) $id);
       if ($deleted) {
    header("Location: /web/index.php?controller=soutenance&page=list&deleted=1");
    exit;
        } else {
            $this->sendJson(["error" => "Échec de la suppression"], 400);
        }
    }
        // Affiche la liste en HTML
    public function liste(): void {

    $soutenances = $this->soutenanceModel->getAllSoutenances([]);

    require_once $_SERVER['DOCUMENT_ROOT'] . '/web/views/soutenance/liste.php';
}
    // Formulaire HTML
    public function afficherFormulaireAjout(): void {
        // Récupérer les données nécessaires pour le formulaire
        $salles = $this->salleModel->listersalles();

        // Récupérer les étudiants disponibles (sans soutenance) filtrés par filière si spécifiée
        $filiere = $_GET['filiere'] ?? '';
        $etudiants = $this->getEtudiantsDisponibles($filiere);

        // Récupérer tous les professeurs pour encadrants et jury
        $profs = $this->getProfesseursDisponibles();

        $errors = []; // Pour afficher les erreurs de validation

        // Inclure la vue
        require_once __DIR__ . '/../views/soutenance/ajouter.php';
    }

    // Traitement du formulaire
    public function ajouter(): void {
        // Validation des données
        $errors = [];

        if (empty($_POST['etudiant_id'])) {
            $errors[] = "L'étudiant est obligatoire.";
        }

        if (empty($_POST['encadrant_id'])) {
            $errors[] = "L'encadrant est obligatoire.";
        }

        if (empty($_POST['president_id'])) {
            $errors[] = "Le président du jury est obligatoire.";
        }

        if ($_POST['encadrant_id'] == $_POST['president_id']) {
            $errors[] = "L'encadrant et le président du jury ne peuvent pas être la même personne.";
        }

        if (!empty($errors)) {
            // Recharger le formulaire avec les erreurs
            $this->afficherFormulaireAjout();
            return;
        }

        // Préparer les données pour la soutenance
        $data = [
            'date_soutenance' => $_POST['date_soutenance'],
            'heure_debut'     => $_POST['heure_debut'],
            'heure_fin'       => $_POST['heure_fin'],
            'id_salle'        => $_POST['id_salle'],
            'id_etudiant'     => $_POST['etudiant_id']
        ];

        // Créer la soutenance
        $result = $this->soutenanceModel->createSoutenance($data);

        if ($result) {
            $id_soutenance = $result['id_stnc'];

            // Créer les membres du jury
            $this->creerMembresJury($id_soutenance, $_POST['encadrant_id'], $_POST['president_id'], $_POST['jury_members'] ?? []);

            echo "<div class='alert alert-success'>✅ Soutenance planifiée avec succès</div>";
            $this->liste();
        } else {
            echo "<div class='alert alert-danger'>❌ Erreur lors de la planification de la soutenance</div>";
            $this->afficherFormulaireAjout();
        }
    }

    // Méthode pour créer les membres du jury
    private function creerMembresJury(int $id_soutenance, int $encadrant_id, int $president_id, array $jury_members): void {
        // Inclure le modèle jury
        require_once __DIR__ . '/../models/jury.php';
        $juryModel = new jury($this->pdo);

        // Ajouter l'encadrant
        $juryModel->insert($id_soutenance, $encadrant_id, 'encadrant');

        // Ajouter le président
        $juryModel->insert($id_soutenance, $president_id, 'president');

        // Ajouter les autres membres du jury
        foreach ($jury_members as $member_id) {
            if ($member_id != $encadrant_id && $member_id != $president_id) {
                $juryModel->insert($id_soutenance, (int)$member_id, 'membre');
            }
        }
    }

    //CHAYMAE : Partie affectation des salles aux soutenances 
    //fonction pour verefier que le creneau est valide 
    private function estDansCreneauValide(string $heure_debut,string $heure_fin):bool{
        $debut_matin=$this->configModel->getValeurByCle('heure_debut_matin');
        $fin_matin=$this->configModel->getValeurByCle('heure_fin_matin');
        $debut_apres_midi = $this->configModel->getValeurByCle('heure_debut_aprem');
        $fin_apres_midi   = $this->configModel->getValeurByCle('heure_fin_aprem');
        
        $dans_matin=($heure_debut>=$debut_matin && $heure_fin <= $fin_matin);
        $dans_apres_midi=($heure_debut>=$debut_apres_midi && $heure_fin <= $fin_apres_midi);

        return $dans_matin || $dans_apres_midi;
    }
    //fonction retourne si une salle dispo 
    private function salleDisponible(int $id_salle,string $date,string $heure_debut,string $heure_fin , int $id_sout_exclure=0):bool{
        $stmt=$this->pdo->prepare("
            SELECT COUNT(*) FROM soutenance
            WHERE id_salle=:id_salle
            AND date=:date
            AND heure_debut < :heure_fin
            AND heure_fin > :heure_debut
            AND id_stnc != :id_exclure
        ");
        $stmt->execute([
            ':id_salle'    => $id_salle,
            ':date'        => $date,
            ':heure_debut' => $heure_debut,
            ':heure_fin'   => $heure_fin,
            ':id_exclure'  => $id_sout_exclure
        ]);
        return $stmt->fetchColumn()==0;
    }
    //la fct principale qui va affecter les salles aux soutenances 
    public function affecterSalles():array{
        //recuperer les soutenances 
        $soutenances=$this->soutenanceModel->soutenancesSansAffectation();
        //recuperer les salles
        $salles=$this->salleModel->listersalles();
        $conflits=[];


        foreach($soutenances as $sout){
            if(!$this->estDansCreneauValide($sout['heure_debut'],$sout['heure_fin'])){
                $conflits[]=[
                    'soutenance'=>$sout,
                    'raison'=>'heure hors creneau autorise'
                ];
                continue;
            }
            $salle_trouvee=null;
            foreach($salles as $s){
                if($this->salleDisponible($s->getId_salle(),$sout['date'],$sout['heure_debut'],$sout['heure_fin'],$sout['id_stnc'])){
                    $salle_trouvee=$s->getId_salle();
                    break;
                }
            }
            if($salle_trouvee){
                $this->soutenanceModel->affecterSalles($sout['id_stnc'],$salle_trouvee);

            }else{
                $conflits[]=[
                    'soutenance'=>$sout,
                    'raison'=>'aucune salle disponible a ce creneau'
                ];
            }
        }
        return[
            'success'=>true,
            'affectees'=>count($soutenances)-count($conflits),
            'conflits'=>$conflits
        ];
    }
    //CHAYMAE : fin

    // Récupérer les étudiants disponibles (sans soutenance planifiée) filtrés par filière
    private function getEtudiantsDisponibles(string $filiere = ''): array {
        $sql = "SELECT e.* FROM etudiant e
                LEFT JOIN soutenance s ON e.id_etudiant = s.etudiant_id
                WHERE s.etudiant_id IS NULL";

        $params = [];
        if (!empty($filiere)) {
            $sql .= " AND e.filiere = ?";
            $params[] = $filiere;
        }

        $sql .= " ORDER BY e.nom, e.prenom";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer tous les professeurs disponibles
    private function getProfesseursDisponibles(): array {
        $stmt = $this->pdo->query("SELECT * FROM professeur ORDER BY nom, prenom");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
