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

    .planning-card{
        background: #ffffff;
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        max-width: 850px;
        margin: auto;
    }

    .planning-title{
        font-size: 2rem;
        font-weight: 700;
        color: #23242c;
        margin-bottom: 10px;
    }

    .planning-subtitle{
        color: #6c757d;
        margin-bottom: 35px;
        font-size: 0.95rem;
    }

    .section-title{
        font-size: 1rem;
        font-weight: 600;
        color: #23242c;
        margin-bottom: 15px;
    }

    .date-grid{
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .input-group-modern{
        display: flex;
        flex-direction: column;
    }

    .input-group-modern label{
        margin-bottom: 8px;
        font-weight: 600;
        color: #495057;
    }

    .input-modern{
        border: 1px solid #dcdfe4;
        border-radius: 14px;
        padding: 14px;
        font-size: 15px;
        transition: 0.3s;
        background: #fafafa;
    }

    .input-modern:focus{
        outline: none;
        border-color: #375e69;
        box-shadow: 0 0 0 4px rgba(55,94,105,0.15);
        background: #fff;
    }

    .duration-box{
        margin-bottom: 35px;
    }

    .btn-planification{
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

    .btn-planification:hover{
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(55,94,105,0.25);
    }

    .planning-icon{
        width: 70px;
        height: 70px;
        background: rgba(55,94,105,0.1);
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 25px;
    }

    .planning-icon i{
        font-size: 32px;
        color: #375e69;
    }

    @media(max-width: 768px){

        .main-content{
            margin-left: 0;
            padding: 20px;
        }

        .planning-card{
            padding: 25px;
        }

    }

</style>

<div class="main-content">

    <div class="planning-card">

        <div class="planning-icon">
            <i class="bi bi-calendar2-check"></i>
        </div>

        <h2 class="planning-title">
            Planification automatique
        </h2>

        <p class="planning-subtitle">
            Génération intelligente des soutenances avec affectation
            automatique des salles, jurys et horaires.
        </p>

        <form method="POST"
              action="/web/index.php?controller=soutenance&action=planifier">

            <div class="section-title">
                Choisir les journées de soutenance
            </div>

            <div class="date-grid">

                <div class="input-group-modern">
                    <label>Jour 1</label>
                    <input type="date"
                           name="jours[]"
                           class="input-modern"
                           required>
                </div>

                <div class="input-group-modern">
                    <label>Jour 2</label>
                    <input type="date"
                           name="jours[]"
                           class="input-modern"
                           required>
                </div>

                <div class="input-group-modern">
                    <label>Jour 3</label>
                    <input type="date"
                           name="jours[]"
                           class="input-modern"
                           required>
                </div>

            </div>

            <div class="duration-box">

                <div class="input-group-modern">
                    <label>
                        Durée d'une soutenance (minutes)
                    </label>

                    <input type="number"
                           name="duree"
                           class="input-modern"
                           value="60"
                           min="15"
                           required>
                </div>

            </div>

            <button type="submit" class="btn-planification">
                <i class="bi bi-magic"></i>
                Générer automatiquement le planning
            </button>

        </form>

    </div>

</div>