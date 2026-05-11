<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/web/views/sidebar.html'); ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="/web/views/css/bootstrap.min.css" rel="stylesheet">
    <link href="/web/views/css/style.css" rel="stylesheet">

    <title>Modifier Soutenance</title>

    <style>
        .page-wrapper {
            display: flex;
        }

        .content-area {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 30px;
            min-height: 100vh;
            background: #f8f9fa;
        }

        .form-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-title {
            margin-bottom: 20px;
            font-weight: bold;
        }

        .btn-import {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
        }

        .btn-import:hover {
            background: #0b5ed7;
        }
    </style>

</head>

<body class="bg-light">

<div class="page-wrapper">

    <?php require_once($_SERVER['DOCUMENT_ROOT'] . '/web/views/sidebar.html'); ?>

    <div class="content-area">

        <div class="container">
            <div class="row justify-content-center">

                <div class="col-md-10">

                    <div class="form-card">

                        <h2 class="form-title">Modification Soutenance</h2>

                        <!-- ✅ FORMULAIRE PHP CLASSIQUE -->
                        <form method="POST"
                              action="/web/index.php?controller=soutenance&page=update&id=<?= htmlspecialchars($soutenance['id_stnc']) ?>">

                            <div class="mb-3">
                                <label>Date</label>
                                <input type="date"
                                       name="date_soutenance"
                                       class="form-control"
                                       value="<?= htmlspecialchars($soutenance['date'] ?? $soutenance['date_soutenance'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label>ID Salle</label>
                                <input type="number"
                                       name="id_salle"
                                       class="form-control"
                                       value="<?= htmlspecialchars($soutenance['id_salle'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label>ID Étudiant</label>
                                <input type="number"
                                       name="etudiant_id"
                                       class="form-control"
                                       value="<?= htmlspecialchars($soutenance['etudiant_id'] ?? '') ?>">
                            </div>

                            <button type="submit" class="btn-import">
                                Modifier
                            </button>

                        </form>

                        <!-- MESSAGE OPTIONNEL -->
                        <?php if (isset($_GET['updated'])): ?>
                            <div class="alert alert-success mt-3">
                                Modification réussie
                            </div>
                        <?php endif; ?>

                    </div>

                </div>

            </div>
        </div>

    </div>

</div>

</body>
</html>
