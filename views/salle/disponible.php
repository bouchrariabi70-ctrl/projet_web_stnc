<?php

require_once   __DIR__ .'/../sidebar.html';


$motCle = trim($_GET['q'] ?? '');
if ($motCle !== '') {
    $salles = $salleObj->rechercher($motCle);
} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>salle_disponible</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="/web/views/salle/styleSalle.css" rel="stylesheet">
</head>
<body>
    <div class="main">
        <div class="topbar">
            <div class="page-title">Gestion des <span> Salles </span></div>
            <div style="font-size:.85rem;color:var(--muted)">
                <i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y') ?>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff7ed">
                <i class="bi bi-calendar-check" style="color:#ea580c"></i>
            </div>
            <div>
                <div class="stat-val"><b><?= count($disponibles) ?></b></div>
                <div class="stat-label">Salles disponibles</div>
            </div>
        </div>
                    
        <div class="card-head-title">
            <i class="bi bi-calendar-check"></i> Disponibilité
        </div>
                
        <div class="card-body-custom">
            <form method="GET" action="/web/index.php">
                <input type="hidden" name="controller" value="salle">
                <input type="hidden" name="action" value="disponible">
                <div class="mb-3">
                    <label class="form-label-custom">Date</label>
                    <input type="date" name="date" class="form-control-custom"
                            value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Heure début</label>
                    <input type="time" name="heure_debut" class="form-control-custom"
                        value="<?= htmlspecialchars($_GET['heure_debut'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Heure fin</label>
                    <input type="time" name="heure_fin" class="form-control-custom"
                         value="<?= htmlspecialchars($_GET['heure_fin'] ?? '') ?>">
                </div>
                <button type="submit" name="check_dispo" value="1" class="btn-primary-custom">
                    <i class="bi bi-search me-1"></i>Vérifier
                </button>
            </form>

            <?php if ($dispo_search): ?>
                <div style="margin-top:16px">
                    <?php if (empty($disponibles)): ?>
                    <div style="text-align:center;color:var(--danger);font-size:.88rem;padding:10px ">
                        <i class="bi bi-x-circle-fill me-1"></i>Aucune salle disponible
                    </div>
                    <?php else: ?>
                        <div style="font-size:.8rem;color:var(--muted);margin-bottom:8px">
                            <?= count($disponibles) ?> salle(s) disponible(s) :
                        </div>
                        <?php foreach ($disponibles as $d): ?>
                            <div class="badge-dispo mb-2" style="display:flex">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Salle <?= htmlspecialchars($d->getNumero_salle()) ?>
                                    — <?= htmlspecialchars($d->getBatiment()) ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
</body>
</html>
