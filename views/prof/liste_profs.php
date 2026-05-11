<?php
require_once ($_SERVER['DOCUMENT_ROOT'].'/web/views/sidebar.html');
require __DIR__ .'/../../config/database.php';

// Récupération des professeurs
$sql = "SELECT * FROM professeur ORDER BY nom ASC";

$stmt = $pdo->query($sql);

$profs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="/web/views/css/bootstrap.min.css" rel="stylesheet">
    <link href="/web/views/style.css" rel="stylesheet">

    <script src="/web/views/js/bootstrap.bundle.min.js"></script>

    <title>Liste des Professeurs</title>
</head>

<body class="bg-light">

    <div class="main-content">

        <h2 class="form-title">
            Liste Des Professeurs
        </h2>

        <?php if (empty($profs)): ?>

            <div class="alert alert-warning">
                Aucun professeur trouvé.
            </div>

        <?php else: ?>

            <table class="table table-striped table-bordered">

                <thead class="table-dark">

                    <tr>
                        <th>id</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Spécialité</th>
                        
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($profs as $prof): ?>

                        <tr>

                            <td>
                                <?= $prof['id'] ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($prof['nom']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($prof['prenom']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($prof['specialite']) ?>
                            </td>

                            

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        <?php endif; ?>

    </div>

</body>
</html>