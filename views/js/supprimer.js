function deleteSoutenance(id) {
    fetch("/web/index.php?controller=soutenance&page=supprimer&id=" + id)
        .then(res => res.json())
        .then(data => {
            let msg = "";

            if (data.success) {
                msg = '<div class="alert-success">Suppression avec succès</div>';
            } 
            else if (data.error) {
                msg = '<div class="alert-error">' + data.error + '</div>';
            }

            document.getElementById("message").innerHTML = msg;
        });
}