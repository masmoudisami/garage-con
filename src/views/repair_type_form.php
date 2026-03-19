<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Types de Réparation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f4f4; }
        .container { max-width: 900px; margin: 0 auto; background: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1, h2 { color: #2c3e50; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px; }
        .btn { padding: 10px 15px; background: #3498db; color: #fff; border: none; cursor: pointer; text-decoration: none; display: inline-block; border-radius: 4px; }
        .btn-danger { background: #e74c3c; }
        .btn-warning { background: #f1c40f; color: #000; }
        .btn-success { background: #2ecc71; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #2c3e50; color: #fff; }
        tr:hover { background: #f9f9f9; }
        .text-right { text-align: right; }
        .actions { margin-bottom: 20px; }
        .form-section { display: flex; gap: 30px; flex-wrap: wrap; }
        .form-panel { flex: 1; min-width: 300px; }
        .list-panel { flex: 1; min-width: 300px; }
        
        /* Message d'erreur */
        .error-box {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .error-box h3 {
            margin: 0 0 10px 0;
            color: #721c24;
        }
        .error-box ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .error-box li {
            margin: 5px 0;
        }
        .warning-icon {
            font-size: 24px;
            margin-right: 10px;
        }
        .info-box {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
            font-size: 13px;
        }
        .delete-btn {
            padding: 5px 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestion des Types de Réparation</h1>
        <div class="actions">
            <a href="index.php?route=invoices" class="btn">Retour Tableau de Bord</a>
        </div>
        
        <?php if(isset($error) && $error['type'] === 'foreign_key'): ?>
        <div class="error-box">
            <h3>⚠️ <?php echo $error['message']; ?></h3>
            <p>
                <strong><?php echo htmlspecialchars($error['typeName']); ?></strong> ne peut pas être supprimé car il est utilisé dans 
                <strong><?php echo $error['usageCount']; ?> facture(s)</strong>.
            </p>
            
            <?php if(!empty($error['usedInInvoices'])): ?>
            <p><strong>Utilisé dans les factures suivantes :</strong></p>
            <ul>
                <?php foreach($error['usedInInvoices'] as $inv): ?>
                    <li>
                        Facture #<?php echo str_pad($inv['id'], 6, '0', STR_PAD_LEFT); ?> 
                        - <?php echo date('d/m/Y', strtotime($inv['invoice_date'])); ?> 
                        - Client: <?php echo htmlspecialchars($inv['client_name']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php if($error['usageCount'] > 5): ?>
                <p><em>... et <?php echo $error['usageCount'] - 5; ?> autre(s) facture(s)</em></p>
            <?php endif; ?>
            <?php endif; ?>
            
            <div class="info-box">
                <strong>💡 Solution :</strong> Vous ne pouvez pas supprimer un type de réparation déjà utilisé. 
                Vous pouvez cependant le <strong>modifier</strong> pour changer son nom ou son prix par défaut.
            </div>
        </div>
        <?php endif; ?>
        
        <div class="form-section">
            <div class="form-panel">
                <h2><?php echo $type ? 'Modifier Type' : 'Nouveau Type'; ?></h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Nom du Type</label>
                        <input type="text" name="name" value="<?php echo $type ? htmlspecialchars($type['name']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Prix Défaut (TND)</label>
                        <input type="number" step="0.001" name="default_price" value="<?php echo $type ? $type['default_price'] : 0; ?>">
                    </div>
                    <button type="submit" class="btn btn-success"><?php echo $type ? 'Modifier' : 'Ajouter'; ?></button>
                    <?php if($type): ?>
                        <a href="index.php?route=types_create" class="btn btn-danger">Annuler</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="list-panel">
                <h2>Liste des Types</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th class="text-right">Prix Défaut</th>
                            <th>Utilisations</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($types)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center;">Aucun type enregistré</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($types as $t): 
                                $usageCount = $this->model->getUsageCount($t['id']);
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($t['name']); ?></td>
                                <td class="text-right"><?php echo number_format($t['default_price'], 3, ',', ' '); ?></td>
                                <td style="text-align: center;">
                                    <?php if($usageCount > 0): ?>
                                        <span style="background: #ffc107; padding: 3px 8px; border-radius: 3px; font-size: 11px;">
                                            🔗 <?php echo $usageCount; ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #2ecc71;">✓ Libre</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="index.php?route=types_edit&id=<?php echo $t['id']; ?>" class="btn btn-warning" style="padding:5px 10px;">Mod</a>
                                    <a href="index.php?route=types_delete&id=<?php echo $t['id']; ?>" 
                                       class="btn btn-danger delete-btn" 
                                       <?php if($usageCount > 0): ?>
                                           onclick="return confirm('⚠️ Ce type est utilisé dans <?php echo $usageCount; ?> facture(s). Voulez-vous vraiment essayer de le supprimer ?')"
                                       <?php else: ?>
                                           onclick="return confirm('Supprimer ce type de réparation ?')"
                                       <?php endif; ?>
                                    >Sup</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>