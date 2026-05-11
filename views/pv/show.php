<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>PV #<?= $data['id'] ?> — GES STNC</title>
    <link href="/web/views/css/bootstrap.min.css" rel="stylesheet">
    <link href="/web/views/style.css" rel="stylesheet">
    <style>
        .pv-box { max-width: 640px; background: #fff; border-radius: 16px;
                  box-shadow: 0 2px 16px rgba(0,0,0,.1); padding: 2rem; }
        .pv-header { border-bottom: 2px solid #e5e7eb; padding-bottom: 1rem; margin-bottom: 1.5rem; }
        .note-block { background: #f9fafb; border-radius: 10px; padding: 1rem; text-align:center; }
        .note-block .note-val { font-size: 2rem; font-weight: 800; color: #4f46e5; }
        .note-block .note-label { font-size: .8rem; color: #6b7280; }
        .mention-badge { font-size: 1rem; padding: .5rem 1.2rem; border-radius: 999px; font-weight: 700; }
    </style>
</head>
<body class="bg-light">
<?php include $_SERVER['DOCUMENT_ROOT'] . '/web/views/sidebar.html'; ?>

<div class="main-content">
    <div class="pv-box">
        <div class="pv-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="fw-bold mb-0">Procès-verbal #<?= $data['id'] ?></h5>
                    <small class="text-muted">Soutenance #<?= $data['soutenance_id'] ?></small>
                </div>
                <?php
                $statusColor = $data['statut'] === 'validé' ? 'success' : 'warning';
                $statusLabel = $data['statut'] === 'validé' ? 'Validé ✔' : 'Brouillon';
                ?>
                <span class="badge bg-<?= $statusColor ?> fs-6"><?= $statusLabel ?></span>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-4">
                <div class="note-block">
                    <div class="note-val"><?= $data['note_contenu'] ?></div>
                    <div class="note-label">Contenu (×0.5)</div>
                </div>
            </div>
            <div class="col-4">
                <div class="note-block">
                    <div class="note-val"><?= $data['note_memoire'] ?></div>
                    <div class="note-label">Mémoire (×0.2)</div>
                </div>
            </div>
            <div class="col-4">
                <div class="note-block">
                    <div class="note-val"><?= $data['note_soutenance'] ?></div>
                    <div class="note-label">Oral (×0.3)</div>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4 p-3"
             style="background:#f3f4f6; border-radius:10px;">
            <div>
                <div class="text-muted small">Moyenne finale</div>
                <div style="font-size:2rem; font-weight:800; color:#111;"><?= $data['moyenne'] ?>/20</div>
            </div>
            <?php
            $mentionColors = [
                'Très Bien'=>'success','Bien'=>'info',
                'Assez Bien'=>'primary','Passable'=>'warning','Ajourné'=>'danger'
            ];
            $mc = $mentionColors[$data['mention']] ?? 'secondary';
            ?>
            <span class="badge bg-<?= $mc ?> mention-badge"><?= $data['mention'] ?></span>
        </div>

        <table class="table table-borderless table-sm mb-4">
            <tr>
                <td class="text-muted">Président du jury</td>
                <td class="fw-semibold">#<?= $data['president_jury_id'] ?></td>
            </tr>
            <tr>
                <td class="text-muted">Date de signature</td>
                <td class="fw-semibold"><?= $data['date_signature'] ?></td>
            </tr>
            <tr>
                <td class="text-muted">Créé le</td>
                <td><?= $data['created_at'] ?></td>
            </tr>
        </table>

        <div class="d-flex gap-2">
            <a href="index.php?controller=pv&action=edit&id=<?= $data['id'] ?>"
               class="btn btn-outline-secondary">✏️ Modifier</a>
            <?php if ($data['statut'] !== 'validé'): ?>
            <a href="index.php?controller=pv&action=valider&id=<?= $data['id'] ?>"
               class="btn btn-success"
               onclick="return confirm('Valider ce PV définitivement ?')">✔ Valider</a>
            <?php endif; ?>
            <a href="index.php?controller=pv&action=index"
               class="btn btn-outline-primary ms-auto">← Liste</a>
        </div>
    </div>
</div>
</body>
</html>

