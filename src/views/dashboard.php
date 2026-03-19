<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord - <?php echo APP_NAME; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f4f4; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; background: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        
        /* Barre utilisateur */
        .user-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 5px;
            color: #fff;
        }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .user-info strong { font-size: 16px; }
        .user-role { padding: 3px 10px; background: rgba(255,255,255,0.2); border-radius: 3px; font-size: 11px; text-transform: uppercase; }
        .btn-logout { padding: 8px 15px; background: #e74c3c; color: #fff; text-decoration: none; border-radius: 4px; font-size: 13px; transition: background 0.3s; }
        .btn-logout:hover { background: #c0392b; }
        
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
        
        .filters { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; align-items: flex-end; }
        .filter-group { display: flex; flex-direction: column; }
        .filter-group label { font-size: 0.8em; margin-bottom: 3px; color: #666; }
        .filters input, .filters select { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #2c3e50; color: #fff; }
        tr:hover { background: #f9f9f9; }
        .text-right { text-align: right; }
        
        .config-info { background: #fff3cd; padding: 10px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #ffc107; }
        .export-info { background: #d5f5e3; padding: 10px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #2ecc71; display: <?php echo !empty($filters['search']) || !empty($filters['date_start']) || !empty($filters['date_end']) || !empty($filters['type']) ? 'block' : 'none'; ?>; }
        
        .table-actions { display: flex; gap: 5px; flex-wrap: wrap; }
        .table-actions .btn { padding: 6px 12px; font-size: 13px; }
        
        @media screen and (max-width: 768px) {
            .banner-container { padding: 15px 0; margin-bottom: 20px; }
            .banner-container img { max-height: 100px; }
            .user-bar { flex-direction: column; gap: 10px; text-align: center; }
            .user-info { flex-direction: column; }
            .actions { flex-direction: column; }
            .actions .btn { width: 100%; text-align: center; }
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
        <!-- Barre Utilisateur -->
        <div class="user-bar">
            <div class="user-info">
                <span>👤 <strong><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></strong></span>
                <span class="user-role"><?php echo strtoupper($_SESSION['role'] ?? 'USER'); ?></span>
            </div>
            <a href="index.php?route=logout" class="btn-logout">🚪 Déconnexion</a>
        </div>
        
        <!-- Bannière -->
        <div class="banner-container">
            <img src="assets/banniere.png" alt="Bannière <?php echo APP_NAME; ?>">
        </div>
        
        <h1>📊 Tableau de Bord</h1>
        
        <div class="config-info">
            <strong>📍 <?php echo GARAGE_NAME; ?></strong> - <?php echo GARAGE_ADDRESS; ?> - Tél: <?php echo GARAGE_PHONE; ?>
            <span style="float:right;"><a href="#config-help" class="btn btn-info" style="padding:5px 10px; font-size:0.9em;">Modifier infos</a></span>
        </div>
        
        <div class="actions">
            <a href="index.php?route=invoices_create" class="btn btn-success">➕ Ajouter Facture</a>
            <a href="index.php?route=devis" class="btn btn-purple">📋 Devis</a>
            <a href="index.php?route=clients_create" class="btn">👥 Ajouter Client</a>
            <a href="index.php?route=types_create" class="btn">🔧 Ajouter Type Réparation</a>
            <a href="index.php?route=client_history" class="btn btn-info">📋 Historique Client</a>
            <a href="exports/export_csv.php?search=<?php echo urlencode($filters['search']); ?>&date_start=<?php echo urlencode($filters['date_start']); ?>&date_end=<?php echo urlencode($filters['date_end']); ?>&type=<?php echo urlencode($filters['type']); ?>" class="btn btn-warning">📊 Export CSV</a>
        </div>
        
        <?php if(!empty($filters['search']) || !empty($filters['date_start']) || !empty($filters['date_end']) || !empty($filters['type'])): ?>
        <div class="export-info">
            <strong>✅ Filtres actifs :</strong>
            <?php if(!empty($filters['search'])): ?> Recherche: "<?php echo htmlspecialchars($filters['search']); ?>" |<?php endif; ?>
            <?php if(!empty($filters['date_start'])): ?> Du: <?php echo htmlspecialchars($filters['date_start']); ?> |<?php endif; ?>
            <?php if(!empty($filters['date_end'])): ?> Au: <?php echo htmlspecialchars($filters['date_end']); ?> |<?php endif; ?>
            <?php if(!empty($filters['type'])): ?> Type: <?php 
                $repairModel = new RepairType();
                $type = $repairModel->getById($filters['type']);
                echo $type ? htmlspecialchars($type['name']) : '';
            ?><?php endif; ?>
            <span style="float:right;"><a href="index.php?route=invoices" class="btn btn-danger" style="padding:3px 8px; font-size:0.8em;">✕ Clear</a></span>
        </div>
        <?php endif; ?>
        
        <form method="GET" class="filters">
            <input type="hidden" name="route" value="invoices">
            <div class="filter-group">
                <label>Recherche (Client/Modèle)</label>
                <input type="text" name="search" placeholder="Nom ou modèle..." value="<?php echo htmlspecialchars($filters['search']); ?>">
            </div>
            <div class="filter-group">
                <label>Date Début</label>
                <input type="date" name="date_start" value="<?php echo htmlspecialchars($filters['date_start']); ?>">
            </div>
            <div class="filter-group">
                <label>Date Fin</label>
                <input type="date" name="date_end" value="<?php echo htmlspecialchars($filters['date_end']); ?>">
            </div>
            <div class="filter-group">
                <label>Type Réparation</label>
                <select name="type">
                    <option value="">Tous les types</option>
                    <?php foreach ($types as $t): ?>
                        <option value="<?php echo $t['id']; ?>" <?php if($filters['type'] == $t['id']) echo 'selected'; ?>><?php echo htmlspecialchars($t['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn">🔍 Filtrer</button>
        </form>
        
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Modèle</th>
                    <th>KM</th>
                    <th class="text-right">Total TTC (TND)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($invoices)): ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding: 30px; color: #777;">
                        📭 Aucune facture trouvée
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($inv['invoice_date']); ?></td>
                        <td><strong><?php echo htmlspecialchars($inv['client_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($inv['car_model'] ?? '—'); ?></td>
                        <td><?php echo number_format($inv['mileage'], 0, ',', ' '); ?></td>
                        <td class="text-right"><strong><?php echo number_format($inv['total_ttc'], 3, ',', ' '); ?></strong></td>
                        <td>
                            <div class="table-actions">
                                <a href="index.php?route=invoices_edit&id=<?php echo $inv['id']; ?>" class="btn btn-warning" title="Modifier">✏️</a>
                                <a href="index.php?route=invoices_print&id=<?php echo $inv['id']; ?>" target="_blank" class="btn btn-info" title="PDF">📄</a>
                                <a href="index.php?route=invoices_delete&id=<?php echo $inv['id']; ?>" class="btn btn-danger" onclick="return confirm('⚠️ Supprimer cette facture ?')" title="Supprimer">🗑️</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div id="config-help" style="margin-top: 30px; padding: 20px; background: #ecf0f1; border-radius: 5px;">
            <h3>📝 Modifier les Informations du Garagiste</h3>
            <p>Pour modifier l'en-tête des factures et devis, éditez le fichier <strong>config.php</strong> et modifiez les constantes suivantes :</p>
            <pre style="background: #fff; padding: 15px; border-radius: 4px; overflow-x: auto;">
define('GARAGE_NAME', 'VOTRE NOM DE GARAGE');
define('GARAGE_ADDRESS', 'Votre adresse complète');
define('GARAGE_PHONE', '+216 XX XXX XXX');
define('GARAGE_EMAIL', 'votre@email.com');
define('GARAGE_LOGO', 'assets/logo.png');
define('GARAGE_MATRICULE', 'Votre matricule fiscal');</pre>
            <p><strong>📌 Pour le logo et la bannière :</strong> Placez vos fichiers <code>logo.png</code> et <code>banniere.png</code> dans le dossier <code>assets/</code></p>
        </div>
    </div>
    <script src="assets/script.js"></script>
</body>
</html>