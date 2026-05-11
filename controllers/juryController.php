<?php

require_once 'jury.php';

class juryController {

    private jury $jury;

    public function __construct(private PDO $pdo) {

        $this->jury = new jury($pdo);
    }

    // =========================
    // AFFICHER TOUS LES JURYS
    // =========================

    public function index() {

        $jurys = $this->jury->getAll();

        include '../views/jury/index.php';
    }

    // =========================
    // FORMULAIRE AJOUT
    // =========================

    public function create() {

        include '../views/jury/create.php';
    }

    // =========================
    // AJOUTER JURY
    // =========================

    public function store() {

        $id_soutenance = $_POST['id_soutenance'] ?? '';
        $id_prof       = $_POST['id_prof'] ?? '';
        $role          = $_POST['role'] ?? '';

        if (
            empty($id_soutenance) ||
            empty($id_prof) ||
            empty($role)
        ) {

            $erreur = "Tous les champs sont obligatoires.";

            include '../views/jury/create.php';

            return;
        }

        $result = $this->jury->insert(
            (int)$id_soutenance,
            (int)$id_prof,
            $role
        );

        if ($result) {

            header('Location: index.php?page=jury&action=index');

        } else {

            $erreur = "Erreur lors de l'ajout.";

            include '../views/jury/create.php';
        }
    }

    // =========================
    // FORMULAIRE MODIFICATION
    // =========================

    public function edit() {

        $id = $_GET['id'] ?? null;

        $jury = $this->jury->getById((int)$id);

        if (!$jury) {

            $erreur = "Jury introuvable.";

            include '../views/jury/index.php';

            return;
        }

        include '../views/jury/edit.php';
    }

    // =========================
    // MODIFIER JURY
    // =========================

    public function updateJury() {

        $id_jury       = $_POST['id_jury'] ?? '';
        $id_soutenance = $_POST['id_soutenance'] ?? '';
        $id_prof       = $_POST['id_prof'] ?? '';
        $role          = $_POST['role'] ?? '';

        if (
            empty($id_jury) ||
            empty($id_soutenance) ||
            empty($id_prof) ||
            empty($role)
        ) {

            $erreur = "Tous les champs sont obligatoires.";

            $jury = $this->jury->getById((int)$id_jury);

            include '../views/jury/edit.php';

            return;
        }

        $this->jury->update(
            (int)$id_jury,
            (int)$id_soutenance,
            (int)$id_prof,
            $role
        );

        header('Location: index.php?page=jury&action=index');
    }

    // =========================
    // SUPPRIMER JURY
    // =========================

    public function delete() {

        $id = $_GET['id'] ?? null;

        $this->jury->delete((int)$id);

        header('Location: index.php?page=jury&action=index');
    }

    // =========================
    // MEMBRES D'UNE SOUTENANCE
    // =========================

    public function membresSoutenance() {

        $id_soutenance = $_GET['id_soutenance'] ?? null;

        $membres = $this->jury->getMembresBySoutenance(
            (int)$id_soutenance
        );

        include '../views/jury/membres.php';
    }
}

?>
