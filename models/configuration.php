<?php
class configuration {
    private $id;
    private $cle;
    private $valeur;
    private $description;
    private PDO $pdo;

    public function __construct(PDO $pdo){
        $this->pdo=$pdo;
    }

    public function getId() :int{
        return $this->id;
    }
    public function getCle() :string{
        return $this->cle;
    }
    public function getValeur() :string{
        return $this->valeur;
    }
    public function getDescription() :string{
        return $this->description;
    }

    public function setId(int $id): void{
        $this->id=$id;
    }
    public function setCle(string $cle): void{
        $this->cle=$cle;
    }
    public function setValeur(string $valeur): void{
        $this->valeur=$valeur;
    }
    public function setDescription(string $des): void{
        $this->description=$des;
    }

    public function getValeurByCle(string $cle): ?string{
        $stmt=$this->pdo->prepare(
            "SELECT valeur FROM configuration WHERE cle=?"
        );
        $stmt->execute([$cle]);
        $result=$stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['valeur'] : null;

    }

    public function setValeurByCle(string $cle , string $valeur):void{
        $sql="UPDATE configuration SET valeur=?
        WHERE cle=? ";
        $stmt=$this->pdo->prepare($sql);
        $stmt->execute([$valeur,$cle]);  
    }

    public function toutesLesConfigs():array{
        $stmt=$this->pdo->prepare(
            "SELECT * FROM configuration ORDER BY id"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO :: FETCH_ASSOC);
    }

}
?>
