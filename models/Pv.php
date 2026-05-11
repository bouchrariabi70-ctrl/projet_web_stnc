<?php
class PV {
    private $pdo;

    // Attributs de la table
    private $id;
    private $soutenance_id;
    private $note_contenu;
    private $note_memoire;
    private $note_soutenance;
    private $moyenne;
    private $mention;
    private $president_jury_id;
    private $date_signature;
    private $statut;
    private $created_at;
    private $updated_at;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // --- Getters ---
    public function getId() { return $this->id; }
    public function getSoutenanceId() { return $this->soutenance_id; }
    public function getNoteContenu() { return $this->note_contenu; }
    public function getNoteMemoire() { return $this->note_memoire; }
    public function getNoteSoutenance() { return $this->note_soutenance; }
    public function getMoyenne() { return $this->moyenne; }
    public function getMention() { return $this->mention; }
    public function getPresidentJuryId() { return $this->president_jury_id; }
    public function getDateSignature() { return $this->date_signature; }
    public function getStatut() { return $this->statut; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }

    // --- Setters ---
    public function setSoutenanceId($id) { $this->soutenance_id = $id; }
    public function setNoteContenu($note) { $this->note_contenu = $note; }
    public function setNoteMemoire($note) { $this->note_memoire = $note; }
    public function setNoteSoutenance($note) { $this->note_soutenance = $note; }
    public function setPresidentJuryId($id) { $this->president_jury_id = $id; }
    public function setDateSignature($date) { $this->date_signature = $date; }
    public function setStatut($statut) { $this->statut = $statut; }

    // --- Méthodes utilitaires ---
    private function calculerMoyenne() {
        $this->moyenne = round(($this->note_contenu * 0.5 + $this->note_memoire * 0.2 + $this->note_soutenance * 0.3), 2);
    }

    private function deduireMention() {
        if ($this->moyenne >= 16) $this->mention = 'Très Bien';
        elseif ($this->moyenne >= 14) $this->mention = 'Bien';
        elseif ($this->moyenne >= 12) $this->mention = 'Assez Bien';
        elseif ($this->moyenne >= 10) $this->mention = 'Passable';
        else $this->mention = 'Ajourné';
    }

    // --- CRUD ---
    public function create() {
        $this->calculerMoyenne();
        $this->deduireMention();

        $sql = "INSERT INTO pv (soutenance_id, note_contenu, note_memoire, note_soutenance,
                moyenne, mention, president_jury_id, date_signature, statut)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $this->soutenance_id,
            $this->note_contenu,
            $this->note_memoire,
            $this->note_soutenance,
            $this->moyenne,
            $this->mention,
            $this->president_jury_id,
            $this->date_signature,
            $this->statut ?? 'brouillon'
        ]);
        $this->id = (int) $this->pdo->lastInsertId();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM pv WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id) {
        $this->calculerMoyenne();
        $this->deduireMention();

        $sql = "UPDATE pv SET note_contenu=?, note_memoire=?, note_soutenance=?, moyenne=?, mention=?, 
                president_jury_id=?, date_signature=?, statut=? WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $this->note_contenu,
            $this->note_memoire,
            $this->note_soutenance,
            $this->moyenne,
            $this->mention,
            $this->president_jury_id,
            $this->date_signature,
            $this->statut,
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM pv WHERE id=?");
        return $stmt->execute([$id]);
    }
}
?>

