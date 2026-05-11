<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/configuration.php';
class ConfigurationController {
    private PDO $pdo;
    public function __construct(PDO $pdo){
        $this->pdo=$pdo;
    }

    public function index(){
        $model=new configuration($this->pdo);
        $config=$model->toutesLesConfigs();
        require_once __DIR__ . '/../views/configuration/index.php';
    }

    public function modifier(){
        $cle=trim($_POST['cle']??'');
        $valeur=trim($_POST['valeur']??'');

        if(!empty($cle) && !empty($valeur)){
            $model = new configuration($this->pdo);
            $model->setValeurByCle($cle, $valeur);
        }
        header("Location: index.php?controller=configuration&action=index");
        exit;


    }
}
?>
