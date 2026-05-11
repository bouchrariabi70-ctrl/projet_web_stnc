<?php


require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/salle.php';

class SalleController {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // ──────────────────────────────────────────
    // Afficher la liste des salles
    // ──────────────────────────────────────────
    public function index(): void {
        $model  = new salle($this->pdo);
        $message = '';
        $type_message = '';

        // supprimer
        if(isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] === 'supprimer') {
            $s = $model->trouversalleParId((int)$_GET['id']);
            if($s) {
                try {
                    $s->supprimersalle();
                    $message = "Salle supprimée avec succès.";
                    $type_message = "success";
                } catch(RuntimeException $e) {
                    $message = $e->getMessage();
                    $type_message = "danger";
                }
            }
        }

        // recherche
        $motCle = trim($_GET['q'] ?? '');
        $salles = $motCle !== '' 
            ? $model->rechercher($motCle)
            : $model->listersalles();

        require_once __DIR__ . '/../views/salle/liste.php';
    }

    // ──────────────────────────────────────────
    // Afficher le formulaire d'ajout
    // ──────────────────────────────────────────
    public function afficherFormulaireAjout(): void {
        require_once __DIR__ . '/../views/salle/ajouter.php';
    }

    // ──────────────────────────────────────────
    // Traiter l'ajout d'une salle (reçoit $_POST)
    // ──────────────────────────────────────────
    public function ajouter(): void {
        $model   = new salle($this->pdo);
        $message = '';
        $type_message = '';
        $salle_edit = null;
        $salles  = $model->listersalles();

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $numero_salle = trim($_POST['numero_salle'] ?? '');
            $batiment     = trim($_POST['batiment']     ?? '');

            if(!empty($numero_salle)) {
                $s = new salle($this->pdo);
                $s->setNumero_salle($numero_salle);
                $s->setBatiment($batiment);
                $s->ajouterSalle();
                $message = "Salle ajoutée avec succès.";
                $type_message = "success";
                $salles = $model->listersalles(); // rafraîchir
            }
        }

        require_once __DIR__ . '/../views/salle/ajouter.php';
    }

    // ──────────────────────────────────────────
    // Afficher le formulaire de modification
    // ──────────────────────────────────────────
    public function afficherFormulaireModifier(int $id): void {
        $model            = new salle($this->pdo);
        $salle_a_modifier = $model->trouversalleParId($id);

        if (!$salle_a_modifier) {
            $erreur = "Salle introuvable.";
            require_once __DIR__ . '/../index.php';
            return;
        }

        // envoie $salle_a_modifier à la vue
        require_once __DIR__ . '/../views/salle/liste.php';
    }

    // ──────────────────────────────────────────
    // Traiter la modification (reçoit $_POST)
    // ──────────────────────────────────────────
    public function modifier(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->rediriger('index');
            return;
        }

        $id           = (int)   ($_POST['id']           ?? 0);
        $numero_salle = trim($_POST['numero_salle'] ?? '');
        $batiment     = trim($_POST['batiment']     ?? '');

        // validation
        if (empty($numero_salle) || $id === 0) {
            $erreur = "Données invalides.";
            require_once __DIR__ . '/../views/salles/form.php';
            return;
        }

        $model = new salle($this->pdo);
        $s     = $model->trouversalleParId($id);

        if ($s) {
            $s->setNumero_salle($numero_salle);
            $s->setBatiment($batiment);
            $s->modifiersalle();
        }

        $this->rediriger('index');
    }

    // ──────────────────────────────────────────
    // Supprimer une salle
    // ──────────────────────────────────────────
    public function supprimer(int $id): void {
        $model = new salle($this->pdo);
        $s     = $model->trouversalleParId($id);

        if (!$s) {
            $this->rediriger('index');
            return;
        }

        try {
            $s->supprimersalle();
        } catch (RuntimeException $e) {
            $erreur = $e->getMessage();
            $salles = $model->listersalles();
            require_once __DIR__ . '/../views/salle/liste.php';
            return;
        }

        $this->rediriger('index');
    }

    public function disponible(): void {
        $model        = new salle($this->pdo);
        $disponibles  = [];
        $dispo_search = false;

        if(isset($_GET['check_dispo'])) {
            $dispo_search = true;
            $date        = $_GET['date']        ?? '';
            $heure_debut = $_GET['heure_debut'] ?? '';
            $heure_fin   = $_GET['heure_fin']   ?? '';

            if(!empty($heure_debut) && strlen($heure_debut) === 5) {
                $heure_debut .= ':00';
            }
            if(!empty($heure_fin) && strlen($heure_fin) === 5) {
                $heure_fin .= ':00';
            }

            if($date && $heure_debut && $heure_fin) {
                $disponibles = $model->sallesDisponibles($date, $heure_debut, $heure_fin);
            }
        }

        require_once __DIR__ . '/../views/salle/disponible.php';
    }

    
    // ──────────────────────────────────────────
    // Méthode privée : rediriger vers une action
    // ──────────────────────────────────────────
    private function rediriger(string $action): void {
        header("Location: index.php?controller=salle&action=$action");
        exit;
    }
}
