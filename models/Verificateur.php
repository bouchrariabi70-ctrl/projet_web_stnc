<?php
require_once __DIR__ . '/../models/configuration.php';

/**
 * Verificateur
 * Vérifie la conformité des fichiers d'affectation et de planning.
 *
 * Principe OCP :
 *   - Chaque règle est une méthode privée indépendante.
 *   - Pour ajouter une règle → ajouter une méthode et l'appeler
 *     dans verifierAffectation() ou verifierPlanning().
 *   - Aucune méthode existante n'est modifiée.
 */
class Verificateur {
    private PDO    $pdo;
    private configuration $config;

    // Seuils de répartition équitable (par défaut : 3–4 étudiants/prof)
    private float $seuilMin;
    private float $seuilMax;

    // Pause minimale entre deux soutenances d'un même prof (secondes)
    private int $pauseMinSecondes = 3600; // 1 heure

    public function __construct(PDO $pdo) {
        $this->pdo    = $pdo;
        $this->config = new configuration($pdo);

        // Lecture des seuils depuis la configuration (ou valeurs par défaut)
        $this->seuilMin = (float) ($this->config->getValeurByCle('seuil_min_encadrement') ?? 3);
        $this->seuilMax = (float) ($this->config->getValeurByCle('seuil_max_encadrement') ?? 4);
    }

    // ══════════════════════════════════════════════════════════
    //  VÉRIFICATION DU FICHIER D'AFFECTATION
    // ══════════════════════════════════════════════════════════

    /**
     * Lance toutes les vérifications d'affectation.
     * @return array ['ok' => bool, 'alertes' => string[]]
     */
    public function verifierAffectation(): array {
        $alertes = array_merge(
            $this->verifierRepartitionEquitable(),
            $this->verifierEtudiantsSansProf()
        );
        return ['ok' => empty($alertes), 'alertes' => $alertes];
    }

    // ──────────────────────────────────────────────────────────
    // Règle A1 : Répartition équitable des étudiants par prof
    // ──────────────────────────────────────────────────────────
    private function verifierRepartitionEquitable(): array {
        $alertes = [];
        $stmt = $this->pdo->query("
            SELECT p.id_prof, p.nom, p.prenom, COUNT(e.id_etudiant) AS nb
            FROM professeur p
            LEFT JOIN etudiant e ON e.id_prof = p.id_prof
            GROUP BY p.id_prof, p.nom, p.prenom
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $nb = (int) $row['nb'];
            if ($nb < $this->seuilMin) {
                $alertes[] = "⚠️ Prof {$row['prenom']} {$row['nom']} encadre seulement $nb étudiant(s) "
                           . "(seuil min : {$this->seuilMin}).";
            } elseif ($nb > $this->seuilMax) {
                $alertes[] = "⚠️ Prof {$row['prenom']} {$row['nom']} encadre $nb étudiants "
                           . "(seuil max : {$this->seuilMax}).";
            }
        }
        return $alertes;
    }

    // ──────────────────────────────────────────────────────────
    // Règle A2 : Étudiants sans professeur assigné
    // ──────────────────────────────────────────────────────────
    private function verifierEtudiantsSansProf(): array {
        $nb = (int) $this->pdo->query(
            "SELECT COUNT(*) FROM etudiant WHERE id_prof IS NULL"
        )->fetchColumn();

        if ($nb > 0) {
            return ["❌ $nb étudiant(s) n'ont pas de professeur encadrant assigné."];
        }
        return [];
    }

    // ══════════════════════════════════════════════════════════
    //  VÉRIFICATION DU FICHIER DE PLANNING
    // ══════════════════════════════════════════════════════════

    /**
     * Lance toutes les vérifications du planning.
     * @return array ['ok' => bool, 'alertes' => string[]]
     */
    public function verifierPlanning(): array {
        $alertes = array_merge(
            $this->verifierChevauchementSalles(),
            $this->verifierPauseProf(),
            $this->verifierConflit2SoutenancesMemeProfMemeHeure(),
            $this->verifierCreneauxHorsPlage(),
            $this->verifierSoutenancesSansSalle()
        );
        return ['ok' => empty($alertes), 'alertes' => $alertes];
    }

    // ──────────────────────────────────────────────────────────
    // Règle P1 : Chevauchement de salles
    // ──────────────────────────────────────────────────────────
    private function verifierChevauchementSalles(): array {
        $alertes = [];
        $stmt = $this->pdo->query("
            SELECT s1.id_stnc AS id1, s2.id_stnc AS id2,
                   s1.date, s1.id_salle,
                   s1.heure_debut AS h1d, s1.heure_fin AS h1f,
                   s2.heure_debut AS h2d, s2.heure_fin AS h2f,
                   sa.numero_salle
            FROM soutenance s1
            JOIN soutenance s2
                ON s1.id_salle   = s2.id_salle
               AND s1.date       = s2.date
               AND s1.id_stnc    < s2.id_stnc
               AND s1.heure_debut < s2.heure_fin
               AND s1.heure_fin   > s2.heure_debut
            JOIN salle sa ON sa.id_salle = s1.id_salle
        ");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $alertes[] = "❌ Chevauchement de salle « {$row['numero_salle']} » le {$row['date']} : "
                       . "soutenance #{$row['id1']} ({$row['h1d']}–{$row['h1f']}) "
                       . "et #{$row['id2']} ({$row['h2d']}–{$row['h2f']}).";
        }
        return $alertes;
    }

    // ──────────────────────────────────────────────────────────
    // Règle P2 : Pause minimale d'1 h entre deux soutenances
    //            d'un même professeur
    // ──────────────────────────────────────────────────────────
    private function verifierPauseProf(): array {
        $alertes = [];
        $stmt = $this->pdo->query("
            SELECT j1.id_prof,
                   p.nom, p.prenom,
                   s1.date,
                   s1.heure_fin   AS fin1,
                   s2.heure_debut AS debut2,
                   s1.id_stnc AS id1,
                   s2.id_stnc AS id2
            FROM jury j1
            JOIN jury j2
                ON j1.id_prof        = j2.id_prof
               AND j1.id_soutenance  < j2.id_soutenance
            JOIN soutenance s1 ON s1.id_stnc = j1.id_soutenance
            JOIN soutenance s2 ON s2.id_stnc = j2.id_soutenance
            JOIN professeur p  ON p.id_prof  = j1.id_prof
            WHERE s1.date = s2.date
              AND TIMESTAMPDIFF(SECOND,
                    CONCAT(s1.date,' ',s1.heure_fin),
                    CONCAT(s2.date,' ',s2.heure_debut)
                  ) BETWEEN 0 AND :pause
        ");
        $stmt->execute([':pause' => $this->pauseMinSecondes - 1]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $pauseMinutes = $this->pauseMinSecondes / 60;
            $alertes[] = "⚠️ Prof {$row['prenom']} {$row['nom']} : pause insuffisante le {$row['date']} "
                       . "entre soutenance #{$row['id1']} (fin {$row['fin1']}) "
                       . "et #{$row['id2']} (début {$row['debut2']}) "
                       . "— minimum requis : {$pauseMinutes} min.";
        }
        return $alertes;
    }

    // ──────────────────────────────────────────────────────────
    // Règle P3 : Un prof dans deux soutenances au même horaire
    // ──────────────────────────────────────────────────────────
    private function verifierConflit2SoutenancesMemeProfMemeHeure(): array {
        $alertes = [];
        $stmt = $this->pdo->query("
            SELECT j1.id_prof, p.nom, p.prenom,
                   s1.date,
                   s1.id_stnc AS id1, s2.id_stnc AS id2,
                   s1.heure_debut AS h1d, s1.heure_fin AS h1f,
                   s2.heure_debut AS h2d, s2.heure_fin AS h2f
            FROM jury j1
            JOIN jury j2
                ON j1.id_prof        = j2.id_prof
               AND j1.id_soutenance  < j2.id_soutenance
            JOIN soutenance s1 ON s1.id_stnc = j1.id_soutenance
            JOIN soutenance s2 ON s2.id_stnc = j2.id_soutenance
            JOIN professeur p  ON p.id_prof  = j1.id_prof
            WHERE s1.date       = s2.date
              AND s1.heure_debut < s2.heure_fin
              AND s1.heure_fin   > s2.heure_debut
        ");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $alertes[] = "❌ Prof {$row['prenom']} {$row['nom']} affecté à deux soutenances simultanées "
                       . "le {$row['date']} : #{$row['id1']} ({$row['h1d']}–{$row['h1f']}) "
                       . "et #{$row['id2']} ({$row['h2d']}–{$row['h2f']}).";
        }
        return $alertes;
    }

    // ──────────────────────────────────────────────────────────
    // Règle P4 : Soutenance hors plage horaire autorisée
    // ──────────────────────────────────────────────────────────
    private function verifierCreneauxHorsPlage(): array {
        $alertes = [];

        $debutMatin   = $this->config->getValeurByCle('heure_debut_matin')  ?? '09:00:00';
        $finMatin     = $this->config->getValeurByCle('heure_fin_matin')    ?? '12:00:00';
        $debutAprem   = $this->config->getValeurByCle('heure_debut_aprem')  ?? '14:00:00';
        $finAprem     = $this->config->getValeurByCle('heure_fin_aprem')    ?? '18:00:00';

        $stmt = $this->pdo->query("SELECT id_stnc, date, heure_debut, heure_fin FROM soutenance");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $s) {
            $hd = $s['heure_debut'];
            $hf = $s['heure_fin'];

            $dansMatin  = ($hd >= $debutMatin  && $hf <= $finMatin);
            $dansAprem  = ($hd >= $debutAprem  && $hf <= $finAprem);

            if (!$dansMatin && !$dansAprem) {
                $alertes[] = "⚠️ Soutenance #{$s['id_stnc']} le {$s['date']} "
                           . "({$hd}–{$hf}) est hors des créneaux autorisés "
                           . "(matin {$debutMatin}–{$finMatin}, après-midi {$debutAprem}–{$finAprem}).";
            }
        }
        return $alertes;
    }

    // ──────────────────────────────────────────────────────────
    // Règle P5 : Soutenances sans salle affectée
    // ──────────────────────────────────────────────────────────
    private function verifierSoutenancesSansSalle(): array {
        $nb = (int) $this->pdo->query(
            "SELECT COUNT(*) FROM soutenance WHERE id_salle IS NULL"
        )->fetchColumn();

        if ($nb > 0) {
            return ["❌ $nb soutenance(s) n'ont pas de salle affectée."];
        }
        return [];
    }
}
?>

