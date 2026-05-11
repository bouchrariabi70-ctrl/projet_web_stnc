<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouveau PV — GES STNC</title>
    <link href="/web/views/css/bootstrap.min.css" rel="stylesheet">
    <link href="/web/views/style.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include $_SERVER['DOCUMENT_ROOT'] . '/web/views/sidebar.html'; ?>

<div class="main-content">
    <h4 class="fw-bold mb-4">📝 Nouveau procès-verbal</h4>

    <?php if (!empty($erreur)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <div class="card p-4" style="border-radius:12px; border:none; box-shadow:0 2px 10px rgba(0,0,0,.08); max-width:620px;">
        <form method="POST" action="index.php?controller=pv&action=store">

            <div class="mb-3">
                <label class="form-label fw-semibold">Soutenance</label>
                <select name="soutenance_id" class="form-select" required>
                    <option value="">— Choisir —</option>
                    <?php foreach ($soutenances as $s): ?>
                    <option value="<?= $s['id_stnc'] ?>">
                        #<?= $s['id_stnc'] ?> — <?= htmlspecialchars($s['prenom'] . ' ' . $s['nom']) ?>
                        (<?= $s['date'] ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-4">
                    <label class="form-label fw-semibold">Note contenu <small class="text-muted">(×0.5)</small></label>
                    <input type="number" name="note_contenu" class="form-control"
                           min="0" max="20" step="0.25" required>
                </div>
                <div class="col-4">
                    <label class="form-label fw-semibold">Note mémoire <small class="text-muted">(×0.2)</small></label>
                    <input type="number" name="note_memoire" class="form-control"
                           min="0" max="20" step="0.25" required>
                </div>
                <div class="col-4">
                    <label class="form-label fw-semibold">Note oral <small class="text-muted">(×0.3)</small></label>
                    <input type="number" name="note_soutenance" class="form-control"
                           min="0" max="20" step="0.25" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Président du jury</label>
                <select name="president_jury_id" class="form-select" required>
                    <option value="">— Choisir —</option>
                    <?php foreach ($profs as $p): ?>
                    <option value="<?= $p['id_prof'] ?>">
                        <?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Date de signature</label>
                <input type="date" name="date_signature" class="form-control"
                       value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Statut</label>
                <select name="statut" class="form-select">
                    <option value="brouillon">Brouillon</option>
                    <option value="validé">Validé</option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Enregistrer le PV</button>
                <a href="index.php?controller=pv&action=index" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>

