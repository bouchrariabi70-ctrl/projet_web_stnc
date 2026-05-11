<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';
require_once 'controllers/ControllerSoutenance.php';
require_once 'models/soutenance.php';
require_once 'models/salle.php';
require_once 'models/configuration.php';

$ctrl = new SoutenanceController($pdo);
$resultat = $ctrl->affecterSalles();

echo "<h2>Résultat de l'affectation</h2>";
echo "<p><strong>Soutenances affectées :</strong> " . $resultat['affectees'] . "</p>";

if (!empty($resultat['conflits'])) {
    echo "<h3 style='color:red'>Conflits (" . count($resultat['conflits']) . ")</h3><ul>";
    foreach ($resultat['conflits'] as $c) {
        echo "<li><strong>" . htmlspecialchars($c['soutenance']['titre_pfe'] ?? $c['soutenance']['id_stnc']) . "</strong> → " . htmlspecialchars($c['raison']) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:green'>✅ Aucun conflit.</p>";
}

echo "<h3>État en base après affectation</h3>";
$rows = $pdo->query("
    SELECT s.id_stnc, s.titre_pfe, s.date, s.heure_debut, s.heure_fin,
           sa.numero_salle, sa.batiment
    FROM soutenance s
    LEFT JOIN salle sa ON sa.id_salle = s.id_salle
    ORDER BY s.date, s.heure_debut
")->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='6'>
<tr><th>#</th><th>Titre</th><th>Date</th><th>Début</th><th>Fin</th><th>Salle</th><th>Bâtiment</th></tr>";
foreach ($rows as $r) {
    $salle = $r['numero_salle'] ?? '<span style="color:red">Non affectée</span>';
    echo "<tr>
        <td>{$r['id_stnc']}</td>
        <td>" . htmlspecialchars($r['titre_pfe']) . "</td>
        <td>{$r['date']}</td>
        <td>{$r['heure_debut']}</td>
        <td>{$r['heure_fin']}</td>
        <td>$salle</td>
        <td>" . htmlspecialchars($r['batiment'] ?? '-') . "</td>
    </tr>";
}
echo "</table>";
?>