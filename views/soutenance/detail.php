<?php
// views/soutenance/detail.php
// Données attendues depuis ControllerSoutenance::detail() :
//   $soutenance → array  (enregistrement complet avec jointures)
//   $jury       → array  (membres du jury)
//   $pv         → array|false  (PV associé s'il existe)
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail soutenance</title>
    <link rel="stylesheet" href="/views/css/bootstrap.min.css">
    <link rel="stylesheet" href="/views/style.css">
    <style>
        .info-label { font-size: .78rem; text-transform: uppercase; letter-spacing: .05em; color: #6c757d; }
        .info-value { font-size: 1rem; font-weight: 500; }
        .section-title { font-size: 1rem; font-weight: 600; border-left: 4px solid #0d6efd; padding-left: .6rem; margin-bottom: 1rem; }
    </style>
</head>
<body>

<?php include __DIR__ . '/../sidebar.html'; ?>

<div class="main-content">

    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">👁 Détail de la soutenance</h2>
        <div class="d-flex gap-2 flex-wrap">
            <a href="index.php?action=soutenance_modifier&id=<?= $soutenance['id'] ?>" class="btn btn-warning btn-sm">✏️ Modifier</a>
            <?php if (!$pv): ?>
                <a href="index.php?action=pv_create&soutenance_id=<?= $soutenance['id'] ?>" class="btn btn-success btn-sm">📄 Créer le PV</a>
            <?php else: ?>
                <a href="index.php?action=pv_show&id=<?= $pv['id'] ?>" class="btn btn-outline-success btn-sm">📄 Voir le PV</a>
            <?php endif; ?>
            <a href="index.php?action=soutenance_liste" class="btn btn-outline-secondary btn-sm">← Liste</a>
        </div>
    </div>

    <!-- Statut -->
    <?php
    $badges = [
        'planifiee' => ['primary',  'Planifiée'],
        'en_cours'  => ['warning',  'En cours'],
        'terminee'  => ['success',  'Terminée'],
        'annulee'   => ['danger',   'Annulée'],
    ];
    [$cls, $label] = $badges[$soutenance['statut']] ?? ['secondary', $soutenance['statut']];
    ?>
    <div class="mb-4">
        <span class="badge bg-<?= $cls ?> fs-6 px-3 py-2"><?= $label ?></span>
    </div>

    <div class="row g-4">

        <!-- ── Colonne gauche ── -->
        <div class="col-lg-8">

            <!-- Étudiant & Sujet -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <p class="section-title">🎓 Étudiant & Sujet PFE</p>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="info-label">Étudiant</div>
                            <div class="info-value">
                                <?= htmlspecialchars($soutenance['etudiant_nom'] . ' ' . $soutenance['etudiant_prenom']) ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">CNE</div>
                            <div class="info-value"><?= htmlspecialchars($soutenance['etudiant_cne'] ?? '—') ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Filière</div>
                            <div class="info-value">
                                <span class="badge bg-secondary"><?= htmlspecialchars($soutenance['etudiant_filiere'] ?? '—') ?></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="info-label">Sujet PFE</div>
                            <div class="info-value"><?= htmlspecialchars($soutenance['sujet_titre'] ?? '—') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Planification -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <p class="section-title">📅 Planification</p>
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="info-label">Date</div>
                            <div class="info-value"><?= date('d/m/Y', strtotime($soutenance['date_soutenance'])) ?></div>
                        </div>
                        <div class="col-sm-4">
                            <div class="info-label">Créneau</div>
                            <div class="info-value">
                                <?php if ($soutenance['creneau'] === 'matin'): ?>
                                    <span class="badge bg-warning text-dark">🌅 Matinée</span>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark">🌇 Après-midi</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="info-label">Horaire</div>
                            <div class="info-value">
                                <?= htmlspecialchars(substr($soutenance['heure_debut'], 0, 5)) ?>
                                → <?= htmlspecialchars(substr($soutenance['heure_fin'], 0, 5)) ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Salle</div>
                            <div class="info-value"><?= htmlspecialchars($soutenance['salle_nom'] ?? '—') ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Encadrant</div>
                            <div class="info-value">
                                <?= htmlspecialchars(($soutenance['encadrant_nom'] ?? '') . ' ' . ($soutenance['encadrant_prenom'] ?? '')) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jury -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <p class="section-title">👨‍⚖️ Composition du jury</p>
                    <?php if (empty($jury)): ?>
                        <p class="text-muted">Aucun membre de jury enregistré.</p>
                    <?php else: ?>
                        <table class="table table-sm align-middle">
                            <thead><tr><th>Professeur</th><th>Rôle</th></tr></thead>
                            <tbody>
                            <?php foreach ($jury as $m): ?>
                                <tr>
                                    <td><?= htmlspecialchars($m['nom'] . ' ' . $m['prenom']) ?></td>
                                    <td>
                                        <?php
                                        $roleMap = ['president' => '👑 Président', 'membre' => '👤 Membre', 'encadrant' => '🎓 Encadrant'];
                                        echo $roleMap[$m['role']] ?? htmlspecialchars($m['role']);
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Remarques -->
            <?php if (!empty($soutenance['remarques'])): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <p class="section-title">📝 Remarques</p>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($soutenance['remarques'])) ?></p>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- ── Colonne droite ── -->
        <div class="col-lg-4">

            <!-- PV -->
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <p class="section-title text-center" style="border:none;padding:0">📄 Procès-verbal</p>
                    <?php if ($pv): ?>
                        <p class="text-success fw-semibold">✔ PV enregistré</p>
                        <div class="d-grid gap-2">
                            <a href="index.php?action=pv_show&id=<?= $pv['id'] ?>" class="btn btn-outline-success btn-sm">Consulter</a>
                            <a href="index.php?action=pv_edit&id=<?= $pv['id'] ?>" class="btn btn-outline-warning btn-sm">Modifier</a>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Aucun PV pour cette soutenance.</p>
                        <a href="index.php?action=pv_create&soutenance_id=<?= $soutenance['id'] ?>"
                           class="btn btn-success btn-sm w-100">Créer le PV</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <p class="section-title">⚡ Actions</p>
                    <div class="d-grid gap-2">
                        <a href="index.php?action=soutenance_modifier&id=<?= $soutenance['id'] ?>" class="btn btn-warning btn-sm">✏️ Modifier</a>
                        <a href="index.php?action=soutenance_supprimer&id=<?= $soutenance['id'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Supprimer définitivement cette soutenance ?')">🗑 Supprimer</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
