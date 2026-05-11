<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/web/views/sidebar.html'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/web/views/css/bootstrap.min.css" rel="stylesheet"></link>
    <link href="/web/views/style.css" rel="stylesheet"></link>
    <title>ajouter etudiant</title>
</head>
<body class="bg-light">
    <div class="main-content">
        <div class="col-md-10 ">
            <div class="form-card">
                <form action="/web/index.php?controller=etudiant&page=traiter_ajout" method="POST">
                    <h2 class="form-title">Ajouter un Etudiant </h2>
                    <div class="mb-3">
                      <input type="text" name="CNE" placeholder="CNE" class="form-control"required>
                    </div>
                    <div class="mb-3">
                      <input type="text" name="nom" placeholder="Nom de l'etudiant" class="form-control" required>
                    </div>
                    <div class="mb-3">
                     <input type="text" name="prenom" placeholder="Prenom de l'etudiant" class="form-control"required>
                    </div>
                    <div class="mb-3">
                      <input type="email" name="email_perso" placeholder="L'email personnel" class="form-control"required>
                    </div>
                    <div class="mb-3">
                      <input type="email" name="email_pro" placeholder="L'email Academique" class="form-control"required>
                    </div>
                    <div class="mb-3">
                      <input type="text" name="filiere" placeholder="Filiere de l'etudiant" class="form-control"required>
                    </div>
                    <button type="submit" name="submit" class="btn-import">Ajouter</button>
                </form>

            </div>

        </div>

    </div>

    
</body>
</html>
