<?php require_once __DIR__ . '/../sidebar.html'; ?>

<style>

    body{
        background: #f4f6f9;
        font-family: 'DM Sans', sans-serif;
    }

    .main-content{
        margin-left: 260px;
        padding: 40px;
        min-height: 100vh;
    }

    .result-card{
        background: #ffffff;
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    }

    .page-header{
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 35px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .page-title{
        font-size: 2rem;
        font-weight: 700;
        color: #23242c;
        margin: 0;
    }

    .page-subtitle{
        color: #6c757d;
        margin-top: 8px;
        font-size: 0.95rem;
    }

    .stats-grid{
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
        margin-bottom: 40px;
    }

    .stat-card{
        border-radius: 20px;
        padding: 25px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .stat-success{
        background: linear-gradient(135deg, #28a745, #48c774);
    }

    .stat-warning{
        background: linear-gradient(135deg, #ff9800, #ffb74d);
    }

    .stat-icon{
        position: absolute;
        top: 20px;
        right: 20px;
        opacity: 0.2;
        font-size: 55px;
    }

    .stat-title{
        font-size: 1rem;
        margin-bottom: 10px;
    }

    .stat-number{
        font-size: 2.5rem;
        font-weight: 700;
    }

    .conflict-section{
        margin-top: 30px;
    }

    .section-title{
        font-size: 1.3rem;
        font-weight: 700;
        color: #23242c;
        margin-bottom: 20px;
    }

    .modern-table{
        width: 100%;
        border-collapse: collapse;
        overflow: hidden;
        border-radius: 18px;
        background: white;
    }

    .modern-table thead{
        background: #375e69;
        color: white;
    }

    .modern-table th{
        padding: 18px;
        text-align: left;
        font-weight: 600;
        font-size: 14px;
    }

    .modern-table td{
        padding: 18px;
        border-bottom: 1px solid #f1f1f1;
        color: #495057;
    }

    .modern-table tbody tr{
        transition: 0.3s;
    }

    .modern-table tbody tr:hover{
        background: #f8f9fb;
    }

    .badge-danger{
        background: rgba(220,53,69,0.12);
        color: #dc3545;
        padding: 8px 14px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
    }

    .success-banner{
        background: linear-gradient(135deg, #375e69, #4d7d8b);
        color: white;
        border-radius: 20px;
        padding: 25px;
        margin-bottom: 35px;
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .success-banner i{
        font-size: 50px;
    }

    .success-banner h3{
        margin: 0;
        font-size: 1.5rem;
    }

    .success-banner p{
        margin: 5px 0 0;
        opacity: 0.9;
    }

    .btn-retour{
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-top: 35px;
        background: #23242c;
        color: white;
        padding: 14px 24px;
        border-radius: 14px;
        text-decoration: none;
        transition: 0.3s;
        font-weight: 600;
    }

    .btn-retour:hover{
        background: #375e69;
        transform: translateY(-2px);
    }

    @media(max-width: 768px){

        .main-content{
            margin-left: 0;
            padding: 20px;
        }

        .result-card{
            padding: 25px;
        }

        .page-header{
            flex-direction: column;
            align-items: flex-start;
        }

    }

</style>

<div class="main-content">

    <div class="result-card">

        <div class="success-banner">

            <i class="bi bi-check2-circle"></i>

            <div>
                <h3>Planification terminée</h3>

                <p>
                    Le système a analysé automatiquement
                    les salles, jurys et créneaux disponibles.
                </p>
            </div>

        </div>

        <div class="page-header">

            <div>
                <h2 class="page-title">
                    Résultat de la planification
                </h2>

                <div class="page-subtitle">
                    Rapport global des soutenances générées automatiquement
                </div>
            </div>

        </div>

        <div class="stats-grid">

            <div class="stat-card stat-success">

                <i class="bi bi-check-circle-fill stat-icon"></i>

                <div class="stat-title">
                    Soutenances affectées
                </div>

                <div class="stat-number">
                    <?= $rapport['affectees'] ?>
                </div>

            </div>

            <div class="stat-card stat-warning">

                <i class="bi bi-exclamation-triangle-fill stat-icon"></i>

                <div class="stat-title">
                    Non affectées
                </div>

                <div class="stat-number">
                    <?= $rapport['non_affectees'] ?>
                </div>

            </div>

        </div>

        <?php if (!empty($rapport['conflits'])): ?>

            <div class="conflict-section">

                <h3 class="section-title">
                    Conflits détectés
                </h3>

                <table class="modern-table">

                    <thead>
                        <tr>
                            <th>ID Soutenance</th>
                            <th>Filière</th>
                            <th>Raison</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php foreach ($rapport['conflits'] as $c): ?>

                        <tr>

                            <td>
                                #<?= $c['id_stnc'] ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($c['filiere']) ?>
                            </td>

                            <td>
                                <span class="badge-danger">
                                    <?= htmlspecialchars($c['raison']) ?>
                                </span>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

        <a href="/web/index.php?controller=soutenance&action=index"
           class="btn-retour">

            <i class="bi bi-arrow-left"></i>

            Retour aux soutenances

        </a>

    </div>

</div>