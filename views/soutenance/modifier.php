<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/web/views/sidebar.html'); ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/web/views/css/bootstrap.min.css" rel="stylesheet">
    <link href="/web/views/css/style.css" rel="stylesheet">
    <title>Modifier Soutenance</title>
</head>

<body class="bg-light">

<div class="main-content">
    <div class="col-md-10">
        <div class="form-card">

            <h2 class="form-title">Modification Soutenance</h2>

            <form id="formModifier">

                <input type="hidden" id="id"
                       value="<?= $soutenance['id']; ?>">

                <div class="mb-3">
                    <label>Date</label>

                    <input type="date"
                           id="date"
                           class="form-control"
                           value="<?= $soutenance['date']; ?>">
                </div>

                <div class="mb-3">
                    <label>ID Salle</label>

                    <input type="number"
                           id="id_salle"
                           class="form-control"
                           value="<?= $soutenance['id_salle']; ?>">
                </div>

                <div class="mb-3">
                    <label>ID Étudiant</label>

                    <input type="number"
                           id="etudiant_id"
                           class="form-control"
                           value="<?= $soutenance['etudiant_id']; ?>">
                </div>

                <button type="submit" class="btn-import">
                    Modifier
                </button>

            </form>

            <div id="message" class="mt-3"></div>

        </div>
    </div>
</div>

<script>

document.getElementById("formModifier")
.addEventListener("submit", async function(e) {

    e.preventDefault();

    const id = document.getElementById("id").value;

    const data = {
        date: document.getElementById("date").value,
        id_salle: document.getElementById("id_salle").value,
        etudiant_id: document.getElementById("etudiant_id").value
    };

    try {

        const response = await fetch(`/web/index.php?controller=soutenance&page=update&id=${id}`, {
            method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify(data)
            }
        );

        const result = await response.json();

        document.getElementById("message").innerHTML =
            `<div class="alert alert-info">
                ${result.message}
             </div>`;

    } catch(error) {

        document.getElementById("message").innerHTML =
            `<div class="alert alert-danger">
                Erreur serveur
             </div>`;
    }

});

</script>

</body>
</html>