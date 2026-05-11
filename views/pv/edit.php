<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier PV — GES STNC</title>
    <link href="/web/views/css/bootstrap.min.css" rel="stylesheet">
    <link href="/web/views/style.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include $_SERVER['DOCUMENT_ROOT'] . '/web/views/sidebar.html'; ?>

<div class="main-content">
    <h4 class="fw-bold mb-4">✏️ Modifier le PV #<?= $data['id'] ?></h4>

    <div class="card p-4" style="border-radius:12px; border:none; box-shadow:0 2px 10px rgba(0,0,0,.08); max-width:620px;">
        <form method="POST" action="index.php?controller=pv&action=update">
            <input type="hidden" name="id" value="<?= $data['id'] ?>">

            <div class="row g-3 mb-3">
                <div class="col-4">
                    <label class="form-label fw-semibold">Note contenu</label>
                    <input type="number" name="note_contenu" class="form-control"
                           min="0" max="20" step="0.25"
                           value="<?= $data['note_contenu'] ?>" required>
                </div>
                <div class="col-4">
                    <label class="form-label fw-semibold">Note mémoire</label>
                    <input type="number" name="note_memoire" class="form-control"
                           min="0" max="20" step="0.25"
                           value="<?= $data['note_memoire'] ?>" required>
                </div>
                <div class="col-4">
                    <label class="form-label fw-semibold">Note oral</label>
                    <input type="number" name="note_soutenance" class="form-control"
                           min="0" max="20" step="0.25"
                           value="<?= $data['note_soutenance'] ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Président du jury</label>
                <select name="president_jury_id" class="form-select" required>
                    <?php foreach ($profs as $p): ?>
                    <option value="<?= $p['id_prof'] ?>"
                        <?= $p['id_prof'] == $data['president_jury_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Date de signature</label>
                <input type="date" name="date_signature" class="form-control"
                       value="<?= $data['date_signature'] ?>" required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Statut</label>
                <select name="statut" class="form-select">
                    <option value="brouillon" <?= $data['statut']==='brouillon'?'selected':'' ?>>Brouillon</option>
                    <option value="validé"    <?= $data['statut']==='validé'   ?'selected':'' ?>>Validé</option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="index.php?controller=pv&action=index" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>

