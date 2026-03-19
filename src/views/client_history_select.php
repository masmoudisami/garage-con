<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des Réparations</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f4f4; }
        .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px; }
        .btn { display: inline-block; padding: 10px 15px; background: #3498db; color: #fff; text-decoration: none; border-radius: 4px; }
        .btn-danger { background: #e74c3c; }
        .btn-success { background: #2ecc71; }
        .btn-csv { background: #27ae60; }
        .btn-info { background: #17a2b8; }
        .form-group { margin-bottom: 20px; }
        .search-section { background: #ecf0f1; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; box-sizing: border-box; }
        .search-row { display: flex; gap: 15px; flex-wrap: wrap; }
        .search-row .form-group { flex: 1; min-width: 200px; margin-bottom: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #2c3e50; color: #fff; }
        tr:hover { background: #f9f9f9; }
        .actions { margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
        .result-count { color: #666; font-size: 0.9em; margin-top: 10px; }
        .no-results { text-align: center; padding: 30px; color: #777; background: #f9f9f9; border-radius: 5px; }
        .all-clients-link { text-align: center; margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📋 Historique des Réparations par Client</h1>
        <div class="actions">
            <a href="index.php?route=invoices" class="btn">Retour Tableau de Bord</a>
            <a href="exports/export_all_clients_csv.php" class="btn btn-csv">📊 Export Tous Clients</a>
        </div>
        
        <div class="search-section">
            <h3>🔍 Rechercher un Client</h3>
            <form method="GET">
                <input type="hidden" name="route" value="client_history">
                <div class="search-row">
                    <div class="form-group">
                        <label>Recherche par Nom ou Modèle de Voiture</label>
                        <input type="text" name="search_model" placeholder="Ex: Clio, Peugeot, BMW, Mohamed..." value="<?php echo htmlspecialchars($searchModel); ?>" autofocus>
                    </div>
                    <div class="form-group" style="flex: 0 0 auto; display: flex; align-items: flex-end;">
                        <button type="submit" class="btn" style="width: auto;">Rechercher</button>
                    </div>
                    <div class="form-group" style="flex: 0 0 auto; display: flex; align-items: flex-end;">
                        <a href="index.php?route=client_history" class="btn btn-danger" style="width: auto;">Réinitialiser</a>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="result-count">
            <strong><?php echo count($clients); ?></strong> client(s) trouvé(s)
            <?php if(!empty($searchModel)): ?>
                pour la recherche : "<strong><?php echo htmlspecialchars($searchModel); ?></strong>"
            <?php else: ?>
                (tous les clients)
            <?php endif; ?>
        </div>
        
        <?php if(empty($clients)): ?>
            <div class="no-results">
                <p>🔍 Aucun client trouvé pour cette recherche.</p>
                <p>Essayez avec un autre modèle ou nom de client.</p>
                <a href="index.php?route=client_history" class="btn btn-info">Voir tous les clients</a>
            </div>
        <?php else: ?>
            <h2 style="margin-top: 30px;">📊 Liste des Clients</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Modèle Voiture</th>
                        <th>Téléphone</th>
                        <th>Adresse</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $c): 
                        $stats = (new Invoice())->getClientStats($c['id']);
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($c['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($c['car_model'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($c['phone'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($c['address'] ?? '-'); ?></td>
                        <td>
                            <a href="index.php?route=client_history&client_id=<?php echo $c['id']; ?>" class="btn btn-info" style="padding:5px 10px;">📋 Historique</a>
                            <a href="index.php?route=invoices_create&client_id=<?php echo $c['id']; ?>" class="btn btn-success" style="padding:5px 10px;">📄 Facture</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
                        
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding: 20px; background: #ecf0f1; border-radius: 5px;">
            <h3>📈 Statistiques Globales</h3>
            <table>
                <tr>
                    <td><strong>Total Clients:</strong></td>
                    <td><?php echo count($this->clientModel->getAll()); ?></td>
                </tr>
                <tr>
                    <td><strong>Clients affichés:</strong></td>
                    <td><?php echo count($clients); ?></td>
                </tr>
                <tr>
                    <td><strong>Total Factures:</strong></td>
                    <td>
                        <?php
                        $totalInvoices = 0;
                        foreach($clients as $c) {
                            $stats = (new Invoice())->getClientStats($c['id']);
                            $totalInvoices += $stats['total_invoices'];
                        }
                        echo $totalInvoices;
                        ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>