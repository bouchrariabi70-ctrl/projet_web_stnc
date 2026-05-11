<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord — GES STNC</title>
    <link href="/web/views/css/bootstrap.min.css" rel="stylesheet">
    <link href="/web/views/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .kpi-card { border-radius: 12px; padding: 1.4rem 1.2rem; color: #fff; }
        .kpi-card .kpi-number { font-size: 2.4rem; font-weight: 800; line-height: 1; }
        .kpi-card .kpi-label  { font-size: .85rem; opacity: .85; margin-top: .3rem; }
        .chart-card { background: #fff; border-radius: 12px; padding: 1.4rem;
                      box-shadow: 0 2px 10px rgba(0,0,0,.08); margin-bottom: 1.5rem; }
        .chart-card h6 { font-weight: 700; margin-bottom: 1rem; color: #374151; }
        .badge-ok      { background: #d1fae5; color: #065f46; padding: .3rem .75rem;
                         border-radius: 999px; font-weight: 600; font-size: .8rem; }
        .badge-warning { background: #fef3c7; color: #92400e; padding: .3rem .75rem;
                         border-radius: 999px; font-weight: 600; font-size: .8rem; }
    </style>
</head>
<body class="bg-light">
<?php include $_SERVER['DOCUMENT_ROOT'] . '/web/views/sidebar.html'; ?>

<div class="main-content">
    <h3 class="fw-bold mb-4">📊 Tableau de bord</h3>

    <!-- ── KPI Cards ── -->
    <div class="row g-3 mb-4">
        <?php
        $kpis = [
            ['label'=>'Étudiants',       'val'=>$globaux['nb_etudiants'],   'bg'=>'#4f46e5'],
            ['label'=>'Professeurs',      'val'=>$globaux['nb_profs'],       'bg'=>'#0891b2'],
            ['label'=>'Soutenances',      'val'=>$globaux['nb_soutenances'], 'bg'=>'#059669'],
            ['label'=>'Salles',           'val'=>$globaux['nb_salles'],      'bg'=>'#d97706'],
            ['label'=>'PV rédigés',       'val'=>$globaux['nb_pv'],          'bg'=>'#7c3aed'],
            ['label'=>'Sans salle ⚠️',   'val'=>$globaux['nb_sans_salle'],  'bg'=>($globaux['nb_sans_salle']>0?'#dc2626':'#16a34a')],
        ];
        foreach ($kpis as $k): ?>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="kpi-card" style="background:<?= $k['bg'] ?>">
                <div class="kpi-number"><?= $k['val'] ?></div>
                <div class="kpi-label"><?= $k['label'] ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── Moyenne encadrement ── -->
    <div class="alert alert-info d-flex align-items-center gap-2 mb-4" style="border-radius:10px;">
        <span style="font-size:1.3rem;">📐</span>
        <span>Moyenne d'encadrement par professeur : 
            <strong><?= $moyenneEncadrement ?> étudiant(s)/prof</strong>
        </span>
        <a href="index.php?controller=verificateur&action=affectation" 
           class="ms-auto btn btn-sm btn-outline-primary">
            Vérifier la conformité →
        </a>
    </div>

    <!-- ── Row 1 : étudiants/prof + soutenances/prof ── -->
    <div class="row g-4 mb-2">
        <div class="col-md-6">
            <div class="chart-card">
                <h6>Étudiants encadrés par professeur</h6>
                <canvas id="chartEtudiantsParProf" height="220"></canvas>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-card">
                <h6>Soutenances par professeur (jury)</h6>
                <canvas id="chartSoutenancesParProf" height="220"></canvas>
            </div>
        </div>
    </div>

    <!-- ── Row 2 : filières + mentions ── -->
    <div class="row g-4 mb-2">
        <div class="col-md-6">
            <div class="chart-card">
                <h6>Soutenances par filière</h6>
                <canvas id="chartFilieres" height="220"></canvas>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-card">
                <h6>Répartition des mentions</h6>
                <canvas id="chartMentions" height="220"></canvas>
            </div>
        </div>
    </div>

    <!-- ── Row 3 : activité mensuelle ── -->
    <div class="row g-4">
        <div class="col-12">
            <div class="chart-card">
                <h6>Activité mensuelle des soutenances</h6>
                <canvas id="chartMensuel" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
/* ── Données PHP → JS ── */
const etudiantsParProf   = <?= json_encode($etudiantsParProf,   JSON_UNESCAPED_UNICODE) ?>;
const soutenancesParProf = <?= json_encode($soutenancesParProf, JSON_UNESCAPED_UNICODE) ?>;
const filieres           = <?= json_encode($soutenancesParFil,  JSON_UNESCAPED_UNICODE) ?>;
const mentions           = <?= json_encode($mentions,           JSON_UNESCAPED_UNICODE) ?>;
const mensuel            = <?= json_encode($activiteMensuelle,  JSON_UNESCAPED_UNICODE) ?>;

/* ── Palette ── */
const COLORS = ['#4f46e5','#0891b2','#059669','#d97706','#7c3aed',
                '#db2777','#ea580c','#16a34a','#2563eb','#9333ea'];

function labels(arr, key) { return arr.map(r => r[key]); }
function vals(arr, key)   { return arr.map(r => +r[key]); }

/* 1 - Étudiants / prof */
new Chart(document.getElementById('chartEtudiantsParProf'), {
    type: 'bar',
    data: {
        labels: etudiantsParProf.map(r => r.prenom + ' ' + r.nom),
        datasets: [{
            label: 'Étudiants encadrés',
            data:  vals(etudiantsParProf, 'nb_etudiants'),
            backgroundColor: '#4f46e5',
            borderRadius: 6
        }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

/* 2 - Soutenances / prof */
new Chart(document.getElementById('chartSoutenancesParProf'), {
    type: 'bar',
    data: {
        labels: soutenancesParProf.map(r => r.prenom + ' ' + r.nom),
        datasets: [{
            label: 'Soutenances',
            data:  vals(soutenancesParProf, 'nb_soutenances'),
            backgroundColor: '#0891b2',
            borderRadius: 6
        }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

/* 3 - Filières */
new Chart(document.getElementById('chartFilieres'), {
    type: 'doughnut',
    data: {
        labels: labels(filieres, 'filiere'),
        datasets: [{
            data: vals(filieres, 'nb_soutenances'),
            backgroundColor: COLORS
        }]
    }
});

/* 4 - Mentions */
const mentionColors = {
    'Très Bien': '#059669', 'Bien': '#0891b2',
    'Assez Bien': '#d97706', 'Passable': '#7c3aed', 'Ajourné': '#dc2626'
};
new Chart(document.getElementById('chartMentions'), {
    type: 'pie',
    data: {
        labels: labels(mentions, 'mention'),
        datasets: [{
            data: vals(mentions, 'nb'),
            backgroundColor: mentions.map(m => mentionColors[m.mention] ?? '#9ca3af')
        }]
    }
});

/* 5 - Activité mensuelle */
new Chart(document.getElementById('chartMensuel'), {
    type: 'line',
    data: {
        labels: labels(mensuel, 'mois'),
        datasets: [{
            label: 'Soutenances',
            data: vals(mensuel, 'nb_soutenances'),
            borderColor: '#4f46e5',
            backgroundColor: 'rgba(79,70,229,.15)',
            fill: true,
            tension: .4,
            pointRadius: 5
        }]
    },
    options: { scales: { y: { beginAtZero: true } } }
});
</script>
</body>
</html>

