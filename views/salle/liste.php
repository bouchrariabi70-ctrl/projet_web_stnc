<?php
require_once   __DIR__ .'/../sidebar.html';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>liste_salle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="/web/views/salle/styleSalle.css" rel="stylesheet">
</head>
<body>
    <div class="main">
        <div class="topbar">
            <div class="page-title">Gestion des <span> Salles </span></div>
            <div style="font-size:.85rem;color:var(--muted)">
                <i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y') ?>
            </div>
        </div>
        <div class="card-body-custom" style="padding-bottom:8px">
            <div class="card-head">
                <div class="card-head-title">
                    <i class="bi bi-list-ul"></i>
                        Liste des salles
                    <span style="background:#eef2ff;color:var(--accent);font-size:.75rem;padding:2px 8px;border-radius:20px;font-weight:600">
                        <?= count($salles) ?>
                    </span>
                </div>
            </div>
            

            <!-- Recherche -->
                <form method="GET" action="/web/index.php">
                    <input type="hidden" name="controller" value="salle">
                    <input type="hidden" name="action" value="liste">
                    <div class="search-wrap">
                        <i class="bi bi-search"></i>
                        <input type="text"
                                    name="q"
                                    class="search-input"
                                    placeholder="Rechercher par numéro ou bâtiment..."
                                    value="<?= htmlspecialchars($motCle) ?>"
                                    oninput="this.form.submit()">
                    </div>
                </form>
            
            
                
            

            <!-- Tableau -->
            <?php if (empty($salles)): ?>
                <div class="empty-state">
                    <i class="bi bi-building-slash"></i>
                    <p><?= $motCle ? "Aucun résultat pour \"$motCle\"" : "Aucune salle enregistrée." ?></p>
                </div>
            <?php else: ?>
                <div style="overflow-x:auto">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Numero</th>
                                <th>Bâtiment</th>
                                <th>ID Salle</th>
                                <th style="text-align:center">Supprimer</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($salles as $i => $s): ?>
                                <tr>
                                    <td style="color:var(--muted);font-size:.82rem"><?= $i + 1 ?></td>
                                    <td>
                                        <span class="badge-salle">
                                            <i class="bi bi-door-open"></i>
                                            <?= htmlspecialchars($s->getNumero_salle()) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($s->getBatiment()): ?>
                                            <span class="badge-batiment">
                                            <i class="bi bi-building"></i>
                                            <?= htmlspecialchars($s->getBatiment()) ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color:var(--muted)">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color:var(--muted);font-size:.85rem"><?= $s->getId_salle() ?></td>
                                    <td style="text-align:center">
                                        
                                        <a href="/web/index.php?controller=salle&action=supprimer&id=<?= $s->getId_salle() ?>"
                                            class="btn-icon del ms-1" title="Supprimer"
                                            onclick="return confirm('Supprimer cette salle ?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>   
                    
     
    
</body>
</html>

