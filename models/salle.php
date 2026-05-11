<?php
class salle{
    private $id_salle;
    private $numero_salle;
    private $batiment;
    private $capacite;
    private $disponible;
    private PDO $pdo; #connection à la base de données

    public function __construct(PDO $pdo){
        $this->pdo=$pdo;
    }
    
    #region getters and setters
    //getters

    public function getId_salle(): int{
        return $this->id_salle;
    }
    public function getNumero_salle(): string{
        return $this->numero_salle;
    }
    public function getBatiment(): string{
        return $this->batiment;
    }
    public function getCapacite(): int{
        return $this->capacite;
    }
    public function getDisponible(): bool{
        return $this->disponible;
    }
    
    //setters
    public function setId_salle(int $id_salle): void{
        $this->id_salle=$id_salle;
    }
    public function setNumero_salle(string $numero_salle): void{
        $this->numero_salle=$numero_salle;
    }
    public function setBatiment(string $batiment): void{
        $this->batiment=$batiment;
    }
    public function setCapacite(int $capacite): void{
        $this->capacite=$capacite;
    }
    public function setDisponible(string $disponible): void{
        $this->disponible=$disponible;
    }

    #methodes crud
    public function ajouterSalle(): void{
        $numero_salle = $_POST['numero_salle'] ?? null;
        $batiment = $_POST['batiment'] ?? null;

        $stmt = $this->pdo->prepare(
            "INSERT INTO salle (numero_salle, batiment) VALUES (?, ?)"
        );
        
        $stmt->execute([$numero_salle, $batiment]);

        $this->id_salle = (int) $this->pdo->lastInsertId();

    
    
    }

    public function modifiersalle():void{
        $sql = "UPDATE salle SET 
        numero_salle = :numero_salle,
        batiment = :batiment
        WHERE id_salle = :id_salle";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':batiment' => $this->batiment,
            ':numero_salle' => $this->numero_salle,
            ':id_salle' => $this->id_salle
        ]);
    }
    public function supprimersalle():void{
        $check = $this->pdo->prepare( "SELECT COUNT(*) FROM Soutenance WHERE id_salle = :id_salle");
        $check->execute([':id_salle' => $this->id_salle]);
        $nb = $check->fetchColumn();
        if ($nb > 0) { throw new RuntimeException( "Impossible de supprimer : cette salle est utilisée dans $nb soutenance(s)." ); } 
        $sql = "DELETE FROM salle WHERE id_salle = :id_salle"; 
        $stmt = $this->pdo->prepare($sql); 
        $stmt->execute([':id_salle' => $this->id_salle]); 
    }

    public function trouversalleParId(int $id):?salle{
        $stmt = $this->pdo->prepare(
            "SELECT * FROM Salle WHERE id_salle = :id"
        );
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch();
 
        if (!$data) {
            return null;   
        }
 
        return $this->remplirDepuisTableau($data);
    }

    public function listersalles():array{
        $stmt = $this->pdo->query(
            "SELECT * FROM Salle ORDER BY batiment, numero_salle"
        );
        $resultats = [];
        foreach ($stmt->fetchAll() as $data) {
            $resultats[] = $this->remplirDepuisTableau($data);
        }
        return $resultats;
    }

    public function rechercher(string $motCle): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM Salle
             WHERE numero_salle LIKE :mot OR batiment LIKE :mot
             ORDER BY batiment, numero_salle"
        );
        $stmt->execute([':mot' => "%$motCle%"]);
        $resultats = [];
        foreach ($stmt->fetchAll() as $data) {
            $resultats[] = $this->remplirDepuisTableau($data);
        }
        return $resultats;
    }

    
    public function sallesDisponibles(string $date, string $heureDebut, string $heureFin): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM Salle
            WHERE id_salle NOT IN (
                SELECT id_salle FROM Soutenance
                WHERE date   = :date
                AND heure_debut < :heure_fin
                AND heure_fin   > :heure_debut
            )
            ORDER BY batiment, numero_salle
        ");
        $stmt->execute([
            ':date'        => $date,
            ':heure_debut' => $heureDebut,
            ':heure_fin'   => $heureFin,
        ]);
        $resultats = [];
        foreach ($stmt->fetchAll() as $data) {
            $resultats[] = $this->remplirDepuisTableau($data);
        }
        return $resultats;
    }
    
    #methode privee
    private function remplirDepuisTableau(array $data): salle {
        $salle = new salle($this->pdo);
        $salle->setId_salle($data['id_salle']);
        $salle->numero_salle = $data['numero_salle'];
        $salle->batiment = $data['batiment'] ??''; 
        return $salle;
    }

    public function toArray(): array {
        return [
            'id_salle' => $this->id_salle,
            'numero_salle'   => $this->numero_salle,
            'batiment' => $this->batiment,
        ];
    }
}
