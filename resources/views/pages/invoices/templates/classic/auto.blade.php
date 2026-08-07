<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Facture {{ $invoice->invoice_number }}</title>
<style>
    @page { margin: 15mm 20mm 15mm 20mm; size: A4; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', 'Arial', sans-serif; font-size: 10px; color: #000; line-height: 1.4; }
    .header { display: table; width: 100%; margin-bottom: 18px; border-bottom: 2px solid #000; padding-bottom: 14px; }
    .header-left { display: table-cell; vertical-align: middle; width: 70px; }
    .header-right { display: table-cell; vertical-align: middle; padding-left: 12px; }
    .logo { width: 60px; height: auto; }
    .company-name { font-size: 20px; font-weight: bold; margin-bottom: 2px; letter-spacing: 0.5px; }
    .company-subtitle { font-size: 9px; color: #555; letter-spacing: 1.5px; text-transform: uppercase; }
    .invoice-title { font-size: 13px; font-weight: bold; margin-bottom: 8px; }
    .invoice-info { margin-bottom: 10px; font-size: 10px; }
    .invoice-info table { width: 100%; border-collapse: collapse; }
    .invoice-info td { padding: 3px 0; vertical-align: top; }
    .invoice-info .label { font-weight: bold; width: 25%; }
    .invoice-info .value { width: 25%; }
    table.items { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 9px; }
    table.items thead { background-color: #e0e0e0; }
    table.items thead tr { border: 2px solid #000; }
    table.items thead th { padding: 6px 5px; font-weight: bold; text-align: center; border-right: 1px solid #999; font-size: 10px; }
    table.items thead th:first-child { text-align: left; padding-left: 8px; }
    table.items thead th:last-child { border-right: none; }
    table.items tbody tr { border-bottom: 1px solid #ddd; }
    table.items tbody tr:last-child { border-bottom: 2px solid #000; }
    table.items tbody td { padding: 4px 5px; border-right: 1px solid #eee; vertical-align: middle; }
    table.items tbody td:last-child { border-right: none; }
    table.items td.description { text-align: left; padding-left: 8px; width: 50%; }
    table.items td.qty { text-align: center; width: 12%; font-family: 'DejaVu Sans Mono', monospace; }
    table.items td.price { text-align: right; width: 18%; font-family: 'DejaVu Sans Mono', monospace; }
    table.items td.total { text-align: right; width: 20%; font-weight: 600; font-family: 'DejaVu Sans Mono', monospace; }
    /* Auto-shrink item rows when the invoice has many products, so everything still fits nicely */
    table.items.items-compact { font-size: 8px; }
    table.items.items-compact thead th { padding: 4px 5px; font-size: 8.5px; }
    table.items.items-compact tbody td { padding: 2.5px 5px; }
    table.items.items-tight { font-size: 7px; }
    table.items.items-tight thead th { padding: 3px 4px; font-size: 7.5px; }
    table.items.items-tight tbody td { padding: 1.5px 4px; }
    table.items.items-ultra { font-size: 6.5px; }
    table.items.items-ultra thead th { padding: 2px 3px; font-size: 7px; }
    table.items.items-ultra tbody td { padding: 1px 3px; line-height: 1.1; }
    /* When density is high, shrink the surrounding blocks too, to free more room for rows */
    body.dense .header { margin-bottom: 8px; padding-bottom: 8px; }
    body.dense .invoice-info { margin-bottom: 4px; }
    body.dense .totals { margin-top: 6px; margin-bottom: 6px; }
    body.dense .amount-words { margin-top: 10px; margin-bottom: 10px; padding: 6px 10px; }
    body.dense .footer { margin-top: 8px; padding-top: 6px; }
    .totals { margin-top: 15px; margin-bottom: 15px; page-break-inside: avoid; }
    .totals-table { float: right; width: 260px; font-size: 10px; }
    .totals-table table { width: 100%; border-collapse: collapse; }
    .totals-table tr { border-bottom: 1px solid #ddd; }
    .totals-table td { padding: 6px 8px; }
    .totals-table .label { font-weight: bold; text-align: left; width: 55%; }
    .totals-table .amount { text-align: right; font-weight: 600; width: 45%; font-family: 'DejaVu Sans Mono', monospace; }
    .total-ttc { background-color: #f0f0f0; font-size: 11px; border-top: 2px solid #000 !important; border-bottom: 2px solid #000 !important; }
    .total-ttc .label, .total-ttc .amount { font-weight: bold; padding: 8px; }
    .amount-words { clear: both; margin-top: 25px; margin-bottom: 20px; padding: 12px 15px; background-color: #f8f8f8; border: 1px solid #ddd; font-size: 9px; line-height: 1.5; page-break-inside: avoid; }
    .footer { margin-top: 20px; padding-top: 12px; border-top: 2px solid #000; font-size: 9px; line-height: 1.6; page-break-inside: avoid; text-align: center; }
    .footer-row { margin: 2px 0; text-align: center; }
    .footer-inline { display: block; text-align: center; }
    .footer-inline span { margin-right: 15px; display: inline-block; }
    .legal-mentions { margin-top: 8px; font-size: 8px; color: #555; font-style: italic; }
    .clearfix::after { content: ""; display: table; clear: both; }
</style>
</head>
<body @php $itemCount = $invoice->items->count(); @endphp class="{{ $itemCount > 15 ? 'dense' : '' }}">
    <div class="header">
        <div class="header-left">
            @if($company->logo_path_for_pdf)
                <img src="{{ $company->logo_path_for_pdf }}" alt="Logo" class="logo">
            @endif
        </div>
        <div class="header-right">
            <div class="company-name">{{ $company->name }}</div>
            @if($company->footer_text)
                <div class="company-subtitle">{{ $company->footer_text }}</div>
            @endif
        </div>
    </div>

    <div class="invoice-title">Facture pour</div>
    <div class="invoice-info">
        <table>
            <tr>
                <td class="label">N° FACTURE :</td>
                <td class="value">{{ $invoice->invoice_number }}</td>
                <td class="label">DATE :</td>
                <td class="value">{{ \Carbon\Carbon::parse($invoice->invoice_date)->locale('fr')->isoFormat('D MMMM YYYY') }}</td>
            </tr>
            <tr>
                <td class="label">Nom de client :</td>
                <td class="value">{{ $invoice->client_name ?? 'divers' }}</td>
                <td class="label">MODE DE PAY :</td>
                <td class="value">{{ $invoice->payment_mode ?? 'ESP' }}</td>
            </tr>
        </table>
    </div>

    @php
        $itemsDensityClass = $itemCount > 45 ? 'items-ultra' : ($itemCount > 25 ? 'items-tight' : ($itemCount > 15 ? 'items-compact' : ''));
    @endphp
    <table class="items {{ $itemsDensityClass }}">
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

    <div class="totals clearfix">
        <div class="totals-table">
            <table>
                <tr>
                    <td class="label">Montant HT</td>
                    <td class="amount">{{ number_format($invoice->montant_ht, 2) }} {{ $company->currency }}</td>
                </tr>
                <tr>
                    <td class="label">TVA {{ $invoice->tva_rate ?? $company->tva_rate }}%</td>
                    <td class="amount">{{ number_format($invoice->tva, 2) }} {{ $company->currency }}</td>
                </tr>
                <tr class="total-ttc">
                    <td class="label">MONTANT TTC</td>
                    <td class="amount">{{ number_format($invoice->total_ttc, 2) }} {{ $company->currency }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="amount-words">
        <strong>Arrêtée la présente facture à la somme de :</strong> {{ strtoupper($amountInWords) }}
    </div>

    <div class="footer">
        @if($company->address)
            <div class="footer-row"><strong>Adresse :</strong> {{ $company->address }}</div>
        @endif
        @if($company->phone || $company->email || $company->website)
            <div class="footer-row">
                @if($company->phone)<strong>Tél :</strong> {{ $company->phone }}@endif
                @if($company->email) &nbsp;|&nbsp; <strong>Email :</strong> {{ $company->email }}@endif
                @if($company->website) &nbsp;|&nbsp; <strong>Web :</strong> {{ $company->website }}@endif
            </div>
        @endif
        <div class="footer-row footer-inline">
            @if($company->rc)<span><strong>R.C :</strong> {{ $company->rc }}</span>@endif
            @if($company->if_number)<span><strong>I.F :</strong> {{ $company->if_number }}</span>@endif
            @if($company->ice)<span><strong>ICE :</strong> {{ $company->ice }}</span>@endif
            @if($company->patente)<span><strong>Patente :</strong> {{ $company->patente }}</span>@endif
            @if($company->cnss)<span><strong>CNSS :</strong> {{ $company->cnss }}</span>@endif
        </div>
        @if($company->legal_mentions)
            <div class="legal-mentions">{{ $company->legal_mentions }}</div>
        @endif
    </div>
</body>
</html>
