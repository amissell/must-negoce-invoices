<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Facture {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 10mm;
            size: A4;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.4;
            padding: 20px 30px;
        }
        
        /* Header Section */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 80px;
        }
        .header-right {
            display: table-cell;
            vertical-align: middle;
            padding-left: 15px;
        }
        .logo {
            width: 70px;
            height: auto;
        }
        .company-name {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
        }
        .company-subtitle {
            font-size: 10px;
            color: #555;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        
        /* Invoice Title */
        .invoice-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 12px;
        }
        
        /* Invoice Info */
        .invoice-info {
            margin-bottom: 20px;
            font-size: 11px;
        }
        .invoice-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-info td {
            padding: 4px 0;
            vertical-align: top;
        }
        .invoice-info .label {
            font-weight: bold;
            width: 25%;
        }
        .invoice-info .value {
            width: 25%;
        }
        
        /* Products Table */
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10px;
        }
        table.items thead {
            background-color: #e0e0e0;
        }
        table.items thead tr {
            border: 2px solid #000;
        }
        table.items thead th {
            padding: 10px 8px;
            font-weight: bold;
            text-align: center;
            border-right: 1px solid #999;
            font-size: 11px;
        }
        table.items thead th:first-child {
            text-align: left;
            padding-left: 12px;
        }
        table.items thead th:last-child {
            border-right: none;
        }
        table.items tbody tr {
            border-bottom: 1px solid #ddd;
        }
        table.items tbody tr:last-child {
            border-bottom: 2px solid #000;
        }
        table.items tbody td {
            padding: 6px 8px;
            border-right: 1px solid #eee;
            vertical-align: middle;
        }
        table.items tbody td:last-child {
            border-right: none;
        }
        table.items td.description {
            text-align: left;
            padding-left: 12px;
            width: 50%;
        }
        table.items td.qty {
            text-align: center;
            width: 12%;
            font-family: 'DejaVu Sans Mono', monospace;
        }
        table.items td.price {
            text-align: right;
            width: 18%;
            font-family: 'DejaVu Sans Mono', monospace;
        }
        table.items td.total {
            text-align: right;
            width: 20%;
            font-weight: 600;
            font-family: 'DejaVu Sans Mono', monospace;
        }
        
        /* Totals Section */
        .totals {
            margin-top: 25px;
            margin-bottom: 20px;
        }
        .totals-table {
            float: right;
            width: 280px;
            font-size: 11px;
        }
        .totals-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table tr {
            border-bottom: 1px solid #ddd;
        }
        .totals-table td {
            padding: 8px 10px;
        }
        .totals-table .label {
            font-weight: bold;
            text-align: left;
            width: 55%;
        }
        .totals-table .amount {
            text-align: right;
            font-weight: 600;
            width: 45%;
            font-family: 'DejaVu Sans Mono', monospace;
        }
        .total-ttc {
            background-color: #f0f0f0;
            font-size: 12px;
            border-top: 2px solid #000 !important;
            border-bottom: 2px solid #000 !important;
        }
        .total-ttc .label,
        .total-ttc .amount {
            font-weight: bold;
            padding: 10px;
        }
        
        /* Amount in Words */
        .amount-words {
            clear: both;
            margin-top: 50px;
            margin-bottom: 30px;
            padding: 12px 15px;
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            font-size: 10px;
            line-height: 1.6;
        }
        .amount-words strong {
            font-weight: bold;
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #000;
            font-size: 10px;
            line-height: 1.8;
        }
        .footer-row {
            margin: 3px 0;
        }
        .footer strong {
            font-weight: bold;
        }
        .footer-inline {
            display: inline;
        }
        .footer-inline span {
            margin-right: 20px;
        }
        
        /* Clearfix */
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>
    <!-- Header with Logo -->
    <div class="header">
        <div class="header-left">
            <img src="{{ public_path('images/must-negoce-logo.png') }}" alt="Logo" class="logo">
        </div>
        <div class="header-right">
            <div class="company-name">Sté. MUST-NEGOCE</div>
            <div class="company-subtitle">Consulting - Formation - Négoce</div>
        </div>
    </div>

    <!-- Invoice Title & Information -->
    <div class="invoice-title">Facture pour</div>
    <div class="invoice-info">
        <table>
            <tr>
                <td class="label">N° FACTURE :</td>
                <td class="value">{{ $invoice->invoice_number }}</td>
                <td class="label">DATE :</td>
                <td class="value">{{ $invoice->invoice_date->format('n/j/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Nom de client :</td>
                <td class="value">{{ $invoice->client_name ?? 'divers' }}</td>
                <td class="label">MODE DE PAY :</td>
                <td class="value">{{ $invoice->payment_mode ?? 'ESP' }}</td>
            </tr>
        </table>
    </div>

    <!-- Products Table -->
    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th>Qté</th>
                <th>Prix unitaire</th>
                <th>Prix total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td class="description">{{ $item->product->name ?? $item->description }}</td>
                <td class="qty">{{ number_format($item->quantity, 0) }}</td>
                <td class="price">{{ number_format($item->unit_price, 2) }}</td>
                <td class="total">{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals clearfix">
        <div class="totals-table">
            <table>
                <tr class="total-ttc">
                    <td class="label">MONTANT TTC</td>
                    <td class="amount">{{ number_format($invoice->total_ttc, 2) }} MAD</td>
                </tr>
                <tr>
                    <td class="label">Taux TVA</td>
                    <td class="amount">{{ $invoice->tva_rate ?? 20 }}%</td>
                </tr>
                <tr>
                    <td class="label">Montant TVA</td>
                    <td class="amount">{{ number_format($invoice->tva, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Montant HT</td>
                    <td class="amount">{{ number_format($invoice->montant_ht, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Amount in Words -->
    <div class="amount-words">
        <strong>Arrêter la présence facture a la somme :</strong> {{ strtoupper($amountInWords) }}
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <div class="footer-row"><strong>Adresse:</strong>MASSIRA 1 C IMM 90 MARRAKECH</div>
        <div class="footer-row"><strong>Tel :</strong>0632629412</div>
        <div class="footer-row footer-inline">
            <span><strong>R.C:</strong>86613</span>
            <span><strong>I.F:</strong>25055643</span>
            <span><strong>ICE :</strong>001996252000022</span>
            <span><strong>TP:</strong>92105859</span>
        </div>
    </div>
</body>
</html>
