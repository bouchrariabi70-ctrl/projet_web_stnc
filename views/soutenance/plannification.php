<?php require_once __DIR__ . '/../sidebar.html'; ?>

<style>
    body {
        background: #f4f6f9;
        font-family: 'DM Sans', sans-serif;
    }
    .main-content {
        margin-left: 260px;
        padding: 40px;
        min-height: 100vh;
    }
    .planning-card {
        background: #ffffff;
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        max-width: 850px;
        margin: auto;
    }
    .planning-title {
        font-size: 2rem;
        font-weight: 700;
        color: #23242c;
        margin-bottom: 10px;
    }
    .planning-subtitle {
        color: #6c757d;
        margin-bottom: 35px;
        font-size: 0.95rem;
    }
    .section-title {
        font-size: 1rem;
        font-weight: 600;
        color: #23242c;
        margin-bottom: 15px;
    }
    .input-group-modern {
        display: flex;
        flex-direction: column;
        margin-bottom: 30px;
    }
    .input-group-modern label {
        margin-bottom: 8px;
        font-weight: 600;
        color: #495057;
    }
    .input-modern {
        border: 1px solid #dcdfe4;
        border-radius: 14px;
        padding: 14px;
        font-size: 15px;
        transition: 0.3s;
        background: #fafafa;
        max-width: 320px;
    }
    .input-modern:focus {
        outline: none;
        border-color: #375e69;
        box-shadow: 0 0 0 4px rgba(55,94,105,0.15);
        background: #fff;
    }

    /* Aperçu des 3 jours générés */
    .jours-preview {
        display: flex;
        gap: 12px;
        margin-top: 16px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }
    .jour-badge {
        background: rgba(55,94,105,0.08);
        border: 1px dashed #375e69;
        border-radius: 12px;
        padding: 10px 18px;
        font-size: 0.88rem;
        color: #375e69;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .jour-badge i { font-size: 14px; }

    .info-box {
        background: #f0f7f9;
        border-left: 4px solid #375e69;
        border-radius: 10px;
        padding: 14px 18px;
        font-size: 0.9rem;
        color: #4a6572;
        margin-bottom: 30px;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
    .info-box i { margin-top: 2px; color: #375e69; }

    .btn-planification {
        background: linear-gradient(135deg, #375e69, #4d7d8b);
        color: white;
        border: none;
        border-radius: 14px;
        padding: 15px 30px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s;
        width: 100%;
    }
    .btn-planification:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(55,94,105,0.25);
    }
    .planning-icon {
        width: 70px;
        height: 70px;
        background: rgba(55,94,105,0.1);
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 25px;
    }
    .planning-icon i {
        font-size: 32px;
        color: #375e69;
    }
    @media(max-width: 768px) {
        .main-content { margin-left: 0; padding: 20px; }
        .planning-card { padding: 25px; }
        .input-modern { max-width: 100%; }
    }
</style>

<div class="main-content">
    <div class="planning-card">

        <div class="planning-icon">
            <i class="bi bi-calendar2-check"></i>
        </div>

        <h2 class="planning-title">Planification automatique</h2>
        <p class="planning-subtitle">
            Génération intelligente des soutenances sur 3 jours consécutifs,
            avec répartition équitable par filière et affectation automatique des jurys.
        </p>

        <form method="POST"
              action="/web/index.php?controller=soutenance&action=planifier"
              id="formPlanif">

            <div class="section-title">Date de début des soutenances</div>

            <div class="input-group-modern">
                <label for="date_debut">
                    <i class="bi bi-calendar-event"></i> Jour 1 (date de départ)
                </label>
                <input type="date"
                       id="date_debut"
                       name="date_debut"
                       class="input-modern"
                       required
                       min="<?= date('Y-m-d') ?>"
                       onchange="afficherJours(this.value)">
            </div>

            <!-- Aperçu dynamique des 3 jours -->
            <div id="jours-preview" class="jours-preview" style="display:none;">
                <div class="jour-badge"><i class="bi bi-calendar-day"></i> Jour 1 : <span id="j1"></span></div>
                <div class="jour-badge"><i class="bi bi-calendar-day"></i> Jour 2 : <span id="j2"></span></div>
                <div class="jour-badge"><i class="bi bi-calendar-day"></i> Jour 3 : <span id="j3"></span></div>
            </div>

            <div class="info-box">
                <i class="bi bi-info-circle-fill"></i>
                <span>
                    Les soutenances de <strong>tous les départements</strong> seront réparties
                    équitablement sur ces 3 jours. Durée par soutenance : <strong>30 min</strong>,
                    avec au moins <strong>60 min de repos</strong> entre deux passages pour un même jury.
                </span>
            </div>

            <button type="submit" class="btn-planification">
                <i class="bi bi-magic"></i>
                Générer automatiquement le planning
            </button>

        </form>

    </div>
</div>

<script>
function afficherJours(valeur) {
    if (!valeur) return;

    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const j1 = new Date(valeur + 'T00:00:00');
    const j2 = new Date(j1); j2.setDate(j1.getDate() + 1);
    const j3 = new Date(j1); j3.setDate(j1.getDate() + 2);

    document.getElementById('j1').textContent = j1.toLocaleDateString('fr-FR', options);
    document.getElementById('j2').textContent = j2.toLocaleDateString('fr-FR', options);
    document.getElementById('j3').textContent = j3.toLocaleDateString('fr-FR', options);

    document.getElementById('jours-preview').style.display = 'flex';
}
</script>