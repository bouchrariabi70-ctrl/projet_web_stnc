<?php
/**
 * Modèle Dashboard
 * Fournit toutes les statistiques pour le tableau de bord.
 * Ouvert à l'extension (nouvelles méthodes) — fermé à la modification.
 */
class Dashboard {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // ──────────────────────────────────────────────────────────
    // 1. Nombre d'étudiants encadrés par professeur
    // ──────────────────────────────────────────────────────────
    public function etudiantsParProf(): array {
        $stmt = $this->pdo->query("
            SELECT p.nom, p.prenom, COUNT(e.id_etudiant) AS nb_etudiants
            FROM professeur p
            LEFT JOIN etudiant e ON e.id_prof = p.id_prof
            GROUP BY p.id_prof, p.nom, p.prenom
            ORDER BY nb_etudiants DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ──────────────────────────────────────────────────────────
    // 2. Nombre de soutenances par professeur (membre jury)
    // ──────────────────────────────────────────────────────────
    public function soutenancesParProf(): array {
        $stmt = $this->pdo->query("
            SELECT p.nom, p.prenom, COUNT(j.id_jury) AS nb_soutenances
            FROM professeur p
            LEFT JOIN jury j ON j.id_prof = p.id_prof
            GROUP BY p.id_prof, p.nom, p.prenom
            ORDER BY nb_soutenances DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ──────────────────────────────────────────────────────────
    // 3. Nombre de soutenances par filière
    // ──────────────────────────────────────────────────────────
    public function soutenancesParFiliere(): array {
        $stmt = $this->pdo->query("
            SELECT e.filiere, COUNT(s.id_stnc) AS nb_soutenances
            FROM soutenance s
            INNER JOIN etudiant e ON e.id_etudiant = s.id_etudiant
            GROUP BY e.filiere
            ORDER BY nb_soutenances DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ──────────────────────────────────────────────────────────
    // 4. Répartition des mentions (PV)
    // ──────────────────────────────────────────────────────────
    public function repartitionMentions(): array {
        $stmt = $this->pdo->query("
            SELECT mention, COUNT(*) AS nb
            FROM pv
            GROUP BY mention
            ORDER BY nb DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ──────────────────────────────────────────────────────────
    // 5. Soutenances par mois (pour graphe d'activité)
    // ──────────────────────────────────────────────────────────
    public function soutenancesParMois(): array {
        $stmt = $this->pdo->query("
            SELECT DATE_FORMAT(date, '%Y-%m') AS mois,
                   COUNT(*) AS nb_soutenances
            FROM soutenance
            GROUP BY mois
            ORDER BY mois ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ──────────────────────────────────────────────────────────
    // 6. Compteurs globaux (chiffres clés en haut du dashboard)
    // ──────────────────────────────────────────────────────────
    public function comptesGlobaux(): array {
        $data = [];

        $data['nb_etudiants']   = (int) $this->pdo->query("SELECT COUNT(*) FROM etudiant")->fetchColumn();
        $data['nb_profs']       = (int) $this->pdo->query("SELECT COUNT(*) FROM professeur")->fetchColumn();
        $data['nb_soutenances'] = (int) $this->pdo->query("SELECT COUNT(*) FROM soutenance")->fetchColumn();
        $data['nb_salles']      = (int) $this->pdo->query("SELECT COUNT(*) FROM salle")->fetchColumn();
        $data['nb_pv']          = (int) $this->pdo->query("SELECT COUNT(*) FROM pv")->fetchColumn();
        $data['nb_sans_salle']  = (int) $this->pdo->query(
            "SELECT COUNT(*) FROM soutenance WHERE id_salle IS NULL"
        )->fetchColumn();

        return $data;
    }

    // ──────────────────────────────────────────────────────────
    // 7. Moyenne des étudiants encadrés par prof
    // ──────────────────────────────────────────────────────────
    public function moyenneEncadrementParProf(): float {
        $result = $this->pdo->query("
            SELECT AVG(nb) FROM (
                SELECT COUNT(e.id_etudiant) AS nb
                FROM professeur p
                LEFT JOIN etudiant e ON e.id_prof = p.id_prof
                GROUP BY p.id_prof
            ) AS sous
        ")->fetchColumn();
        return round((float)$result, 2);
    }
}
?>

