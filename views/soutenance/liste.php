<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des soutenances</title>
    <link href="/web/views/css/bootstrap.min.css" rel="stylesheet">
    <link href="/web/views/css/style.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php require_once($_SERVER['DOCUMENT_ROOT'].'/web/views/sidebar.html'); ?>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success" style="margin-left:250px;">
        Suppression avec succès
    </div>
<?php endif; ?>

<div class="main-content" style="margin-left:250px; padding:1.5rem;">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="form-title">🎓 Liste des soutenances</h2>
            <p class="text-muted">Gestion des soutenances PFE</p>
        </div>
    </div>

    <!-- TABLEAU -->
    <?php if (empty($soutenances)): ?>
        <div class="alert alert-warning">Aucune soutenance trouvée</div>
    <?php else: ?>
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Titre PFE</th>
                    <th>Date</th>
                    <th>Début</th>
                    <th>Fin</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($soutenances as $index => $s): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($s['titre_pfe'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($s['date'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($s['heure_debut'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($s['heure_fin'] ?? '-') ?></td>
                    <td>
                        <?php if (($s['statut'] ?? '') == 'planifiée'): ?>
                            <span class="badge bg-success">Planifiée</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">
                                <?= htmlspecialchars($s['statut']) ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <a href="/web/index.php?controller=soutenance&action=update&id=<?= $s['id_stnc']; ?>"
                           class="btn btn-warning btn-sm">✏️</a>
                        <a href="/web/index.php?controller=soutenance&action=supprimer&id=<?= $s['id_stnc']; ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Supprimer cette soutenance ?')">🗑️</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>

</body>
</html>