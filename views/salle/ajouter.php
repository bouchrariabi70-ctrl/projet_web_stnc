<?php
require_once   __DIR__ .'/../sidebar.html';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="/web/views/salle/styleSalle.css" rel="stylesheet">
    
</head>
<body>
    <?php if($message): ?>
        <div class="alert alert-<?= $type_message ?>">
            <?= $message ?>
        </div>
        <?php if($type_message === 'success'): ?>
        <script>
            setTimeout(() => {
                window.location.href = '/web/index.php?controller=salle&action=liste';
            }, 1500);  // redirige après 1.5 secondes
        </script>
        <?php endif; ?>
    <?php endif; ?>
    <div class="main">
        <div class="topbar">
            <div class="page-title">Gestion des <span> Salles </span></div>
            <div style="font-size:.85rem;color:var(--muted)">
                <i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y') ?>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#AFEEEE">
                <i class="bi bi-building" style="color:#4682B4"></i>
            </div>
            <div>
                <div class="stat-val"><b><?= count($salles) ?></b></div>
                <div class="stat-label">Salles disponibles</div>
            </div>
        </div>         
        <div class="card-body-custom">
            <div class="card-head">
                <div class="card-head-title">
                    <i class="bi bi-calendar-check"></i>Nouvelle Salle                                
                </div>
            </div>
           <form method="POST" action="/web/index.php?controller=salle&action=ajouter">
                
                <div class="mb-3">
                    <label class="form-label-custom">Numéro de salle</label>
                    <input 
                        type="text"
                        name="numero_salle"
                        class="form-control-custom"
                        placeholder="Ex : A101"
                        title="Exemple valide : A101, B202..."
                        value="<?= htmlspecialchars($salle_edit ? $salle_edit->getNumero_salle() : '') ?>"
                        required
                    >
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Bâtiment</label>
                    <input type="text"
                        name="batiment"
                        class="form-control-custom"
                        placeholder="Ex : Bloc A, Bâtiment principal..."
                        value="<?= htmlspecialchars($salle_edit ? $salle_edit->getBatiment() : '') ?>"
                        required>
                </div>

                <button type="submit" class="btn-primary-custom">
                    <i class="bi bi-plus-lg  me-1">Ajouter la salle</i>
                    
                </button>
                
            </form>
        </div>
    </div>
        
    
</body>
</html>
