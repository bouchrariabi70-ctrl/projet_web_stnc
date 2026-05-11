<?php


class Soutenance {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAllSoutenances(array $filters = []): array {
       $sql = "SELECT 
            id_stnc,
            date AS date_soutenance,
            heure_debut,
            heure_fin,
            titre_pfe,
            statut,
            id_salle,
            etudiant_id
        FROM soutenance";
        $params = [];
        $clauses = [];

        if (!empty($filters['statut'])) {
            $clauses[] = 'statut = ?';
            $params[] = $filters['statut'];
        }
        if (!empty($filters['id_salle'])) {
            $clauses[] = 'id_salle = ?';
            $params[] = $filters['id_salle'];
        }

        if (!empty($clauses)) {
            $sql .= ' WHERE ' . implode(' AND ', $clauses);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSoutenanceById(int $id): ?array {
        $sql = "SELECT * FROM soutenance WHERE id_stnc = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function createSoutenance(array $data): array {
        $sql = "INSERT INTO soutenance (date, heure_debut, heure_fin, id_salle, etudiant_id, titre_pfe, statut)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['date_soutenance'],
            $data['heure_debut'],
            $data['heure_fin'],
            $data['id_salle'],
            $data['id_etudiant'],
            '', // titre_pfe vide
            $data['statut'] ?? 'planifiée'
        ]);

        $id = (int) $this->pdo->lastInsertId();
        return $this->getSoutenanceById($id);
    }

    public function updateSoutenance(int $id, array $data): bool {
        $sql = "UPDATE soutenance SET date = ?, id_salle = ?, etudiant_id = ? WHERE id_stnc = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['date_soutenance'], 
            $data['id_salle'], 
            $data['id_etudiant'], 
            $id
        ]);
        return $stmt->rowCount() > 0;
    }

    public function deleteSoutenance(int $id): bool {
        $sql = "DELETE FROM soutenance WHERE id_stnc = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    // CHAYMAE: debut

    public function soutenancesSansAffectation(): array {
        $stmt = $this->pdo->query(
            "SELECT * FROM soutenance 
            WHERE id_salle IS NULL 
            ORDER BY date, heure_debut"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function affecterSalles(int $id_stc, int $id_salle): void {
        $stmt = $this->pdo->prepare(
            "UPDATE soutenance SET id_salle = ? WHERE id_stnc = ?"
        );
        $stmt->execute([$id_salle, $id_stc]);
    }
    //CHAYMAE: fin 
}
