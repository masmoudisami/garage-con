<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Devis - Garage Auto Service</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f4f4; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; background: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        
        /* Bannière */
        .banner-container {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px 0;
            border-bottom: 2px solid #ecf0f1;
        }
        .banner-container img {
            max-width: 100%;
            height: auto;
            max-height: 150px;
            object-fit: contain;
        }
        
        h1 { color: #2c3e50; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px; }
        .actions { margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
        .btn { display: inline-block; padding: 10px 15px; background: #3498db; color: #fff; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; font-size: 14px; }
        .btn:hover { opacity: 0.9; }
        .btn-danger { background: #e74c3c; }
        .btn-success { background: #2ecc71; }
        .btn-warning { background: #f1c40f; color: #000; }
        .btn-info { background: #17a2b8; }
        .btn-purple { background: #9b59b6; }
        .btn-secondary { background: #95a5a6; }
        .btn-pdf { background: #e74c3c; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-bottom: 20px; }
        .stat-card { background: #ecf0f1; padding: 15px; border-radius: 5px; text-align: center; }
        .stat-card h3 { margin: 0; font-size: 24px; color: #2c3e50; }
        .stat-card p { margin: 5px 0 0; font-size: 12px; color: #666; }
        
        .filters { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; align-items: flex-end; }
        .filter-group { display: flex; flex-direction: column; }
        .filter-group label { font-size: 0.8em; margin-bottom: 3px; color: #666; }
        .filters input, .filters select { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #2c3e50; color: #fff; }
        tr:hover { background: #f9f9f9; }
        .text-right { text-align: right; }
        
        .status-badge { padding: 4px 10px; border-radius: 3px; font-size: 11px; font-weight: bold; display: inline-block; }
        .status-draft { background: #95a5a6; color: #fff; }
        .status-sent { background: #3498db; color: #fff; }
        .status-accepted { background: #2ecc71; color: #fff; }
        .status-rejected { background: #e74c3c; color: #fff; }
        .status-expired { background: #f39c12; color: #fff; }
        
        .table-actions { display: flex; gap: 5px; flex-wrap: wrap; }
        .table-actions .btn { padding: 6px 12px; font-size: 13px; }
        
        @media screen and (max-width: 768px) {
            .banner-container { padding: 15px 0; margin-bottom: 20px; }
            .banner-container img { max-height: 100px; }
            .actions { flex-direction: column; }
            .actions .btn { width: 100%; text-align: center; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .filters { flex-direction: column; }
            .filters .filter-group { width: 100%; }
            .filters input, .filters select { width: 100%; }
            table { font-size: 12px; }
            th, td { padding: 8px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Bannière -->
        <div class="banner-container">
            <img src="assets/banniere.png" alt="Bannière Garage Auto Service">
        </div>
        
        <h1>📋 Gestion des Devis</h1>
        
        <div class="actions">
            <a href="index.php?route=invoices" class="btn btn-secondary">↩️ Tableau de Bord</a>
            <a href="index.php?route=devis_create" class="btn btn-success">➕ Nouveau Devis</a>
            <a href="index.php?route=devis" class="btn btn-info">🔄 Actualiser</a>
        </div>
        
        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $stats['total'] ?? 0; ?></h3>
                <p>Total Devis</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['draft'] ?? 0; ?></h3>
                <p>📝 Brouillons</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['sent'] ?? 0; ?></h3>
                <p>📤 Envoyés</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['accepted'] ?? 0; ?></h3>
                <p>✅ Acceptés</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['rejected'] ?? 0; ?></h3>
                <p>❌ Refusés</p>
            </div>
        </div>
        
        <!-- Filtres -->
        <form method="GET" class="filters">
            <input type="hidden" name="route" value="devis">
            <div class="filter-group">
                <label>Recherche Client</label>
                <input type="text" name="search" placeholder="Nom du client..." value="<?php echo htmlspecialchars($filters['search']); ?>">
            </div>
            <div class="filter-group">
                <label>Statut</label>
                <select name="status">
                    <option value="">Tous les statuts</option>
                    <option value="draft" <?php if($filters['status'] == 'draft') echo 'selected'; ?>>📝 Brouillon</option>
                    <option value="sent" <?php if($filters['status'] == 'sent') echo 'selected'; ?>>📤 Envoyé</option>
                    <option value="accepted" <?php if($filters['status'] == 'accepted') echo 'selected'; ?>>✅ Accepté</option>
                    <option value="rejected" <?php if($filters['status'] == 'rejected') echo 'selected'; ?>>❌ Refusé</option>
                    <option value="expired" <?php if($filters['status'] == 'expired') echo 'selected'; ?>>⏰ Expiré</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Date Début</label>
                <input type="date" name="date_start" value="<?php echo htmlspecialchars($filters['date_start']); ?>">
            </div>
            <div class="filter-group">
                <label>Date Fin</label>
                <input type="date" name="date_end" value="<?php echo htmlspecialchars($filters['date_end']); ?>">
            </div>
            <button type="submit" class="btn">🔍 Filtrer</button>
            <a href="index.php?route=devis" class="btn btn-secondary">✕ Clear</a>
        </form>
        
        <!-- Tableau des Devis -->
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">N°</th>
                    <th style="width: 10%;">Date</th>
                    <th style="width: 20%;">Client</th>
                    <th style="width: 10%;">Validité</th>
                    <th style="width: 10%;">Statut</th>
                    <th style="width: 12%;" class="text-right">Total TTC</th>
                    <th style="width: 30%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($devis)): ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding: 40px; color: #777;">
                        📭 Aucun devis trouvé
                        <br>
                        <a href="index.php?route=devis_create" class="btn btn-success" style="margin-top: 15px;">➕ Créer un devis</a>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($devis as $d): ?>
                    <tr>
                        <td><strong>#<?php echo str_pad($d['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                        <td><?php echo date('d/m/Y', strtotime($d['devis_date'])); ?></td>
                        <td><strong><?php echo htmlspecialchars($d['client_name']); ?></strong></td>
                        <td><?php echo $d['validity_date'] ? date('d/m/Y', strtotime($d['validity_date'])) : '<span style="color:#999;">—</span>'; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $d['status']; ?>">
                                <?php 
                                $statusLabels = [
                                    'draft' => '📝 Brouillon',
                                    'sent' => '📤 Envoyé',
                                    'accepted' => '✅ Accepté',
                                    'rejected' => '❌ Refusé',
                                    'expired' => '⏰ Expiré'
                                ];
                                echo $statusLabels[$d['status']] ?? $d['status'];
                                ?>
                            </span>
                        </td>
                        <td class="text-right"><strong><?php echo number_format($d['total_ttc'], 3, ',', ' '); ?></strong></td>
                        <td>
                            <div class="table-actions">
                                <a href="index.php?route=devis_edit&id=<?php echo $d['id']; ?>" 
                                   class="btn btn-warning" 
                                   title="Modifier">✏️</a>
                                
                                <a href="index.php?route=devis_print&id=<?php echo $d['id']; ?>" 
                                   target="_blank" 
                                   class="btn btn-pdf" 
                                   title="Imprimer PDF">🖨️ PDF</a>
                                
                                <?php if($d['status'] != 'accepted' && $d['status'] != 'rejected'): ?>
                                    <a href="index.php?route=devis_convert&id=<?php echo $d['id']; ?>" 
                                       class="btn btn-success" 
                                       title="Convertir en facture"
                                       onclick="return confirm('Convertir ce devis en facture ?')">🔄</a>
                                <?php endif; ?>
                                
                                <?php if($d['status'] == 'draft'): ?>
                                    <a href="index.php?route=devis_update_status&id=<?php echo $d['id']; ?>&status=sent" 
                                       class="btn btn-info" 
                                       title="Marquer comme envoyé">📤</a>
                                <?php endif; ?>
                                
                                <a href="index.php?route=devis_delete&id=<?php echo $d['id']; ?>" 
                                   class="btn btn-danger" 
                                   title="Supprimer"
                                   onclick="return confirm('⚠️ Supprimer ce devis ?')">🗑️</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 20px; padding: 15px; background: #ecf0f1; border-radius: 5px; font-size: 13px;">
            <strong>💡 Astuce :</strong> Cliquez sur le bouton <strong>🖨️ PDF</strong> pour imprimer ou télécharger un devis. 
            Dans la fenêtre d'impression, sélectionnez "Enregistrer au format PDF" pour créer un fichier PDF.
        </div>
    </div>
</body>
</html>