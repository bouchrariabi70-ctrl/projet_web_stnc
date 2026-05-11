<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Verificateur.php';

/**
 * VerificateurController
 * Expose deux actions :
 *   - affectation() → vérifie le fichier d'affectation
 *   - planning()    → vérifie le fichier de planning
 */
class VerificateurController {
    private Verificateur $verificateur;

    public function __construct(PDO $pdo) {
        $this->verificateur = new Verificateur($pdo);
    }

    /** Vérifie la répartition des étudiants par professeur */
    public function affectation(): void {
        $rapport = $this->verificateur->verifierAffectation();
        $titre   = "Vérification — Fichier d'affectation";
        require_once __DIR__ . '/../views/verificateur/rapport.php';
    }

    /** Vérifie le planning des soutenances */
    public function planning(): void {
        $rapport = $this->verificateur->verifierPlanning();
        $titre   = "Vérification — Fichier de planning";
        require_once __DIR__ . '/../views/verificateur/rapport.php';
    }

    /** Lance les deux vérifications en une seule page */
    public function tout(): void {
        $rapportAffectation = $this->verificateur->verifierAffectation();
        $rapportPlanning    = $this->verificateur->verifierPlanning();
        require_once __DIR__ . '/../views/verificateur/rapport_complet.php';
    }
}
?>

