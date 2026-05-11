<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';
require_once 'controllers/PvController.php';
require_once 'controllers/PvGenerator.php';

$ctrl = new PvController($pdo);

// Tester la génération pour l'étudiant ID=4
echo "<h2>Test génération PV étudiant #4</h2>";
ob_start();
$ctrl->genererPV(4);
$output = ob_get_clean();

$pvDir = realpath(__DIR__ . '/storage/pvs/');
$fichiers = glob($pvDir . '/pv_4_*.pdf');

if (!empty($fichiers)) {
    echo "<p style='color:green'>✅ PDF généré : " . basename($fichiers[0]) . "</p>";
    echo "<p>Taille : " . round(filesize($fichiers[0]) / 1024, 1) . " Ko</p>";
    echo "<a href='/web/index.php?controller=pv&action=telecharger&scope=etudiant&etudiant_id=4'>
            Télécharger le PDF
          </a>";
} else {
    echo "<p style='color:red'>❌ PDF non trouvé dans $pvDir</p>";
    echo "<pre>$output</pre>";
}

// Tester le ZIP encadrant
echo "<h2>Test ZIP encadrant #2 (ABAKOUY)</h2>";
echo "<a href='/web/index.php?controller=pv&action=telecharger&scope=encadrant&encadrant_id=2'>
        Télécharger ZIP encadrant
      </a><br><br>";

echo "<a href='/web/index.php?controller=pv&action=telecharger&scope=tous'>
        Télécharger ZIP tous les PVs
      </a>";
?>