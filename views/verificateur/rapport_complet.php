<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification complète — GES STNC</title>
    <link href="/web/views/css/bootstrap.min.css" rel="stylesheet">
    <link href="/web/views/style.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include $_SERVER['DOCUMENT_ROOT'] . '/web/views/sidebar.html'; ?>

<div class="main-content">
    <h4 class="fw-bold mb-4">🔍 Vérification complète de conformité</h4>

    <!-- ── Affectation ── -->
    <?php $this->_renderSection(
        "📋 Fichier d'affectation",
        $rapportAffectation
    ); ?>

    <!-- ── Planning ── -->
    <?php $this->_renderSection(
        "🗓️ Fichier de planning",
        $rapportPlanning
    ); ?>

    <a href="index.php?controller=dashboard&action=index" class="btn btn-primary mt-2">
        ← Retour au tableau de bord
    </a>
</div>

<?php
/* Petite fonction locale pour éviter la répétition HTML */
function _renderSection(string $titre, array $rapport): void { ?>
    <div class="card mb-4" style="border-radius:12px; overflow:hidden; border:none;
         box-shadow: 0 2px 10px rgba(0,0,0,.08);">
        <div class="card-header fw-bold" style="background:#f3f4f6;"><?= $titre ?></div>
        <div class="card-body">
        <?php if ($rapport['ok']): ?>
            <div class="alert alert-success mb-0">✅ Aucune anomalie.</div>
        <?php else: ?>
            <ul class="list-group list-group-flush">
            <?php foreach ($rapport['alertes'] as $a): ?>
                <li class="list-group-item list-group-item-warning">
                    <?= htmlspecialchars($a) ?>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        </div>
    </div>
<?php }
?>
</body>
</html>

