<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Facture {{ $invoice->invoice_number }}</title>
<style>
    @page {
        margin: 8mm 10mm 8mm 10mm;
        size: A4;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'DejaVu Sans', 'Arial', sans-serif;
        font-size: 10px;
        color: #000;
        line-height: 1.3;
        padding: 15px 20px;
    }

    /* ── Header ─────────────────────────────────────────────────────── */
    .header {
        display: table;
        width: 100%;
        margin-bottom: 12px;
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
    }
    .header-left  { display: table-cell; vertical-align: middle; width: 70px; }
    .header-right { display: table-cell; vertical-align: middle; padding-left: 12px; }
    .logo { width: 60px; height: auto; }
    .company-name     { font-size: 20px; font-weight: bold; margin-bottom: 2px; letter-spacing: 0.5px; }
    .company-subtitle { font-size: 9px; color: #555; letter-spacing: 1.5px; text-transform: uppercase; }

    /* ── Invoice Title & Info ────────────────────────────────────────── */
    .invoice-title { font-size: 13px; font-weight: bold; margin-bottom: 8px; }
    .invoice-info  { margin-bottom: 10px; font-size: 10px; }
    .invoice-info table  { width: 100%; border-collapse: collapse; }
    .invoice-info td     { padding: 3px 0; vertical-align: top; }
    .invoice-info .label { font-weight: bold; width: 25%; }
    .invoice-info .value { width: 25%; }

    /* ── Client Box ──────────────────────────────────────────────────── */
    .client-box        { margin-bottom: 10px; padding: 8px 10px; border: 1px solid #ccc; background-color: #f9f9f9; page-break-inside: avoid; }
    .client-box .title { font-weight: bold; font-size: 10px; margin-bottom: 6px; color: #333; text-transform: uppercase; }
    .client-info-row        { margin-bottom: 3px; font-size: 9px; }
    .client-info-row .label { font-weight: bold; display: inline-block; width: 70px; }

    /* ── Products Table ──────────────────────────────────────────────── */
    table.items {
        width: 100%;
        border-collapse: collapse;
        margin: 12px 0;
        font-size: 9px;
    }
    table.items thead             { background-color: #e0e0e0; }
    table.items thead tr          { border: 2px solid #000; }
    table.items thead th          { padding: 6px 5px; font-weight: bold; text-align: center; border-right: 1px solid #999; font-size: 10px; }
    table.items thead th:first-child { text-align: left; padding-left: 8px; }
    table.items thead th:last-child  { border-right: none; }

    table.items tbody tr           { border-bottom: 1px solid #ddd; height: auto; }
    table.items tbody tr:last-child { border-bottom: 2px solid #000; }
    table.items tbody td           { padding: 4px 5px; border-right: 1px solid #eee; vertical-align: middle; }
    table.items tbody td:last-child { border-right: none; }

    table.items td.description { text-align: left; padding-left: 8px; width: 50%; }
    table.items td.qty         { text-align: center; width: 12%; font-family: 'DejaVu Sans Mono', monospace; }
    table.items td.price       { text-align: right;  width: 18%; font-family: 'DejaVu Sans Mono', monospace; }
    table.items td.total       { text-align: right;  width: 20%; font-weight: 600; font-family: 'DejaVu Sans Mono', monospace; }

    /* ✅ Badge "TTC" dans l'entête du tableau */
    .badge-ttc {
        display: inline-block;
        background-color: #1d4ed8;
        color: #fff;
        font-size: 7px;
        font-weight: bold;
        padding: 1px 4px;
        border-radius: 3px;
        letter-spacing: 0.5px;
        margin-left: 3px;
        vertical-align: middle;
    }

    /* ── Totals ──────────────────────────────────────────────────────── */
    .totals        { margin-top: 15px; margin-bottom: 15px; page-break-inside: avoid; }
    .totals-table  { float: right; width: 280px; font-size: 10px; }
    .totals-table table     { width: 100%; border-collapse: collapse; }
    .totals-table tr        { border-bottom: 1px solid #ddd; }
    .totals-table td        { padding: 6px 8px; }
    .totals-table .label    { font-weight: bold; text-align: left; width: 55%; }
    .totals-table .amount   { text-align: right; font-weight: 600; width: 45%; font-family: 'DejaVu Sans Mono', monospace; }

    /* ✅ Ligne TTC (affichée en premier) */
    .row-ttc { background-color: #eff6ff; border-top: 2px solid #1d4ed8 !important; }
    .row-ttc .label  { font-weight: bold; color: #1d4ed8; }
    .row-ttc .amount { font-weight: bold; color: #1d4ed8; }

    /* ✅ Ligne remise */
    .remise-row .amount { color: #d32f2f; }

    /* ✅ Séparateur extraction TVA/HT */
    .row-separator td { border-top: 1px dashed #999 !important; background-color: #fafafa; }
    .row-separator .label  { font-size: 8px; color: #888; font-style: italic; font-weight: normal; }

    /* ✅ Ligne HT finale */
    .total-ht { background-color: #f0f0f0; border-top: 2px solid #000 !important; border-bottom: 2px solid #000 !important; }
    .total-ht .label, .total-ht .amount { font-weight: bold; padding: 8px; }

    /* ── Amount in Words ─────────────────────────────────────────────── */
    .amount-words {
        clear: both;
        margin-top: 20px; margin-bottom: 15px;
        padding: 10px 12px;
        background-color: #f8f8f8;
        border: 1px solid #ddd;
        font-size: 9px; line-height: 1.5;
        page-break-inside: avoid;
    }

    /* ── Footer ──────────────────────────────────────────────────────── */
    .footer {
        margin-top: 20px; padding-top: 12px;
        border-top: 2px solid #000;
        font-size: 9px; line-height: 1.6;
        page-break-inside: avoid;
        text-align: center;
    }
    .footer-row    { margin: 2px 0; text-align: center; }
    .footer-inline { display: block; text-align: center; }
    .footer-inline span { margin-right: 15px; display: inline-block; }

    /* ── Pagination ───────────────────────────────────────────────────── */
    @media print {
        .header               { page-break-after: avoid; }
        table.items           { page-break-inside: auto; }
        table.items thead     { display: table-header-group; }
        table.items tbody tr  { page-break-inside: avoid; }
        .totals, .amount-words, .footer { page-break-inside: avoid; }
    }

    .clearfix::after { content: ""; display: table; clear: both; }
</style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <img src="{{ public_path('images/must-negoce-logo.png') }}" alt="Logo" class="logo">
        </div>
        <div class="header-right">
            <div class="company-name">Sté. MUST-NEGOCE</div>
            <div class="company-subtitle">Consulting - Formation - Négoce</div>
        </div>
    </div>

    <!-- Invoice Title & Info -->
    <div class="invoice-title">Facture pour</div>
    <div class="invoice-info">
        <table>
            <tr>
                <td class="label">N° FACTURE :</td>
                <td class="value">{{ $invoice->invoice_number }}</td>
                <td class="label">DATE :</td>
                <td class="value">{{ $invoice->invoice_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">MODE DE PAY :</td>
                <td class="value" colspan="3">{{ $invoice->payment_mode ?? 'ESP' }}</td>
            </tr>
        </table>
    </div>

    <!-- Client Box -->
    <div class="client-box">
        <div class="title">Informations Client</div>
        <div class="client-info-row">
            <span class="label">Nom :</span>
            <span>{{ $invoice->client_name }}</span>
        </div>
        @if($invoice->client_phone)
        <div class="client-info-row">
            <span class="label">Téléphone :</span>
            <span>{{ $invoice->client_phone }}</span>
        </div>
        @endif
        @if($invoice->client_ice)
        <div class="client-info-row">
            <span class="label">ICE :</span>
            <span>{{ $invoice->client_ice }}</span>
        </div>
        @endif
        @if($invoice->client_address)
        <div class="client-info-row">
            <span class="label">Adresse :</span>
            <span>{{ $invoice->client_address }}</span>
        </div>
        @endif
    </div>

    <!-- Products Table -->
    {{--
        ✅ Les prix unitaires sont TTC.
           Montant HT = Total TTC ÷ 1.20
           TVA        = Total TTC − HT
    --}}
    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th>Qté</th>
                <th>Prix unitaire <span class="badge-ttc">TTC</span></th>
                <th>Prix total <span class="badge-ttc">TTC</span></th>
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

    <!-- ✅ Totals — ordre : TTC → (Remise) → extraction TVA/HT → MONTANT HT -->
    <div class="totals clearfix">
        <div class="totals-table">
            <table>

                {{-- 1. Total TTC (= somme des lignes) --}}
                <tr class="row-ttc">
                    <td class="label">Total TTC</td>
                    <td class="amount">{{ number_format($invoice->total_ttc, 2) }} MAD</td>
                </tr>

                {{-- 2. Remise si applicable --}}
                @if(isset($invoice->remise_percentage) && $invoice->remise_percentage > 0)
                <tr class="remise-row">
                    <td class="label">Remise {{ $invoice->remise_percentage }}%</td>
                    <td class="amount">- {{ number_format($invoice->remise_amount, 2) }} MAD</td>
                </tr>
                <tr class="row-ttc">
                    <td class="label">TTC après remise</td>
                    <td class="amount">
                        {{ number_format($invoice->total_ttc, 2) }} MAD
                    </td>
                </tr>
                @endif

                {{-- 3. Séparateur "extraction" --}}
                <tr class="row-separator">
                    <td class="label" colspan="2">Détail TVA incluse (÷ 1.20)</td>
                </tr>

                {{-- 4. TVA extraite --}}
                <tr>
                    <td class="label">dont TVA {{ $invoice->tva_rate ?? 20 }}%</td>
                    <td class="amount">{{ number_format($invoice->tva, 2) }} MAD</td>
                </tr>

                {{-- 5. MONTANT HT = TTC ÷ 1.20 --}}
                <tr class="total-ht">
                    <td class="label">MONTANT HT</td>
                    <td class="amount">{{ number_format($invoice->montant_ht, 2) }} MAD</td>
                </tr>

            </table>
        </div>
    </div>

    <!-- Amount in Words -->
    <div class="amount-words">
        <strong>Arrêter la présente facture à la somme :</strong> {{ strtoupper($amountInWords) }}
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-row"><strong>Adresse:</strong> MASSIRA 1 C IMM 90 MARRAKECH</div>
        <div class="footer-row"><strong>Tel :</strong> 0632629412</div>
        <div class="footer-row footer-inline">
            <span><strong>R.C:</strong> 86613</span>
            <span><strong>I.F:</strong> 25055643</span>
            <span><strong>ICE :</strong> 001996252000022</span>
            <span><strong>TP:</strong> 92105859</span>
        </div>
    </div>

</body>
</html>