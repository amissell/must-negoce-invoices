<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Facture {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 20mm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.4;
        }
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .header-left, .header-right {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }
        .company-name {
            font-size: 24pt;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        .company-info {
            font-size: 9pt;
            color: #666;
            line-height: 1.6;
        }
        .invoice-title {
            font-size: 20pt;
            font-weight: bold;
            text-align: right;
            color: #1f2937;
            margin-bottom: 10px;
        }
        .invoice-details {
            text-align: right;
            font-size: 10pt;
        }
        .invoice-details div {
            margin-bottom: 5px;
        }
        .client-section {
            background: #f3f4f6;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #2563eb;
        }
        .client-section h3 {
            margin: 0 0 10px 0;
            font-size: 12pt;
            color: #1f2937;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        thead {
            background: #2563eb;
            color: white;
        }
        thead th {
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 10pt;
        }
        tbody td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10pt;
        }
        tbody tr:hover {
            background: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-section {
            margin-top: 30px;
            float: right;
            width: 40%;
        }
        .totals-section table {
            margin: 0;
        }
        .totals-section td {
            padding: 8px;
            border: none;
        }
        .total-row {
            background: #2563eb;
            color: white;
            font-weight: bold;
            font-size: 12pt;
        }
        .amount-words {
            clear: both;
            margin-top: 40px;
            padding: 15px;
            background: #f9fafb;
            border-left: 4px solid #10b981;
        }
        .amount-words strong {
            color: #059669;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            font-size: 9pt;
            color: #666;
        }
        .legal-ids {
            display: table;
            width: 100%;
            margin-top: 10px;
        }
        .legal-ids > div {
            display: table-cell;
            width: 25%;
            padding: 5px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <div class="company-name">MUST NEGOCE</div>
            <div class="company-info">
                Société spécialisée en produits de nettoyage<br>
                123 Boulevard Mohammed V<br>
                Casablanca, Maroc<br>
                Tél: +212 522 123 456<br>
                Email: contact@mustnegoce.ma
            </div>
        </div>
        <div class="header-right">
            <div class="invoice-title">FACTURE</div>
            <div class="invoice-details">
                <div><strong>N°:</strong> {{ $invoice->invoice_number }}</div>
                <div><strong>Date:</strong> {{ $invoice->invoice_date->format('d/m/Y') }}</div>
                <div><strong>Date d'échéance:</strong> {{ $invoice->invoice_date->addDays(30)->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Client Section -->
    <div class="client-section">
        <h3>FACTURÉ À:</h3>
        <div>
            <strong>Client Entreprise</strong><br>
            Adresse du client<br>
            Ville, Code Postal<br>
            Maroc
        </div>
    </div>

    <!-- Products Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">#</th>
                <th style="width: 40%;">Produit</th>
                <th style="width: 15%;" class="text-center">Quantité</th>
                <th style="width: 15%;" class="text-right">Prix Unitaire</th>
                <th style="width: 20%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td class="text-center">{{ $item->quantity }} {{ $item->product->unite }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }} MAD</td>
                <td class="text-right">{{ number_format($item->total, 2) }} MAD</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals-section">
        <table>
            <tr>
                <td><strong>Sous-total HT:</strong></td>
                <td class="text-right">{{ number_format($invoice->montant_ht, 2) }} MAD</td>
            </tr>
            <tr>
                <td><strong>TVA (20%):</strong></td>
                <td class="text-right">{{ number_format($invoice->tva, 2) }} MAD</td>
            </tr>
            <tr class="total-row">
                <td><strong>TOTAL TTC:</strong></td>
                <td class="text-right">{{ number_format($invoice->total_ttc, 2) }} MAD</td>
            </tr>
        </table>
    </div>

    <!-- Amount in Words -->
    <div class="amount-words">
        <strong>Montant en toutes lettres:</strong><br>
        {{ App\Helpers\NumberToWords::convert($invoice->total_ttc) }}
    </div>

    <!-- Footer -->
    <div class="footer">
        <div style="margin-bottom: 15px;">
            <strong>Conditions de paiement:</strong> Paiement à 30 jours<br>
            <strong>Mode de paiement:</strong> Virement bancaire, Chèque, Espèces
        </div>
        
        <div class="legal-ids">
            <div><strong>ICE:</strong> 002547896000045</div>
            <div><strong>RC:</strong> 125478</div>
            <div><strong>IF:</strong> 45896214</div>
            <div><strong>CNSS:</strong> 8547896</div>
        </div>
        
        <div style="text-align: center; margin-top: 20px; font-size: 8pt; color: #999;">
            Cette facture est générée automatiquement et ne nécessite pas de signature
        </div>
    </div>
</body>
</html>