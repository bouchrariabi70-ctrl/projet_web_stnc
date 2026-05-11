<?php
include($_SERVER['DOCUMENT_ROOT'].'/web/views/sidebar.html');
?>
<?php   

/**
 * Import des professeurs depuis un fichier Excel
 * Table cible : prof (id, nom, prenom, specialite)
 * Bibliothèque : PhpSpreadsheet
 */

require __DIR__ .'/../vendor/autoload.php';
require __DIR__ .'/../config/database.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$totalImporte = 0;
$totalEchec   = 0;
$erreurs      = [];
$imported     = false;

// ──────────────────────────────────────────────
// Traitement du formulaire (POST)
// ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fichier'])) {

    $fichier    = $_FILES['fichier'];
    $extension  = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
    $ligneDebut = 3; // Ligne 1-2 = en-têtes, données à partir de la ligne 3

    if ($fichier['error'] !== UPLOAD_ERR_OK) {
        $erreurs[] = "Erreur lors de l'upload du fichier (code : " . $fichier['error'] . ").";
    } elseif (!in_array($extension, ['xlsx', 'xls'])) {
        $erreurs[] = "Format non supporté. Veuillez uploader un fichier .xlsx ou .xls.";
    } else {
        try {
            $spreadsheet = IOFactory::load($fichier['tmp_name']);
            $feuille     = $spreadsheet->getActiveSheet();
            $ligneMax    = $feuille->getHighestDataRow();

            $sql  = "INSERT INTO professeur (nom, prenom, specialite) VALUES (:nom, :prenom, :specialite)";
            $stmt = $pdo->prepare($sql);

            for ($ligne = $ligneDebut; $ligne <= $ligneMax; $ligne++) {

                $nom        = trim((string) $feuille->getCell('A' . $ligne)->getValue());
                $prenom     = trim((string) $feuille->getCell('B' . $ligne)->getValue());
                $specialite = trim((string) $feuille->getCell('C' . $ligne)->getValue());

                // Ignorer les lignes vides
                if ($nom === '' && $prenom === '' && $specialite === '') continue;

                // Validation basique
                if ($nom === '' || $prenom === '') {
                    $erreurs[] = "Ligne $ligne : nom ou prénom manquant — ignorée.";
                    $totalEchec++;
                    continue;
                }

                try {
                    $stmt->execute([
                        ':nom'        => $nom,
                        ':prenom'     => $prenom,
                        ':specialite' => $specialite,
                    ]);
                    $totalImporte++;
                } catch (\PDOException $e) {
                    $erreurs[] = "Ligne $ligne ($prenom $nom) : " . $e->getMessage();
                    $totalEchec++;
                }
            }

            $imported = true;

        } catch (\Exception $e) {
            $erreurs[] = "Erreur lors de la lecture du fichier Excel : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Professeurs</title>
    <link href="/web/views/css/bootstrap.min.css" rel="stylesheet">
    <link href="/web/views/style.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="main-content">
        <div class="col-md-10">
            <div class="form-card">

                <?php if ($imported): ?>
                <!-- ── Résultats de l'import ── -->
                <h2 class="form-title">Résultat de l'importation</h2>

                <div class="row g-3 mb-4 text-center">
                    <div class="col-6">
                        <div class="p-4 rounded-4 border" style="background:#f0fdf4; border-color:#bbf7d0 !important;">
                            <div style="font-size:3rem; font-weight:800; color:#16a34a; line-height:1;">
                                <?= $totalImporte ?>
                            </div>
                            <div class="fw-semibold text-success mt-1">Importés avec succès</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-4 rounded-4 border" style="background:#fef2f2; border-color:#fecaca !important;">
                            <div style="font-size:3rem; font-weight:800; color:#dc2626; line-height:1;">
                                <?= $totalEchec ?>
                            </div>
                            <div class="fw-semibold text-danger mt-1">Échecs</div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($erreurs)): ?>
                <div class="alert alert-danger rounded-3 mb-4" style="font-size:0.9rem;">
                    <div class="fw-bold mb-2">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Détail des erreurs :
                    </div>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($erreurs as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="text-center mt-3">
                    <a href="?" class="btn btn-import text-white">
                        Importer un autre fichier
                    </a>
                </div>

                <?php else: ?>
                <!-- ── Formulaire ── -->
                <h2 class="form-title">Importation d'un fichier Excel</h2>

                <?php if (!empty($erreurs)): ?>
                <div class="alert alert-danger rounded-3 mb-4" style="font-size:0.9rem;">
                    <?php foreach ($erreurs as $err): ?>
                        <div><?= htmlspecialchars($err) ?></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <form method="POST"
                      enctype="multipart/form-data">

                    <div class="mb-3">
                        <label for="fichier" class="form-label">Importer un Fichier Excel :</label>
                        <input type="file"
                               name="fichier"
                               id="fichier"
                               class="form-control"
                               accept=".xls,.xlsx"
                               required>
                    </div>

                    <div class="text-center">
                        <button type="submit" name="submit" class="btn btn-import text-white">
                            Importer
                        </button>
                    </div>

                </form>
                <?php endif; ?>

            </div>
        </div>
    </div>

</body>
</html>