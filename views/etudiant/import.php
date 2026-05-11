<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/web/views/sidebar.html'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/web/views/css/bootstrap.min.css" rel="stylesheet"></link>
    <link href="/web/views/style.css" rel="stylesheet"></link>
    <title>Import</title>
</head>
<body class="bg-light">
    <div class="main-content">
        <div class="col-md-10 ">
            <div class="form-card">
                <form action="/web/index.php?controller=etudiant&page=importer_etudiants" method="POST" enctype="multipart/form-data">
                    <h2 class="form-title">Importation d'un fichier Excel</h2>
                    <div class="mb-3">
                        <label for="filiere" class="form-label">Choisir une filière :</label>
                            <select name="filiere" id="filiere" class="form-select" required>
                                <option value="">-- Sélectionner une filière --</option>
                                <option value="GI">Génie Informatique</option>
                                <option value="DATA">Ingénierie des données</option>
                                <option value="TDIA">Trabsformation Digitale & IA</option>
                            </select>
                    </div>
                    <div class="mb-3">
                        <label for="fichier" class="form-label">Importer un Fichier Excel :</label>
                        <input type="file" name="fichier" id="fichier" class="form-control" accept=".xls,.xlsx" required />
                    </div>
                    <div class="text-center">
                        <button type="submit" name="submit"class="btn btn-import text-white">Importer</button>
                    </div>
                </form>

            </div>

        </div>

    </div>
</body>
</html>
