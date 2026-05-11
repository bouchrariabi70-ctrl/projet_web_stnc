<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/web/views/sidebar.html'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/web/views/css/bootstrap.min.css" rel="stylesheet"></link>
    <link href="/web/views/css/style.css" rel="stylesheet"></link>
    <title>Modification</title>
</head>
<body class="bg-light">
    <div class="main-content">
        <div class="col-md-10">
            <div class="form-card">
                <form action="/web/index.php?controller=etudiant&page=traiter_modifier" method="POST">
                    <h2 class="form-title">Modification </h2>
                    <input type="hidden" name="id" value="<?= $etudiant['id_etudiant'];?>" class="form-control">
                    <div class="mb-3">
                        <input type="text" name="CNE" value="<?=$etudiant['CNE'];?>" class="form-control">
                    </div>
                    <div class="mb-3">
                        <input type="text" name="nom" value="<?=$etudiant['nom'];?>"class="form-control">
                    </div>
                    <div class="mb-3">
                        <input type="text" name="prenom" value="<?=$etudiant['prenom'];?>" class="form-control">
                    </div>
                    <div class="mb-3">
                        <input type="email" name="email_perso" value="<?=$etudiant['email_perso'];?>"class="form-control">
                    </div>
                    <div class="mb-3">
                        <input type="email" name="email_pro" value="<?=$etudiant['email_pro'];?>" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="filiere" class="form-label">Choisir une filière :</label>
                            <select name="filiere" id="filiere" class="form-select" required>
                                <option value="">-- Sélectionner une filière --</option>
                                <option value="GI">Génie Informatique</option>
                                <option value="DATA">Ingénierie des données</option>
                                <option value="TDIA">Trabsformation Digitale & IA</option>
                            </select>
                    </div>
                    <button type="submit" name="submit" class="btn-import">Done</button>

                </form>

            </div>

        </div>

    </div>

    
</body>
</html>
