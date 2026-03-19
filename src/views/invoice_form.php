<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f4f4; }
        .container { max-width: 900px; margin: 0 auto; background: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px; }
        .lines-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .lines-table th, .lines-table td { border: 1px solid #ddd; padding: 8px; }
        .btn { padding: 10px 15px; background: #3498db; color: #fff; border: none; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-danger { background: #e74c3c; }
        .btn-success { background: #2ecc71; }
        .totals { margin-top: 20px; text-align: right; }
        .totals div { margin-bottom: 5px; }
        .search-container { position: relative; }
        .search-results { position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid #ddd; border-top: none; max-height: 200px; overflow-y: auto; z-index: 1000; display: none; }
        .search-result-item { padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; }
        .search-result-item:hover { background: #ecf0f1; }
        .search-result-item strong { color: #2c3e50; }
        .search-result-item small { color: #777; display: block; margin-top: 3px; }
        .selected-client { background: #d5f5e3; padding: 10px; border-radius: 4px; margin-top: 10px; display: none; }
        .selected-client.show { display: block; }
        .client-info-row { display: flex; justify-content: space-between; align-items: center; }
        .clear-selection { color: #e74c3c; cursor: pointer; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2><?php echo $invoice ? 'Modifier Facture' : 'Nouvelle Facture'; ?></h2>
        <form method="POST">
            <div class="form-group">
                <label>Rechercher un Client</label>
                <div class="search-container">
                    <input type="text" id="clientSearch" placeholder="Tapez le nom ou le modèle..." value="<?php echo $selectedClient ? htmlspecialchars($selectedClient['name']) : ''; ?>" <?php echo $invoice ? 'readonly' : ''; ?>>
                    <input type="hidden" name="client_id" id="clientId" value="<?php echo $invoice ? $invoice['client_id'] : ($selectedClient ? $selectedClient['id'] : ''); ?>">
                    <div class="search-results" id="searchResults"></div>
                </div>
                <div class="selected-client <?php echo ($selectedClient || $invoice) ? 'show' : ''; ?>" id="selectedClientBox">
                    <div class="client-info-row">
                        <div>
                            <strong id="selectedClientName"><?php echo $invoice ? htmlspecialchars($clientForInvoice['name']) : ($selectedClient ? htmlspecialchars($selectedClient['name']) : ''); ?></strong>
                            <span id="selectedClientModel"><?php echo $invoice && !empty($clientForInvoice['car_model']) ? ' - ' . htmlspecialchars($clientForInvoice['car_model']) : ($selectedClient && !empty($selectedClient['car_model']) ? ' - ' . htmlspecialchars($selectedClient['car_model']) : ''); ?></span>
                        </div>
                        <?php if(!$invoice): ?>
                        <span class="clear-selection" onclick="clearClientSelection()">Changer</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="invoice_date" value="<?php echo $invoice ? $invoice['invoice_date'] : date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label>Kilométrage</label>
                <input type="number" name="mileage" value="<?php echo $invoice ? $invoice['mileage'] : 0; ?>">
            </div>
            <div class="form-group">
                <label>Commentaire</label>
                <textarea name="comment" rows="3"><?php echo $invoice ? htmlspecialchars($invoice['comment']) : ''; ?></textarea>
            </div>
            
            <table class="lines-table" id="linesTable">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Qté</th>
                        <th>Prix Unit.</th>
                        <th>Total</th>
                        <th>Action</th>
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
                        <td><input type="number" step="1" name="quantity[]" value="<?php echo $line['quantity']; ?>" class="qty" required></td>
                        <td><input type="number" step="0.001" name="price_unit[]" value="<?php echo $line['price_unit']; ?>" class="price" required></td>
                        <td><span class="line-total"><?php echo number_format($line['total_line'], 3); ?></span></td>
                        <td><button type="button" class="btn btn-danger remove-line">X</button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="button" class="btn btn-success" id="addLine" style="margin-top:10px;">+ Ligne</button>

            <div class="totals">
                <div>Taux TVA (%): <input type="number" step="0.01" name="tax_rate" value="<?php echo $invoice ? $invoice['tax_rate'] : 19; ?>" style="width:100px;" class="calc-input"></div>
                <div>Droit de timbre: <input type="number" step="0.001" name="droit_timbre" value="<?php echo $invoice ? $invoice['droit_timbre'] : 0; ?>" style="width:100px;" class="calc-input"></div>
                <div><strong>Total HT: <span id="total_ht">0.000</span></strong></div>
                <div><strong>Total TVA: <span id="total_tva">0.000</span></strong></div>
                <div><strong>Total TTC: <span id="total_ttc">0.000</span></strong></div>
            </div>

            <div style="margin-top:20px;">
                <button type="submit" class="btn btn-success">Enregistrer</button>
                <a href="index.php?route=invoices" class="btn btn-danger">Annuler</a>
            </div>
        </form>
    </div>
    <script src="assets/script.js"></script>
</body>
</html>