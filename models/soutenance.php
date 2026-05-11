<?php

class Soutenance {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // =========================================================
    // NORMALISATION (sécurisée)
    // =========================================================
    private function normalizeData(array $data): array {
        return [
            'date_soutenance' => $data['date_soutenance'] ?? null,
            'heure_debut'     => $data['heure_debut'] ?? null,
            'heure_fin'       => $data['heure_fin'] ?? null,
            'id_salle'        => $data['id_salle'] ?? null,
            'id_etudiant'     => $data['id_etudiant'] ?? $data['etudiant_id'] ?? null,
            'titre_pfe'       => $data['titre_pfe'] ?? '',
            'statut'          => $data['statut'] ?? 'non planifiée',
        ];
    }

    // =========================================================
    // LISTE
    // =========================================================
    public function getAllSoutenances(array $filters = []): array {

        $sql = "
            SELECT
                s.id_stnc,
                s.date AS date_soutenance,
                s.heure_debut,
                s.heure_fin,
                s.titre_pfe,
                s.statut,
                s.id_salle,
                s.etudiant_id
            FROM soutenance s
        ";

        $params = [];
        $where  = [];

        if (!empty($filters['statut'])) {
            $where[] = "s.statut = ?";
            $params[] = $filters['statut'];
        }

        if (!empty($filters['id_salle'])) {
            $where[] = "s.id_salle = ?";
            $params[] = $filters['id_salle'];
        }

        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY s.date, s.heure_debut";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================================================
    // GET BY ID
    // =========================================================
    public function getSoutenanceById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM soutenance WHERE id_stnc = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // =========================================================
    // CRÉATION (corrigée)
    // =========================================================
    public function createSoutenance(array $data): ?array {

        $data = $this->normalizeData($data);

        // ✔ étudiant obligatoire
        if (empty($data['id_etudiant'])) {
            throw new InvalidArgumentException("Étudiant obligatoire pour créer une soutenance.");
        }

        // ✔ insertion même sans planning (OK pour ton système auto)
        $stmt = $this->pdo->prepare("
            INSERT INTO soutenance 
            (date, heure_debut, heure_fin, id_salle, etudiant_id, titre_pfe, statut)
            VALUES
            (:date, :heure_debut, :heure_fin, :id_salle, :id_etudiant, :titre_pfe, :statut)
        ");

        $stmt->execute([
            ':date'        => $data['date_soutenance'],
            ':heure_debut' => $data['heure_debut'],
            ':heure_fin'   => $data['heure_fin'],
            ':id_salle'    => $data['id_salle'],
            ':id_etudiant' => $data['id_etudiant'],
            ':titre_pfe'   => $data['titre_pfe'],
            ':statut'      => $data['statut'],
        ]);

        return $this->getSoutenanceById((int)$this->pdo->lastInsertId());
    }

    // =========================================================
    // UPDATE
    // =========================================================
    public function updateSoutenance(int $id, array $data): bool {

        $data = $this->normalizeData($data);

        if (empty($data['id_etudiant'])) {
            throw new InvalidArgumentException("Étudiant requis");
        }

        $stmt = $this->pdo->prepare("
            UPDATE soutenance
            SET date = ?,
                heure_debut = ?,
                heure_fin = ?,
                id_salle = ?,
                etudiant_id = ?,
                titre_pfe = ?,
                statut = ?
            WHERE id_stnc = ?
        ");

        return $stmt->execute([
            $data['date_soutenance'],
            $data['heure_debut'],
            $data['heure_fin'],
            $data['id_salle'],
            $data['id_etudiant'],
            $data['titre_pfe'],
            $data['statut'],
            $id
        ]);
    }

    // =========================================================
    // DELETE
    // =========================================================
    public function deleteSoutenance(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM soutenance WHERE id_stnc = ?");
        return $stmt->execute([$id]);
    }

    // =========================================================
    // AFFECTATION PLANNING (IMPORTANT)
    // =========================================================
    public function affecterPlanning(
        int $id_stnc,
        int $id_salle,
        string $date,
        string $heure_debut,
        string $heure_fin,
        string $statut = 'planifiée'
    ): bool {

        // ✔ validation minimale (évite données cassées)
        if (empty($date) || empty($heure_debut) || empty($heure_fin)) {
            return false;
        }

        $stmt = $this->pdo->prepare("
            UPDATE soutenance
            SET id_salle = ?,
                date = ?,
                heure_debut = ?,
                heure_fin = ?,
                statut = ?
            WHERE id_stnc = ?
        ");

        return $stmt->execute([
            $id_salle,
            $date,
            $heure_debut,
            $heure_fin,
            $statut,
            $id_stnc
        ]);
    }

    // =========================================================
    // CONFLITS
    // =========================================================
    public function hasTimeConflict(
        int $id_salle,
        string $date,
        string $heure_debut,
        string $heure_fin,
        ?int $exclude_id = null
    ): bool {

        $sql = "
            SELECT COUNT(*) FROM soutenance
            WHERE id_salle = ?
              AND date = ?
              AND heure_debut < ?
              AND heure_fin > ?
        ";

        $params = [$id_salle, $date, $heure_fin, $heure_debut];

        if ($exclude_id) {
            $sql .= " AND id_stnc != ?";
            $params[] = $exclude_id;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn() > 0;
    }

    // =========================================================
    // SOUTENANCES NON PLANIFIÉES
    // =========================================================
    public function soutenancesSansAffectation(): array {
        $stmt = $this->pdo->query("
            SELECT * FROM soutenance
            WHERE id_salle IS NULL
            ORDER BY id_stnc DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}