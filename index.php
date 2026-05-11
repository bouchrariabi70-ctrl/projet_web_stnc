<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';
require_once __DIR__ . '/vendor/autoload.php';

// ────────────────────────────────────────────
// Chargement des controllers
// ────────────────────────────────────────────
require_once 'controllers/EtudiantController.php';
require_once 'controllers/SalleController.php';
require_once 'controllers/ControllerSoutenance.php';
require_once 'controllers/PvController.php';
require_once 'controllers/ConfigurationController.php';
require_once 'controllers/VerificateurController.php';
require_once 'controllers/PvGenerator.php';



// ────────────────────────────────────────────
// Paramètres URL
// ────────────────────────────────────────────
$controller = $_GET['controller'] ?? 'dashboard';
$action     = $_GET['action'] ?? ($_GET['page'] ?? 'index');

// ────────────────────────────────────────────
// Instanciation du controller
// ────────────────────────────────────────────
switch ($controller) {

    case 'salle':
        $ctrl = new SalleController($pdo);
        break;

    case 'soutenance':
        $ctrl = new SoutenanceController($pdo);
        break;

    case 'pv':
        $ctrl = new PvController($pdo);
        break;

    case 'configuration':
        $ctrl = new ConfigurationController($pdo);
        break;

    case 'rapport':
        $ctrl = new VerificateurController($pdo);
        break;

    case 'etudiant':
        $ctrl = new EtudiantController($pdo);
        break;

    default:
        $ctrl = null;
        break;
}

// ────────────────────────────────────────────
// ROUTER PRINCIPAL
// ────────────────────────────────────────────
switch ($controller) {

    // =====================================================
    // ETUDIANT
    // =====================================================
    case 'etudiant':

        switch ($action) {

            case 'liste_etudiants':
                $ctrl->afficherListe();
                break;

            case 'ajouter_etudiant':
                $ctrl->afficherFormulaireAjout();
                break;

            case 'importer_etudiants':

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $ctrl->importerEtudiants();
                } else {
                    $ctrl->afficherImport();
                }

                break;

            case 'traiter_ajout':
                $ctrl->ajoutEtd();
                break;

            case 'afficher_modifier':
                $ctrl->AfficherModifier();
                break;

            case 'traiter_modifier':
                $ctrl->traiterModification();
                break;

            case 'supprimer_etudiant':
                $ctrl->supprimerEtudiant();
                break;

            default:
                $ctrl->afficherListe();
                break;
        }

        break;

    // =====================================================
    // SALLE
    // =====================================================
    case 'salle':

        switch ($action) {

            case 'liste':
            case 'index':
                $ctrl->index();
                break;

            case 'ajouter':
                $ctrl->ajouter();
                break;

            case 'disponible':
                $ctrl->disponible();
                break;

            case 'modifier':

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                    $ctrl->modifier();

                } else {

                    $id = (int)($_GET['id'] ?? 0);

                    if ($id > 0) {
                        $ctrl->afficherFormulaireModifier($id);
                    } else {
                        echo "ID invalide";
                    }
                }

                break;

            case 'supprimer':

                $id = (int)($_GET['id'] ?? 0);

                if ($id > 0) {
                    $ctrl->supprimer($id);
                } else {
                    echo "ID invalide";
                }

                break;

            default:
                $ctrl->index();
                break;
        }

        break;


    // =====================================================
// SOUTENANCE — planification automatique uniquement
// =====================================================
case 'soutenance':

    switch ($action) {

        case 'liste':
        case 'index':
            $ctrl->liste();
            break;

        case 'planifier':
            // Déclencheur manuel (ex: bouton admin) → algorithme automatique en backend
            // Collecte les étudiants, calcule créneaux/salles, affecte jurys selon disponibilité
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $ctrl->planifierAutomatiquement();
            } else {
                $ctrl->afficherConfirmationPlanification();
            }
            break;
            case 'update':
             $id = (int)($_GET['id'] ?? 0);
             if ($id > 0) {
             $ctrl->update($id);
           } else {
        echo "ID invalide";
    }
    break;

        case 'supprimer':
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) {
                $ctrl->destroy($id);
            } else {
                echo "ID invalide";
            }
            break;


        // ❌ 'ajouter' et 'update' supprimés : plus d'affectation manuelle

        default:
            $ctrl->liste();
            break;
    }

    break;

// =====================================================
// PV — liste groupée par encadrant + téléchargements
// =====================================================
case 'pv':

    switch ($action) {

        case 'index':

            // Vue principale
            $ctrl->index();
            break;

        case 'telecharger':

            $scope       = $_GET['scope'] ?? 'tous';
            $encadrantId = (int)($_GET['encadrant_id'] ?? 0);
            $etudiantId  = (int)($_GET['etudiant_id'] ?? 0);

            match ($scope) {

                'encadrant' => $encadrantId > 0
                    ? $ctrl->_telechargerParEncadrant($encadrantId)
                    : die("encadrant_id requis"),

                'etudiant' => $etudiantId > 0
                    ? $ctrl->_telechargerUnPV($etudiantId)
                    : die("etudiant_id requis"),

                default => $ctrl->_telechargerTous(),
            };

            break;

        case 'generer':

            $etudiantId = (int)($_GET['etudiant_id'] ?? 0);

            if ($etudiantId > 0) {

                $ctrl->_genererPV($etudiantId);

            } else {

                echo "ID invalide";
            }

            break;

        default:

            $ctrl->index();
            break;
    }

    break;


    // =====================================================
    // CONFIGURATION
    // =====================================================
    case 'configuration':

        switch ($action) {

            case 'modifier':
                $ctrl->modifier();
                break;

            case 'index':
            default:
                $ctrl->index();
                break;
        }

        break;

    // =====================================================
    // PROF
    // =====================================================
    case 'prof':
        require_once 'controllers/import_profs.php';
        break;

    case 'liste_prof':
        require_once 'views/prof/liste_profs.php';
        break;

    // =====================================================
    // DASHBOARD
    // =====================================================
    default:

        echo "<h3>Bienvenue sur GestPFE 👋</h3>";
        echo "<p>Utilisez le menu à gauche pour naviguer.</p>";

        break;
}
?>