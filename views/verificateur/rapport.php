<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($titre) ?> — GES STNC</title>
    <link href="/web/views/css/bootstrap.min.css" rel="stylesheet">
    <link href="/web/views/style.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include $_SERVER['DOCUMENT_ROOT'] . '/web/views/sidebar.html'; ?>

<div class="main-content">
    <div class="d-flex align-items-center gap-3 mb-4">
        <h4 class="fw-bold mb-0">🔍 <?= htmlspecialchars($titre) ?></h4>
        <a href="index.php?controller=verificateur&action=tout" class="btn btn-outline-secondary btn-sm ms-auto">
            Tout vérifier
        </a>
    </div>

    <?php if ($rapport['ok']): ?>
    <div class="alert alert-success d-flex align-items-center gap-2" style="border-radius:12px;">
        <span style="font-size:1.5rem;">✅</span>
        <strong>Aucune anomalie détectée — tout est conforme.</strong>
    </div>
    <?php else: ?>
    <div class="alert alert-warning mb-3" style="border-radius:12px;">
        <strong>⚠️ <?= count($rapport['alertes']) ?> anomalie(s) détectée(s) :</strong>
    </div>
    <ul class="list-group mb-4">
        <?php foreach ($rapport['alertes'] as $alerte): ?>
        <li class="list-group-item list-group-item-warning" style="border-radius:8px; margin-bottom:.4rem;">
            <?= htmlspecialchars($alerte) ?>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <div class="d-flex gap-2">
        <a href="index.php?controller=dashboard&action=index" class="btn btn-primary">
            ← Retour au tableau de bord
        </a>
        <a href="javascript:window.location.reload()" class="btn btn-outline-secondary">
            🔄 Relancer la vérification
        </a>
    </div>
</div>
</body>
</html>

