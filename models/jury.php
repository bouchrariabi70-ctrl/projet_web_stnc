<?php

require_once 'prof.php';

class jury {

    private ?int $id_jury = null;
    private int $id_soutenance;
    private int $id_prof;
    private string $role;

    public function __construct(private PDO $pdo) {

    }

    // =========================
    // GETTERS
    // =========================

    public function getIdJury(): ?int {
        return $this->id_jury;
    }

    public function getIdSoutenance(): int {
        return $this->id_soutenance;
    }

    public function getIdProf(): int {
        return $this->id_prof;
    }

    public function getRole(): string {
        return $this->role;
    }

    // =========================
    // SETTERS
    // =========================

    public function setIdJury(?int $id_jury): void {
        $this->id_jury = $id_jury;
    }

    public function setIdSoutenance(int $id_soutenance): void {
        $this->id_soutenance = $id_soutenance;
    }

    public function setIdProf(int $id_prof): void {
        $this->id_prof = $id_prof;
    }

    public function setRole(string $role): void {
        $this->role = $role;
    }

    // =========================
    // CRUD
    // =========================

    // Afficher tous les jurys
    public function getAll() {

        $sql = "SELECT j.*, 
                       p.nom,
                       p.prenom,
                       s.date
                FROM jury j
                INNER JOIN professeur p 
                    ON j.id_prof = p.id
                INNER JOIN soutenance s
                    ON j.id_soutenance = s.id_stnc
                ORDER BY s.date";

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer un jury par id
    public function getById(int $id) {

        $sql = "SELECT * FROM jury 
                WHERE id_jury = :id";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Ajouter un membre jury
    public function insert(
        int $id_soutenance,
        int $id_prof,
        string $role
    ) {

        $sql = "INSERT INTO jury(
                    id_soutenance,
                    id_prof,
                    role
                )
                VALUES(
                    :id_soutenance,
                    :id_prof,
                    :role
                )";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':id_soutenance' => $id_soutenance,
            ':id_prof'       => $id_prof,
            ':role'          => $role
        ]);
    }

    // Modifier jury
    public function update(
        int $id_jury,
        int $id_soutenance,
        int $id_prof,
        string $role
    ) {

        $sql = "UPDATE jury
                SET 
                    id_soutenance = :id_soutenance,
                    id_prof       = :id_prof,
                    role          = :role
                WHERE id_jury = :id_jury";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':id_jury'       => $id_jury,
            ':id_soutenance' => $id_soutenance,
            ':id_prof'       => $id_prof,
            ':role'          => $role
        ]);
    }

    // Supprimer jury
    public function delete(int $id) {

        $sql = "DELETE FROM jury
                WHERE id_jury = :id";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);
    }

    // Membres d'une soutenance
    public function getMembresBySoutenance(int $id_soutenance) {

        $sql = "SELECT j.*, 
                       p.nom,
                       p.prenom
                FROM jury j
                INNER JOIN professeur p
                    ON j.id_prof = p.id
                WHERE j.id_soutenance = :id_soutenance";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id_soutenance' => $id_soutenance
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
