<?php

class Etudiant{
    private PDO $connexion;
    public function __construct(PDO $connexion){
        
        $this->connexion=$connexion;
     }
     public function getEtudiants(){
        $stmt=$this->connexion->query("
        SELECT * FROM etudiant");
        return $stmt->fetchAll();
     }
     public function getById($id){
        $stmt=$this->connexion->prepare("SELECT * FROM etudiant where id_etudiant=:id");
        $stmt->execute([':id'=>$id]);
        return $stmt->fetch();
     }
     public function insert($CNE,$nom,$prenom,$email_perso,$email_pro,$filiere,$id_prof){
      try{
         $stmt=$this->connexion->prepare("INSERT INTO etudiant(CNE,nom,prenom,email_perso,email_pro,filiere,id_prof) VALUES (?,?,?,?,?,?,?)");
         $param=array($CNE,$nom,$prenom,$email_perso,$email_pro,$filiere,$id_prof);
         $stmt->execute($param);
         return true;
      }catch(PDOException $e){
         /*if($e->getCode()==23000){
            return false; //des doublons , on les ignore
         }*/
         die ("Erreur : " . $e->getMessage());//autre erreur, on l'affiche
      }
       
     }
     public function getByProf($id_prof){
      $stmt=$this->connexion->prepare("SELECT *FROM etudiant where id_prof=:id_prof");
      $stmt->execute([':id_prof'=>$id_prof]);
      return $stmt->fetchAll();
     }
     public function updateProf($id_etudiant,$id_prof){
      $stmt=$this->connexion->prepare("UPDATE etudiant SET id_prof=:id_prof WHERE id_etudiant=:id_etudiant");
      $stmt->execute([
         ':id_prof'=>$id_prof,
         ':id_etudiant'=>$id_etudiant,
      ]);
     }
     public function getParFiliere($filiere){
      $stmt=$this->connexion->prepare("SELECT * FROM etudiant WHERE filiere=?");
      $stmt->execute([$filiere]);
      return $stmt->fetchAll();
     }
     public function delete($id){
      $stmt=$this->connexion->prepare("DELETE  FROM etudiant WHERE id_etudiant=?");
      $stmt->execute([$id]);

     }
     public function update($id, $CNE, $nom, $prenom, $email_perso, $email_pro, $filiere) {
    $stmt = $this->connexion->prepare("UPDATE etudiant SET CNE=?, nom=?, prenom=?, email_perso=?, email_pro=?, filiere=? WHERE id_etudiant=?");
    $stmt->execute([$CNE, $nom, $prenom, $email_perso, $email_pro, $filiere, $id]);
    }
}
?>
