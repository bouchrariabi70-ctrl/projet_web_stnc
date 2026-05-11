
<?php
require_once ($_SERVER['DOCUMENT_ROOT'].'/web/views/sidebar.html');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/web/views/css/bootstrap.min.css" rel="stylesheet"></link>
    <link href="/web/views/style.css" rel="stylesheet"></link>
    <script src="/web/views/js/bootstrap.bundle.min.js"></script>
    <title>Liste des etudiants</title>
</head>
<body class="bg-light">
    <div class="main-content">
        <h2 class="form-title"> Liste Des Etudiants </h2>
        <form action="/web/index.php?controller=etudiant&page=liste_etudiants" method="get">
            <input type="hidden" name="page" value="liste_etudiants">
            <div class="mb-3 d-flex align-items-center gap-3">
                <label for="filiere" class="form-label mb-0">Filtrer par filière :</label>
                    <select name="filiere" id="filiere" class="form-select w-auto" onchange="this.form.submit()">
                        <option value="">Toutes les filières</option>
                        <option value="GI" <?= (isset($_GET['filiere']) && $_GET['filiere']=='GI')?'selected':'' ?>>Génie Informatique</option>
                        <option value="DATA" <?= (isset($_GET['filiere']) && $_GET['filiere']=='DATA')?'selected':'' ?>>Ingénierie des données</option>
                        <option value="TDIA" <?= (isset($_GET['filiere']) && $_GET['filiere']=='TDIA')?'selected':'' ?>>Transformation Digitale & IA</option>
                    </select>
             </div>
        </form>
        <?php if (empty($etudiants)): ?>
            <div class="alert alert-warning">Aucun étudiant trouvé </div>
        <?php else :?>
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>CNE</th>
                        <th>NOM</th>
                        <th>PRENOM</th>
                        <th>Email Personnel</th>
                        <th>Email Académique</th>
                        <th>Filière</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($etudiants as $etudiant): ?>
                    <tr>
                        <td><?= $etudiant['CNE']?></td>
                        <td><?= $etudiant['nom']?></td>
                        <td><?= $etudiant['prenom']?></td>
                        <td><?= $etudiant['email_perso']?></td>
                        <td><?= $etudiant['email_pro']?></td>
                        <td><?= $etudiant['filiere']?></td>
                        <td class="text-center">
                            <a href="/web/index.php?controller=etudiant&page=afficher_modifier&id=<?= $etudiant['id_etudiant'];?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i></a>
                            <a href="/web/index.php?controller=etudiant&page=supprimer_etudiant&id=<?= $etudiant['id_etudiant'];?>" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>

                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
            <?php endif; ?>


    </div>

    
</body>
</html>
