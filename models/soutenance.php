<?php

class Soutenance {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // =========================================================
    // NORMALISATION DES DONNÉES
    // =========================================================
    private function normalizeData(array $data): array {
        return [
            'date_soutenance' => $data['date_soutenance'] ?? $data['date'] ?? null,
            'heure_debut'     => $data['heure_debut'] ?? null,
            'heure_fin'       => $data['heure_fin'] ?? null,
            'id_salle'        => $data['id_salle'] ?? null,
            'id_etudiant'     => $data['id_etudiant'] ?? $data['etudiant_id'] ?? null,
            'titre_pfe'       => $data['titre_pfe'] ?? '',
            'statut'          => $data['statut'] ?? 'planifiée'
        ];
    }

    // =========================================================
    // LISTE DES SOUTENANCES
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

        $params  = [];
        $clauses = [];

        if (!empty($filters['statut'])) {
            $clauses[] = "s.statut = ?";
            $params[]  = $filters['statut'];
        }

        if (!empty($filters['id_salle'])) {
            $clauses[] = "s.id_salle = ?";
            $params[]  = $filters['id_salle'];
        }

        if (!empty($clauses)) {
            $sql .= " WHERE " . implode(' AND ', $clauses);
        }

        $sql .= " ORDER BY s.date, s.heure_debut";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================================================
    // RECUPERATION PAR ID
    // =========================================================
    public function getSoutenanceById(int $id): ?array {

        $sql = "
            SELECT *
            FROM soutenance
            WHERE id_stnc = ?
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // =========================================================
    // CREATION
    // =========================================================
    public function createSoutenance(array $data): array {

        $data = $this->normalizeData($data);

        // Validation des données requises
        if (empty($data['id_etudiant'])) {
            throw new InvalidArgumentException("L'étudiant est requis pour créer une soutenance.");
        }

        // Vérifier les conflits d'horaires si salle et horaires sont fournis
        if (!empty($data['id_salle']) && !empty($data['date_soutenance']) && 
            !empty($data['heure_debut']) && !empty($data['heure_fin'])) {
            if ($this->hasTimeConflict($data['id_salle'], $data['date_soutenance'], 
                $data['heure_debut'], $data['heure_fin'])) {
                throw new RuntimeException("Conflit d'horaire détecté pour cette salle.");
            }
        }

        $sql = "
            INSERT INTO soutenance (
                date,
                heure_debut,
                heure_fin,
                id_salle,
                etudiant_id,
                titre_pfe,
                statut
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $data['date_soutenance'],
            $data['heure_debut'],
            $data['heure_fin'],
            $data['id_salle'],
            $data['id_etudiant'],
            $data['titre_pfe'],
            $data['statut']
        ]);

        $id = (int)$this->pdo->lastInsertId();

        return $this->getSoutenanceById($id);
    }

    // =========================================================
    // MISE A JOUR
    // =========================================================
    public function updateSoutenance(int $id, array $data): bool {
        $data = $this->normalizeData($data);

        if (empty($data['id_etudiant'])) {
            throw new InvalidArgumentException("Étudiant manquant");
        }

        $etudiant_id = $data['id_etudiant'];

        $sql = "
        UPDATE soutenance
        SET
            date        = ?,
            heure_debut = ?,
            heure_fin   = ?,
            id_salle    = ?,
            etudiant_id = ?,
            titre_pfe   = ?,
            statut      = ?
        WHERE id_stnc = ?
    ";

    $stmt = $this->pdo->prepare($sql);

    return $stmt->execute([
        $data['date_soutenance'] ?? null,
        $data['heure_debut'] ?? null,
        $data['heure_fin'] ?? null,
        $data['id_salle'] ?? null,
        $etudiant_id,
        $data['titre_pfe'] ?? '',
        $data['statut'] ?? 'planifiée',
        $id
    ]);
    }

    // =========================================================
    // SUPPRESSION
    // =========================================================
    public function deleteSoutenance(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM soutenance WHERE id_stnc = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    // =========================================================
    // AFFECTER DATE + HORAIRES
    // =========================================================
    public function affecterHoraire(
        int $id_stnc,
        string $date,
        string $heure_debut,
        string $heure_fin
    ): bool {

        $sql = "
            UPDATE soutenance
            SET
                date = ?,
                heure_debut = ?,
                heure_fin = ?
            WHERE id_stnc = ?
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $date,
            $heure_debut,
            $heure_fin,
            $id_stnc
        ]);

        return $stmt->rowCount() > 0;
    }

    // =========================================================
    // AFFECTER LE PLANNING COMPLET
    // =========================================================
    public function affecterPlanning(
        int $id_stnc,
        int $id_salle,
        string $date,
        string $heure_debut,
        string $heure_fin,
        string $statut = 'planifiée'
    ): bool {

        $sql = "
            UPDATE soutenance
            SET
                id_salle = ?,
                date = ?,
                heure_debut = ?,
                heure_fin = ?,
                statut = ?
            WHERE id_stnc = ?
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $id_salle,
            $date,
            $heure_debut,
            $heure_fin,
            $statut,
            $id_stnc
        ]);

        return $stmt->rowCount() > 0;
    }

    // =========================================================
    // VÉRIFICATION DES CONFLITS D'HORAIRES
    // =========================================================
    public function hasTimeConflict(
        int $id_salle, 
        string $date, 
        string $heure_debut, 
        string $heure_fin, 
        ?int $exclude_id = null
    ): bool {

        $sql = "
            SELECT COUNT(*) as count
            FROM soutenance
            WHERE id_salle = ?
              AND date = ?
              AND (
                  (heure_debut < ? AND heure_fin > ?) OR
                  (heure_debut < ? AND heure_fin > ?) OR
                  (heure_debut >= ? AND heure_fin <= ?)
              )
        ";

        $params = [
            $id_salle,
            $date,
            $heure_fin, $heure_debut,  // chevauchement partiel début
            $heure_debut, $heure_fin,  // chevauchement partiel fin
            $heure_debut, $heure_fin   // complètement inclus
        ];

        if ($exclude_id !== null) {
            $sql .= " AND id_stnc != ?";
            $params[] = $exclude_id;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return ($result['count'] ?? 0) > 0;
    }

    // =========================================================
    // SOUTENANCES PAR SALLE ET DATE
    // =========================================================
    public function getSoutenancesBySalleAndDate(int $id_salle, string $date): array {

        $sql = "
            SELECT 
                s.*,
                e.nom as etudiant_nom,
                e.prenom as etudiant_prenom,
                p.nom as encadrant_nom,
                p.prenom as encadrant_prenom
            FROM soutenance s
            JOIN etudiant e ON e.id_etudiant = s.etudiant_id
            LEFT JOIN professeur p ON p.id = e.id_prof
            WHERE s.id_salle = ? AND s.date = ?
            ORDER BY s.heure_debut
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_salle, $date]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}