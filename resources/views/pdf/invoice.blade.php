<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        
        .container {
            padding: 20px 30px;
        }
        
        /* Header */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .company-info {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1a56db;
            margin-bottom: 10px;
        }
        
        .company-details {
            font-size: 9px;
            color: #666;
            line-height: 1.6;
        }
        
        .invoice-info {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: right;
        }
        
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .invoice-number {
            font-size: 14px;
            color: #1a56db;
            font-weight: bold;
        }
        
        .invoice-date {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        
        /* Client Section */
        .client-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        
        .client-name {
            font-size: 14px;
            font-weight: bold;
            color: #1e293b;
        }
        
        /* Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table th {
            background: #1a56db;
            color: white;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 8px;
            text-align: left;
        }
        
        .items-table th:nth-child(n+2) {
            text-align: right;
        }
        
        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10px;
        }
        
        .items-table td:nth-child(n+2) {
            text-align: right;
        }
        
        .items-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        
        .items-table tbody tr:hover {
            background: #f1f5f9;
        }
        
        /* Totals */
        .totals-section {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .totals-spacer {
            display: table-cell;
            width: 50%;
        }
        
        .totals-box {
            display: table-cell;
            width: 50%;
        }
        
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .totals-table tr td {
            padding: 8px 12px;
            font-size: 11px;
        }
        
        .totals-table tr td:first-child {
            text-align: left;
            color: #64748b;
        }
        
        .totals-table tr td:last-child {
            text-align: right;
            font-weight: bold;
        }
        
        .totals-table tr.total-row {
            background: #1a56db;
            color: white;
        }
        
        .totals-table tr.total-row td {
            font-size: 13px;
            padding: 12px;
        }
        
        /* Amount in Words */
        .amount-words {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 25px;
        }
        
        .amount-words-label {
            font-size: 9px;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        
        .amount-words-text {
            font-size: 11px;
            font-weight: bold;
            color: #1e3a8a;
        }
        
        /* Bank Info */
        .bank-info {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .bank-info table {
            width: 100%;
            font-size: 9px;
        }
        
        .bank-info td {
            padding: 3px 0;
        }
        
        .bank-info td:first-child {
            color: #64748b;
            width: 100px;
        }
        
        /* Footer */
        .footer {
            border-top: 2px solid #e2e8f0;
            padding-top: 15px;
            text-align: center;
        }
        
        .footer-text {
            font-size: 8px;
            color: #94a3b8;
            line-height: 1.6;
        }
        
        .legal-info {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .legal-item {
            display: table-cell;
            text-align: center;
            font-size: 8px;
            color: #64748b;
        }
        
        .legal-label {
            font-weight: bold;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <div class="company-name">{{ $company['name'] }}</div>
                <div class="company-details">
                    {{ $company['address'] }}<br>
                    {{ $company['city'] }}<br>
                    Tél: {{ $company['phone'] }}<br>
                    Email: {{ $company['email'] }}
                </div>
            </div>
            <div class="invoice-info">
                <div class="invoice-title">FACTURE</div>
                <div class="invoice-number">N° {{ $invoice->invoice_number }}</div>
                <div class="invoice-date">Date: {{ $invoice->invoice_date->format('d/m/Y') }}</div>
            </div>
        </div>

        <!-- Client Section -->
        <div class="client-section">
            <div class="section-title">Facturé à</div>
            <div class="client-name">{{ $invoice->client->name }}</div>
            @if($invoice->client->ice)
                <div style="font-size: 10px; color: #64748b; margin-top: 5px;">
                    ICE: {{ $invoice->client->ice }}
                </div>
            @endif
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Désignation</th>
                    <th style="width: 12%;">Prix Unit. TTC</th>
                    <th style="width: 10%;">Qté</th>
                    <th style="width: 13%;">Total HT</th>
                    <th style="width: 12%;">TVA (20%)</th>
                    <th style="width: 13%;">Total TTC</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ number_format($item->unit_price_ttc, 2, ',', ' ') }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->line_total_ht, 2, ',', ' ') }}</td>
                        <td>{{ number_format($item->line_vat, 2, ',', ' ') }}</td>
                        <td>{{ number_format($item->line_total_ttc, 2, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-spacer"></div>
            <div class="totals-box">
                <table class="totals-table">
                    <tr>
                        <td>Total HT</td>
                        <td>{{ number_format($invoice->total_ht, 2, ',', ' ') }} MAD</td>
                    </tr>
                    <tr>
                        <td>TVA ({{ number_format($invoice->vat_rate, 0) }}%)</td>
                        <td>{{ number_format($invoice->total_vat, 2, ',', ' ') }} MAD</td>
                    </tr>
                    <tr class="total-row">
                        <td>TOTAL TTC</td>
                        <td>{{ number_format($invoice->total_ttc, 2, ',', ' ') }} MAD</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Amount in Words -->
        <div class="amount-words">
            <div class="amount-words-label">Arrêté la présente facture à la somme de:</div>
            <div class="amount-words-text">{{ $invoice->total_in_words }}</div>
        </div>

        <!-- Bank Info -->
        <div class="bank-info">
            <div class="section-title">Coordonnées Bancaires</div>
            <table>
                <tr>
                    <td>Banque:</td>
                    <td>{{ $company['bank_name'] }}</td>
                </tr>
                <tr>
                    <td>RIB:</td>
                    <td>{{ $company['bank_rib'] }}</td>
                </tr>
            </table>
        </div>

        <!-- Legal Info -->
        <div class="legal-info">
            <div class="legal-item">
                <span class="legal-label">ICE:</span> {{ $company['ice'] }}
            </div>
            <div class="legal-item">
                <span class="legal-label">RC:</span> {{ $company['rc'] }}
            </div>
            <div class="legal-item">
                <span class="legal-label">IF:</span> {{ $company['if'] }}
            </div>
            <div class="legal-item">
                <span class="legal-label">Patente:</span> {{ $company['patente'] }}
            </div>
            <div class="legal-item">
                <span class="legal-label">CNSS:</span> {{ $company['cnss'] }}
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-text">
                {{ $company['name'] }} - {{ $company['address'] }}, {{ $company['city'] }}<br>
                Merci de votre confiance. Cette facture est payable à réception.
            </div>
        </div>
    </div>
</body>
</html>
