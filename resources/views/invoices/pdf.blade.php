<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Facture - {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 15px;
            size: A4;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #000;
        }
        
        .container {
            width: 100%;
            padding: 10px;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .header-title {
            font-size: 12px;
            font-weight: bold;
            margin: 5px 0;
        }
        
        /* Invoice Info Table */
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        
        .info-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 10px;
        }
        
        .info-table td.label {
            font-weight: bold;
            width: 25%;
            background-color: #f5f5f5;
        }
        
        .info-table td.value {
            width: 25%;
        }
        
        /* Products Table */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .products-table thead {
            background-color: #f0f0f0;
        }
        
        .products-table th {
            border: 1px solid #000;
            padding: 8px 5px;
            font-weight: bold;
            text-align: center;
            font-size: 10px;
        }
        
        .products-table td {
            border: 1px solid #000;
            padding: 6px 5px;
            font-size: 9px;
        }
        
        .products-table td.description {
            text-align: left;
            padding-left: 8px;
        }
        
        .products-table td.center {
            text-align: center;
        }
        
        .products-table td.right {
            text-align: right;
            padding-right: 8px;
        }
        
        .products-table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }
        
        /* Totals Table */
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .totals-table td {
            border: 1px solid #000;
            padding: 8px 12px;
            font-size: 11px;
        }
        
        .totals-table td.label {
            font-weight: bold;
            text-align: left;
            width: 70%;
            background-color: #f5f5f5;
        }
        
        .totals-table td.amount {
            text-align: right;
            width: 30%;
            font-weight: bold;
        }
        
        .totals-table tr.highlight {
            background-color: #e8e8e8;
        }
        
        .totals-table tr.highlight td {
            font-size: 13px;
            font-weight: bold;
            padding: 10px 12px;
        }
        
        /* Amount in Words */
        .amount-words {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 9px;
            text-align: justify;
        }
        
        .amount-words strong {
            font-size: 10px;
        }
        
        /* Footer */
        .footer {
            border-top: 1px solid #000;
            padding-top: 8px;
            font-size: 8px;
            text-align: center;
            line-height: 1.6;
        }
        
        .footer-row {
            margin: 3px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">Sté. MUST-NEGOCE</div>
            <div class="header-title">Facture pour</div>
        </div>
        
        <!-- Invoice Info -->
        <table class="info-table">
            <tr>
                <td class="label">N° FACTURE :</td>
                <td class="value">{{ $invoice->invoice_number }}</td>
                <td class="label">DATE :</td>
                <td class="value">{{ $invoice->invoice_date->format('n/j/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Nom de client :</td>
                <td class="value">{{ $invoice->customer_name }}</td>
                <td class="label">MODE DE PAY :</td>
                <td class="value">CH</td>
            </tr>
        </table>
        
        <!-- Products Table -->
        <table class="products-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th style="width: 12%;">Qté</th>
                    <th style="width: 18%;">Prix unitaire</th>
                    <th style="width: 20%;">Prix total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $subtotalHT = 0;
                @endphp
                
                @foreach($invoice->items as $item)
                <tr>
                    <td class="description">{{ $item->product->name }}</td>
                    <td class="center">{{ $item->quantity }}</td>
                    <td class="right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="right"><strong>{{ number_format($item->subtotal, 2) }}</strong></td>
                </tr>
                @php
                    $subtotalHT += $item->subtotal;
                @endphp
                @endforeach
                
                <!-- Empty rows for spacing (minimum 5 rows) -->
                @for($i = count($invoice->items); $i < 5; $i++)
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                @endfor
            </tbody>
        </table>
        
        <!-- Totals -->
        <table class="totals-table">
            @php
                // Calcul TVA 20%
                $tauxTVA = 20;
                $montantTTC = $invoice->total_amount;
                $montantHT = $montantTTC / 1.20;
                $montantTVA = $montantTTC - $montantHT;
            @endphp
            
            <tr class="highlight">
                <td class="label">MONTANT TTC</td>
                <td class="amount">{{ number_format($montantTTC, 2) }} MAD</td>
            </tr>
            <tr>
                <td class="label">Taux TVA</td>
                <td class="amount">{{ $tauxTVA }}%</td>
            </tr>
            <tr>
                <td class="label">Montant TVA</td>
                <td class="amount">{{ number_format($montantTVA, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Montant HT</td>
                <td class="amount">{{ number_format($montantHT, 2) }}</td>
            </tr>
        </table>
        
        <!-- Amount in Words -->
        <div class="amount-words">
            <strong>Arrêter la présente facture à la somme :</strong><br>
            {{ strtoupper(\App\Helpers\NumberToWords::convertToFrench($montantTTC)) }} DIRHAMS
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-row"><strong>Tel :</strong> 06 62482531</div>
            <div class="footer-row"><strong>TP:</strong> 47960328</div>
            <div class="footer-row"><strong>Adresse :</strong> Mourabitoun commune Saada Marrakech</div>
            <div class="footer-row">
                <strong>R.C:</strong> 86613 | 
                <strong>I.F:</strong> 25055643 | 
                <strong>ICE :</strong> 001996252000022
            </div>
        </div>
    </div>
</body>
</html>