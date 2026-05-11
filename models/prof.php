<?php
class Prof {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Récupérer tous les profs
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM professeur ORDER BY nom, prenom");
        return $stmt->fetchAll();
    }

    // Récupérer un prof par son id
    public function getById(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM professeur WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // Insérer un prof
    public function insert(string $nom, string $prenom, string $specialite) {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO professeur(nom, prenom, specialite) VALUES (?, ?, ?)"
            );
            $stmt->execute([$nom, $prenom, $specialite]);
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return false; // doublon, on ignore
            }
            throw $e; // autre erreur, on relance
        }
    }

    // Modifier un prof
    public function update(int $id, string $nom, string $prenom, string $specialite) {
        $stmt = $this->pdo->prepare(
            "UPDATE professeur SET nom=?, prenom=?, specialite=? WHERE id=?"
        );
        $stmt->execute([$nom, $prenom, $specialite, $id]);
    }

    // Supprimer un prof
    public function delete(int $id) {
        $stmt = $this->pdo->prepare("DELETE FROM professeur WHERE id = ?");
        $stmt->execute([$id]);
    }

   

    // Compter le nombre d'étudiants encadrés par un prof
    public function getNbEtudiants(int $id) {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM etudiant WHERE id_prof = ?"
        );
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }

    // Répartition équitable des étudiants sur les profs
    public function repartirEtudiants() {
        $stmt = $this->pdo->query(
            "SELECT id_etudiant FROM etudiant WHERE id_prof IS NULL ORDER BY id_etudiant"
        );
        $etudiants = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($etudiants)) {
            return [];
        }

        $stmtP = $this->pdo->query(
            "SELECT p.id, COUNT(e.id_etudiant) AS nb_actuels
             FROM professeur p
             LEFT JOIN etudiant e ON e.id_prof = p.id
             GROUP BY p.id
             ORDER BY nb_actuels ASC"
        );
        $profs = $stmtP->fetchAll();

        if (empty($profs)) {
            return [];
        }

        // Toujours assigner au prof le moins chargé
        $affectations = [];
        foreach ($etudiants as $id_etudiant) {
            usort($profs, fn($a, $b) => $a['nb_actuels'] <=> $b['nb_actuels']);
            $profChoisi = &$profs[0];
            $affectations[$id_etudiant] = $profChoisi['id'];
            $profChoisi['nb_actuels']++;
        }

        $stmtUp = $this->pdo->prepare(
            "UPDATE etudiant SET id_prof = ? WHERE id_etudiant = ?"
        );
        foreach ($affectations as $id_etudiant => $id_prof) {
            $stmtUp->execute([$id_prof, $id_etudiant]);
        }

        return $affectations;
    }

    
    
}
?>
