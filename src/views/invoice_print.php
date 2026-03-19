<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture N° <?php echo str_pad($invoice['id'], 6, '0', STR_PAD_LEFT); ?></title>
    <style>
        @page {
            margin: 15mm 15mm;
            size: A4;
            
            @top-right {
                content: "Page " counter(page) " / " counter(pages);
                font-size: 10px;
                color: #777;
            }
            
            @bottom-left { content: ""; }
            @bottom-center { content: ""; }
            @bottom-right { content: ""; }
        }
        
        @page :first {
            @top-right { content: ""; }
        }
        
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        body { 
            font-family: 'Times New Roman', serif; 
            padding: 0; 
            margin: 0; 
            color: #000;
            font-size: 12px;
            line-height: 1.4;
        }
        
        .invoice-container { 
            max-width: 800px; 
            margin: 0 auto; 
            padding: 20px;
        }
        
        .header { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 30px; 
            border-bottom: 3px solid #2c3e50; 
            padding-bottom: 20px; 
        }
        
        .garage-info { flex: 1; }
        .garage-logo { max-width: 150px; max-height: 80px; margin-bottom: 10px; }
        .garage-name { font-size: 16px; font-weight: bold; color: #2c3e50; margin: 5px 0; }
        .garage-details { font-size: 11px; line-height: 1.5; color: #555; }
        .invoice-info { text-align: right; }
        .invoice-title { font-size: 20px; font-weight: bold; color: #2c3e50; margin-bottom: 10px; }
        .invoice-number { font-size: 12px; margin-bottom: 5px; }
        
        .client-section { 
            margin-bottom: 30px; 
            background: #f9f9f9; 
            padding: 15px; 
            border-radius: 5px; 
        }
        
        .client-title { 
            font-weight: bold; 
            margin-bottom: 10px; 
            color: #2c3e50; 
            font-size: 13px;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        
        th, td { 
            border: 1px solid #2c3e50; 
            padding: 8px; 
            text-align: left; 
            font-size: 11px;
        }
        
        th { 
            background: #2c3e50; 
            color: #fff; 
            font-size: 11px;
        }
        
        .totals { 
            margin-top: 20px; 
            text-align: right; 
        }
        
        .totals p { margin: 5px 0; font-size: 11px; }
        
        .totals .total-ttc { 
            font-size: 14px; 
            font-weight: bold; 
            color: #2c3e50; 
            border-top: 2px solid #2c3e50; 
            padding-top: 10px; 
            margin-top: 10px; 
        }
        
        .amount-in-words {
            margin-top: 15px;
            padding: 10px 15px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-style: italic;
            font-size: 11px;
            color: #555;
            text-align: left;
        }
        
        .amount-in-words .label {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .footer { 
            margin-top: 40px; 
            text-align: center; 
            font-size: 10px; 
            color: #777; 
            border-top: 1px solid #ddd; 
            padding-top: 15px; 
        }
        
        .no-print { display: none; }
        
        @media print { 
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
            .invoice-container { border: none; box-shadow: none; margin: 0; padding: 0; }
            @page { margin: 15mm 15mm; }
        }
        
        @media screen {
            .invoice-container { border: 1px solid #ddd; padding: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            .no-print { display: block; margin-top: 20px; text-align: center; padding: 20px; background: #ecf0f1; }
        }
    </style>
</head>
<body>
    <?php
    function convertNumberToWords($amount) {
        $amount = floatval($amount);
        $dinars = floor($amount);
        $millimes = round(($amount - $dinars) * 1000);
        
        $units = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf',
                  'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 
                  'dix-huit', 'dix-neuf'];
        $tens = ['', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante'];
        
        $convertLessThan100 = function($n) use ($units, $tens) {
            if ($n < 20) return $units[$n];
            if ($n < 70) {
                $t = floor($n / 10) * 10;
                $u = $n % 10;
                return $tens[$t / 10] . ($u > 0 ? '-' . $units[$u] : '');
            }
            if ($n < 80) {
                $u = $n - 70;
                return 'soixante-' . ($u == 1 ? 'et-' : '') . $units[10 + $u];
            }
            if ($n < 90) {
                $u = $n - 80;
                return 'quatre-vingts' . ($u > 0 ? '-' . $units[$u] : '');
            }
            $u = $n - 90;
            return 'quatre-vingt-' . ($u == 1 ? 'et-' : '') . $units[10 + $u];
        };
        
        $convertLessThan1000 = function($n) use ($units, $convertLessThan100) {
            if ($n < 100) return $convertLessThan100($n);
            $h = floor($n / 100);
            $r = $n % 100;
            if ($h == 1) {
                return 'cent' . ($r > 0 ? ' ' . $convertLessThan100($r) : '');
            }
            return $units[$h] . ' cent' . ($r > 0 ? 's ' . $convertLessThan100($r) : 's');
        };
        
        $convertDinars = function($n) use ($convertLessThan1000) {
            if ($n == 0) return 'zéro';
            if ($n < 1000) return $convertLessThan1000($n);
            if ($n < 1000000) {
                $th = floor($n / 1000);
                $r = $n % 1000;
                if ($th == 1) {
                    return 'mille' . ($r > 0 ? ' ' . $convertLessThan1000($r) : '');
                }
                return $convertLessThan1000($th) . ' mille' . ($r > 0 ? ' ' . $convertLessThan1000($r) : '');
            }
            return $n;
        };
        
        $dinarsText = $convertDinars($dinars);
        
        if ($dinars == 0) {
            $result = 'Zéro dinar';
        } elseif ($dinars == 1) {
            $result = 'Un dinar';
        } else {
            $result = ucfirst($dinarsText) . ' dinars';
        }
        
        if ($millimes > 0) {
            $result .= ' et ' . str_pad($millimes, 3, '0', STR_PAD_LEFT) . ' millimes';
        }
        
        return $result;
    }
    ?>
    
    <div class="invoice-container">
        <div class="header">
            <div class="garage-info">
                <?php if(file_exists(GARAGE_LOGO)): ?>
                    <img src="<?php echo GARAGE_LOGO; ?>" alt="Logo" class="garage-logo">
                <?php endif; ?>
                <div class="garage-name"><?php echo GARAGE_NAME; ?></div>
                <div class="garage-details">
                    <?php echo GARAGE_ADDRESS; ?><br>
                    Tél: <?php echo GARAGE_PHONE; ?><br>
                    <?php if(defined('GARAGE_EMAIL')): ?>Email: <?php echo GARAGE_EMAIL; ?><br><?php endif; ?>
                    <?php if(defined('GARAGE_MATRICULE')): ?>Mat. Fiscal: <?php echo GARAGE_MATRICULE; ?><?php endif; ?>
                </div>
            </div>
            <div class="invoice-info">
                <div class="invoice-title">FACTURE</div>
                <div class="invoice-number">N° <?php echo str_pad($invoice['id'], 6, '0', STR_PAD_LEFT); ?></div>
                <div class="invoice-number">Date: <?php echo date('d/m/Y', strtotime($invoice['invoice_date'])); ?></div>
            </div>
        </div>

        <div class="client-section">
            <div class="client-title">INFORMATIONS CLIENT</div>
            <strong><?php echo htmlspecialchars($client['name']); ?></strong><br>
            <?php if(!empty($client['matricule_fiscal'])): ?>
                <strong>Mat. Fiscal:</strong> <?php echo htmlspecialchars($client['matricule_fiscal']); ?><br>
            <?php endif; ?>
            <?php if(!empty($client['car_model'])): ?>Modèle: <?php echo htmlspecialchars($client['car_model']); ?><br><?php endif; ?>
            <?php if(!empty($client['address'])): ?><?php echo htmlspecialchars($client['address']); ?><br><?php endif; ?>
            <?php if(!empty($client['phone'])): ?>Tél: <?php echo htmlspecialchars($client['phone']); ?><?php endif; ?>
            <div style="margin-top: 10px;"><strong>Kilométrage:</strong> <?php echo number_format($invoice['mileage'], 0, ',', ' '); ?> KM</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 50%;">Désignation</th>
                    <th style="width: 10%; text-align: center;">Qté</th>
                    <th style="width: 20%; text-align: right;">Prix Unit.</th>
                    <th style="width: 20%; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lines as $line): ?>
                <tr>
                    <td><?php echo htmlspecialchars($line['type_name']); ?></td>
                    <td style="text-align: center;"><?php echo $line['quantity']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($line['price_unit'], 3, ',', ' '); ?></td>
                    <td style="text-align: right;"><?php echo number_format($line['total_line'], 3, ',', ' '); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals">
            <p>Total HT: <strong><?php echo number_format($invoice['total_ht'], 3, ',', ' '); ?></strong></p>
            <p>TVA (<?php echo $invoice['tax_rate']; ?>%): <strong><?php echo number_format($invoice['total_tva'], 3, ',', ' '); ?></strong></p>
            <p>Droit de timbre: <strong><?php echo number_format($invoice['droit_timbre'], 3, ',', ' '); ?></strong></p>
            <p class="total-ttc">Net à Payer: <?php echo number_format($invoice['total_ttc'], 3, ',', ' '); ?></p>
            
            <div class="amount-in-words">
                <span class="label">Arrêté la présente facture à la somme de :</span>
                <strong><?php echo convertNumberToWords($invoice['total_ttc']); ?></strong>
            </div>
        </div>

        <?php if(!empty($invoice['comment'])): ?>
        <div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 5px;">
            <div class="client-title">COMMENTAIRE</div>
            <?php echo nl2br(htmlspecialchars($invoice['comment'])); ?>
        </div>
        <?php endif; ?>

        <div class="footer">
            <?php echo GARAGE_NAME; ?> - <?php echo GARAGE_ADDRESS; ?> - Tél: <?php echo GARAGE_PHONE; ?><br>
            Merci de votre confiance
        </div>
    </div>

    <div class="no-print">
        <button onclick="window.print()" class="btn" style="padding: 10px 20px; background: #3498db; color: #fff; border: none; cursor: pointer; border-radius: 4px;">🖨️ Imprimer / PDF</button>
        <a href="index.php?route=invoices" class="btn" style="padding: 10px 20px; background: #e74c3c; color: #fff; text-decoration: none; border-radius: 4px; margin-left: 10px;">↩️ Retour</a>
        
        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px; text-align: left; max-width: 600px; margin-left: auto; margin-right: auto;">
            <strong>⚠️ Important - Pour supprimer l'URL en bas du PDF :</strong>
            <ol style="margin: 10px 0; padding-left: 20px;">
                <li>Cliquez sur "🖨️ Imprimer / PDF"</li>
                <li>Dans la fenêtre d'impression, cliquez sur <strong>"Plus de paramètres"</strong></li>
                <li><strong>DÉCOCHEZ</strong> l'option <strong>"En-têtes et pieds de page"</strong></li>
                <li>Cliquez sur "Enregistrer" ou "Imprimer"</li>
            </ol>
        </div>
    </div>
    
    <script>
        if (window.location.search.includes('print=1')) {
            window.onload = function() {
                window.print();
            };
        }
    </script>
</body>
</html>