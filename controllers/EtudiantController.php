<?php
require_once('./config/database.php');
require_once('./models/Etudiant.php');
class EtudiantController{
    private $model;
    public function __construct($connexion){
        $this->model=new Etudiant($connexion);
    }
    public function afficherImport(){
        require $_SERVER['DOCUMENT_ROOT'] . '/web/views/etudiant/import.php';
    }
    public function afficherListe(){
        if(isset($_GET['filiere']) && $_GET['filiere']!=''){
            $filiere=$_GET['filiere'];
            $etudiants=$this->model->getParFiliere($filiere);
        }else{
           $etudiants=$this->model->getEtudiants();
        }
        require $_SERVER['DOCUMENT_ROOT'] . '/web/views/etudiant/liste.php';
       
    }
    public function afficherFormulaireAjout(){
        require $_SERVER['DOCUMENT_ROOT'] . '/web/views/etudiant/ajouteretudiant.php';
    }
    public function ajoutEtd(){
        
        if($_SERVER['REQUEST_METHOD']=='POST'){
            $CNE=$_POST['CNE'];
            $nom=$_POST['nom'];
            $prenom=$_POST['prenom'];
            $email_perso=$_POST['email_perso'];
            $email_pro=$_POST['email_pro'];
            $filiere=$_POST['filiere'];
            $this->model->insert($CNE,$nom,$prenom,$email_perso,$email_pro,$filiere,null);
        }
        header("location:/web/index.php?controller=etudiant&page=liste_etudiants");
        exit();
    }
    public function AfficherModifier(){
        if($_SERVER['REQUEST_METHOD']=='GET'){
            $id=$_GET['id'];
            $etudiant=$this->model->getById($id);
            require $_SERVER['DOCUMENT_ROOT'] . '/web/views/etudiant/modifier.php';
        }
         
    }
    public function traiterModification(){
        if($_SERVER['REQUEST_METHOD']=='POST'){
            $id=$_POST['id'];
            $CNE=$_POST['CNE'];
            $nom=$_POST['nom'];
            $prenom=$_POST['prenom'];
            $email_perso=$_POST['email_perso'];
            $email_pro=$_POST['email_pro'];
            $filiere=$_POST['filiere'];
            $this->model->update($id,$CNE,$nom,$prenom,$email_perso,$email_pro,$filiere);
            
        }
        header("location:/web/index.php?controller=etudiant&page=liste_etudiants");
        exit();
    }

   public function supprimerEtudiant(){
    if($_SERVER['REQUEST_METHOD']=='GET'){
        $id=$_GET['id'];
        $this->model->delete($id);
    }
    header("location:/web/index.php?controller=etudiant&page=liste_etudiants");
    exit();
   }




    public function importerEtudiants(){
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $filiere=$_POST['filiere'];
            $tmp=$_FILES['fichier']['tmp_name'];
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmp);
            $feuille=$spreadsheet->getActiveSheet();
            $compteur=0;
            foreach($feuille->getRowIterator() as $ligne){
                $compteur++;
                if($compteur==1){continue ;} //sauter l'entete
                $CNE=$feuille->getCell('A'.$compteur)->getValue();
                $nom=$feuille->getCell('B'.$compteur)->getValue();
                $prenom=$feuille->getCell('C'.$compteur)->getValue();
                $email_perso=$feuille->getCell('D'.$compteur)->getValue();
                $email_pro=$feuille->getCell('E'.$compteur)->getValue();
                if(empty($nom)){continue ;}
                $this->model->insert($CNE,$nom,$prenom,$email_perso,$email_pro,$filiere,null);


            }
            header("location:/web/index.php?controller=etudiant&page=liste_etudiants");
            exit();
        }
    }
}

?>