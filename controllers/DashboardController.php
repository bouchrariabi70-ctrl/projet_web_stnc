<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/dashboard.php';

/**
 * DashboardController
 * Une seule action publique : index() → affiche le tableau de bord.
 * Toute la logique de calcul reste dans le modèle Dashboard.
 */
class DashboardController {
    private Dashboard $model;

    public function __construct(PDO $pdo) {
        $this->model = new Dashboard($pdo);
    }

    public function index(): void {
        // Collecte de toutes les statistiques
        $globaux             = $this->model->comptesGlobaux();
        $etudiantsParProf    = $this->model->etudiantsParProf();
        $soutenancesParProf  = $this->model->soutenancesParProf();
        $soutenancesParFil   = $this->model->soutenancesParFiliere();
        $mentions            = $this->model->repartitionMentions();
        $activiteMensuelle   = $this->model->soutenancesParMois();
        $moyenneEncadrement  = $this->model->moyenneEncadrementParProf();

        require_once __DIR__ . '/../views/dashboard/index.php';
    }
}
?>

