<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Devis - Garage Auto Service</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f4f4; }
        .container { max-width: 900px; margin: 0 auto; background: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #2c3e50; }
        input, select, textarea { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #3498db; }
        
        .lines-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .lines-table th, .lines-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .lines-table th { background: #2c3e50; color: #fff; }
        
        .btn { padding: 10px 20px; background: #3498db; color: #fff; border: none; cursor: pointer; text-decoration: none; display: inline-block; border-radius: 4px; font-size: 14px; }
        .btn:hover { opacity: 0.9; }
        .btn-danger { background: #e74c3c; }
        .btn-success { background: #2ecc71; }
        .btn-warning { background: #f1c40f; color: #000; }
        
        .totals { margin-top: 20px; text-align: right; background: #f9f9f9; padding: 15px; border-radius: 4px; }
        .totals div { margin-bottom: 8px; }
        .totals strong { color: #2c3e50; }
        
        .search-container { position: relative; }
        .search-results { 
            position: absolute; 
            top: 100%; 
            left: 0; 
            right: 0; 
            background: #fff; 
            border: 1px solid #ddd; 
            border-top: none; 
            max-height: 200px; 
            overflow-y: auto; 
            z-index: 1000; 
            display: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .search-result-item { padding: 12px; cursor: pointer; border-bottom: 1px solid #eee; }
        .search-result-item:hover { background: #ecf0f1; }
        .search-result-item strong { display: block; color: #2c3e50; margin-bottom: 3px; }
        .search-result-item small { display: block; color: #777; font-size: 12px; }
        
        .selected-client { background: #d5f5e3; padding: 10px; border-radius: 4px; margin-top: 10px; display: none; border: 1px solid #2ecc71; }
        .selected-client.show { display: block; }
        .client-info-row { display: flex; justify-content: space-between; align-items: center; }
        .clear-selection { color: #e74c3c; cursor: pointer; text-decoration: underline; font-size: 13px; }
        
        .form-actions { margin-top: 25px; display: flex; gap: 10px; flex-wrap: wrap; }
        .form-actions .btn { padding: 12px 25px; }
        
        .remove-line { padding: 5px 10px; font-size: 12px; }
        
        @media screen and (max-width: 768px) {
            .container { padding: 15px; }
            .form-actions { flex-direction: column; }
            .form-actions .btn { width: 100%; text-align: center; }
            .lines-table { font-size: 12px; }
            .lines-table th, .lines-table td { padding: 5px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><?php echo $devis ? '✏️ Modifier Devis' : '➕ Nouveau Devis'; ?></h2>
        
        <form method="POST" id="devisForm">
            <!-- Client Search -->
            <div class="form-group">
                <label>🔍 Rechercher un Client *</label>
                <div class="search-container">
                    <input type="text" id="clientSearch" placeholder="Tapez le nom, modèle ou téléphone..." 
                           value="<?php echo $selectedClient ? htmlspecialchars($selectedClient['name']) : ''; ?>" 
                           <?php echo $devis ? 'readonly' : ''; ?> required>
                    <input type="hidden" name="client_id" id="clientId" value="<?php echo $devis ? $devis['client_id'] : ($selectedClient ? $selectedClient['id'] : ''); ?>">
                    <div class="search-results" id="searchResults"></div>
                </div>
                <?php if(!$devis): ?>
                <div class="selected-client <?php echo $selectedClient ? 'show' : ''; ?>" id="selectedClientBox">
                    <div class="client-info-row">
                        <div>
                            <strong id="selectedClientName"><?php echo $selectedClient ? htmlspecialchars($selectedClient['name']) : ''; ?></strong>
                            <span id="selectedClientModel"><?php echo $selectedClient && !empty($selectedClient['car_model']) ? ' - ' . htmlspecialchars($selectedClient['car_model']) : ''; ?></span>
                        </div>
                        <span class="clear-selection" onclick="clearClientSelection()">Changer</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>📅 Date du Devis *</label>
                <input type="date" name="devis_date" value="<?php echo $devis ? $devis['devis_date'] : date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label>📅 Date de Validité</label>
                <input type="date" name="validity_date" value="<?php echo $devis ? $devis['validity_date'] : date('Y-m-d', strtotime('+30 days')); ?>">
            </div>
            
            <div class="form-group">
                <label>🚗 Kilométrage</label>
                <input type="number" name="mileage" value="<?php echo $devis ? $devis['mileage'] : 0; ?>">
            </div>
            
            <div class="form-group">
                <label>📊 Statut</label>
                <select name="status">
                    <option value="draft" <?php echo ($devis && $devis['status'] == 'draft') ? 'selected' : ''; ?>>📝 Brouillon</option>
                    <option value="sent" <?php echo ($devis && $devis['status'] == 'sent') ? 'selected' : ''; ?>>📤 Envoyé</option>
                    <option value="accepted" <?php echo ($devis && $devis['status'] == 'accepted') ? 'selected' : ''; ?>>✅ Accepté</option>
                    <option value="rejected" <?php echo ($devis && $devis['status'] == 'rejected') ? 'selected' : ''; ?>>❌ Refusé</option>
                    <option value="expired" <?php echo ($devis && $devis['status'] == 'expired') ? 'selected' : ''; ?>>⏰ Expiré</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>💬 Commentaire</label>
                <textarea name="comment" rows="3" placeholder="Notes additionnelles..."><?php echo $devis ? htmlspecialchars($devis['comment']) : ''; ?></textarea>
            </div>
            
            <!-- Lines Table -->
            <h3 style="margin-top: 30px; color: #2c3e50;">📋 Lignes du Devis</h3>
            <table class="lines-table" id="linesTable">
                <thead>
                    <tr>
                        <th style="width: 40%;">Type de Réparation</th>
                        <th style="width: 15%;">Quantité</th>
                        <th style="width: 20%;">Prix Unit. (TND)</th>
                        <th style="width: 15%;">Total</th>
                        <th style="width: 10%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $linesData = $lines ?: [['repair_type_id'=>'', 'quantity'=>1, 'price_unit'=>0, 'total_line'=>0]];
                    foreach ($linesData as $index => $line): 
                    ?>
                    <tr class="line-row">
                        <td>
                            <select name="repair_type_id[]" required>
                                <option value="">Choisir...</option>
                                <?php foreach ($types as $t): ?>
                                    <option value="<?php echo $t['id']; ?>" data-price="<?php echo $t['default_price']; ?>" <?php if($line['repair_type_id'] == $t['id']) echo 'selected'; ?>><?php echo htmlspecialchars($t['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="number" step="1" min="1" name="quantity[]" value="<?php echo $line['quantity']; ?>" class="qty" required></td>
                        <td><input type="number" step="0.001" min="0" name="price_unit[]" value="<?php echo $line['price_unit']; ?>" class="price" required></td>
                        <td><span class="line-total"><?php echo number_format($line['total_line'], 3); ?></span></td>
                        <td><button type="button" class="btn btn-danger remove-line">✕</button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="button" class="btn btn-success" id="addLine" style="margin-top:15px;">+ Ajouter une ligne</button>

            <!-- Totals -->
            <div class="totals">
                <div>
                    <label>Taux TVA (%):</label>
                    <input type="number" step="0.01" name="tax_rate" value="<?php echo $devis ? $devis['tax_rate'] : 19; ?>" style="width:100px;" class="calc-input">
                </div>
                <div>
                    <label>Droit de timbre:</label>
                    <input type="number" step="0.001" name="droit_timbre" value="<?php echo $devis ? $devis['droit_timbre'] : 0; ?>" style="width:100px;" class="calc-input">
                </div>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 2px solid #ddd;">
                    <strong>Total HT: <span id="total_ht"><?php echo $devis ? number_format($devis['total_ht'], 3) : '0.000'; ?></span> TND</strong>
                </div>
                <div>
                    <strong>Total TVA: <span id="total_tva"><?php echo $devis ? number_format($devis['total_tva'], 3) : '0.000'; ?></span> TND</strong>
                </div>
                <div style="font-size: 18px; color: #27ae60;">
                    <strong>Total TTC: <span id="total_ttc"><?php echo $devis ? number_format($devis['total_ttc'], 3) : '0.000'; ?></span> TND</strong>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">💾 Enregistrer le Devis</button>
                <a href="index.php?route=devis" class="btn btn-danger">✕ Annuler</a>
            </div>
        </form>
    </div>
    
    <script src="assets/script.js"></script>
</body>
</html>