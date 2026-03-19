<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique - <?php echo htmlspecialchars($client['name']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f4f4; }
        .container { max-width: 1200px; margin: 0 auto; background: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1, h2 { color: #2c3e50; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px; }
        .btn { display: inline-block; padding: 10px 15px; background: #3498db; color: #fff; text-decoration: none; border-radius: 4px; }
        .btn-danger { background: #e74c3c; }
        .btn-success { background: #2ecc71; }
        .btn-warning { background: #f1c40f; color: #000; }
        .btn-csv { background: #27ae60; }
        .btn-pdf { background: #e74c3c; }
        .client-info { background: #ecf0f1; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px; }
        .stat-card { background: #3498db; color: #fff; padding: 15px; border-radius: 5px; text-align: center; }
        .stat-card h3 { margin: 0; font-size: 24px; }
        .stat-card p { margin: 5px 0 0; opacity: 0.9; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #2c3e50; color: #fff; }
        tr:hover { background: #f9f9f9; }
        .text-right { text-align: right; }
        .actions { margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
        .repair-details { background: #f9f9f9; padding: 10px; margin-top: 10px; border-radius: 4px; display: none; }
        .toggle-btn { cursor: pointer; color: #3498db; text-decoration: underline; }
        .no-data { text-align: center; padding: 40px; color: #777; }
        .model-badge { background: #9b59b6; color: #fff; padding: 5px 10px; border-radius: 3px; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📋 Historique des Réparations</h1>
        <div class="actions">
            <a href="index.php?route=client_history" class="btn">Changer de Client</a>
            <a href="index.php?route=invoices" class="btn">Retour Tableau de Bord</a>
            <a href="index.php?route=invoices_create&client_id=<?php echo $client['id']; ?>" class="btn btn-success">Nouvelle Facture</a>
            <a href="exports/export_client_history_csv.php?client_id=<?php echo $client['id']; ?>" class="btn btn-csv">📊 Export CSV</a>
            <a href="exports/export_client_history_pdf.php?client_id=<?php echo $client['id']; ?>" target="_blank" class="btn btn-pdf">📄 Export PDF (Sans Prix)</a>
        </div>
        
        <div class="client-info">
            <h2>
                <?php echo htmlspecialchars($client['name']); ?>
                <?php if(!empty($client['car_model'])): ?>
                    <span class="model-badge">🚗 <?php echo htmlspecialchars($client['car_model']); ?></span>
                <?php endif; ?>
            </h2>
            <p>
                <?php if(!empty($client['phone'])): ?><strong>Téléphone:</strong> <?php echo htmlspecialchars($client['phone']); ?> |<?php endif; ?>
                <?php if(!empty($client['address'])): ?><strong>Adresse:</strong> <?php echo htmlspecialchars($client['address']); ?><?php endif; ?>
            </p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $stats['total_invoices'] ?? 0; ?></h3>
                <p>Factures</p>
            </div>
            <div class="stat-card">
                <h3><?php echo number_format($stats['total_spent'] ?? 0, 3, ',', ' '); ?> TND</h3>
                <p>Total Dépensé</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['first_visit'] ? date('d/m/Y', strtotime($stats['first_visit'])) : '-'; ?></h3>
                <p>Première Visite</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['last_visit'] ? date('d/m/Y', strtotime($stats['last_visit'])) : '-'; ?></h3>
                <p>Dernière Visite</p>
            </div>
        </div>
        
        <h2>📄 Liste des Factures</h2>
        
        <?php if(empty($invoices)): ?>
            <div class="no-data">
                <p>Aucune facture trouvée pour ce client.</p>
                <a href="index.php?route=invoices_create&client_id=<?php echo $client['id']; ?>" class="btn btn-success">Créer une facture</a>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>N° Facture</th>
                        <th>Date</th>
                        <th>KM</th>
                        <th>Réparations</th>
                        <th class="text-right">Total TTC</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $inv): 
                        $lines = $this->model->getLines($inv['id']);
                    ?>
                    <tr>
                        <td>#<?php echo str_pad($inv['id'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($inv['invoice_date'])); ?></td>
                        <td><?php echo number_format($inv['mileage'], 0, ',', ' '); ?></td>
                        <td>
                            <span class="toggle-btn" onclick="toggleDetails(<?php echo $inv['id']; ?>)">
                                <?php echo count($lines); ?> réparation(s) ▼
                            </span>
                            <div id="details-<?php echo $inv['id']; ?>" class="repair-details">
                                <ul style="margin: 10px 0; padding-left: 20px;">
                                    <?php foreach ($lines as $line): ?>
                                        <li>
                                            <strong><?php echo htmlspecialchars($line['type_name']); ?></strong>
                                            x<?php echo $line['quantity']; ?> = 
                                            <?php echo number_format($line['total_line'], 3, ',', ' '); ?> TND
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php if(!empty($inv['comment'])): ?>
                                    <p><strong>Commentaire:</strong> <?php echo htmlspecialchars($inv['comment']); ?></p>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="text-right"><strong><?php echo number_format($inv['total_ttc'], 3, ',', ' '); ?></strong></td>
                        <td>
                            <a href="index.php?route=invoices_print&id=<?php echo $inv['id']; ?>" target="_blank" class="btn" style="padding:5px 10px;">PDF</a>
                            <a href="index.php?route=invoices_edit&id=<?php echo $inv['id']; ?>" class="btn btn-warning" style="padding:5px 10px;">Mod</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <script>
        function toggleDetails(invoiceId) {
            var details = document.getElementById('details-' + invoiceId);
            if (details.style.display === 'block') {
                details.style.display = 'none';
            } else {
                details.style.display = 'block';
            }
        }
    </script>
</body>
</html>