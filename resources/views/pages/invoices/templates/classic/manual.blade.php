<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Facture {{ $invoice->invoice_number }}</title>
<style>
    @page { margin: 20mm 35mm 20mm 35mm; size: A4; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', 'Arial', sans-serif; font-size: 10px; color: #000; line-height: 1.4;padding: 20px 30px; }
    .header { display: table; width: 100%; margin-bottom: 18px; border-bottom: 2px solid #000; padding-bottom: 14px; }
    .header-left { display: table-cell; vertical-align: middle; width: 70px; }
    .header-right { display: table-cell; vertical-align: middle; padding-left: 12px; }
    .logo { width: 60px; height: auto; }
    .company-name { font-size: 20px; font-weight: bold; margin-bottom: 2px; letter-spacing: 0.5px; }
    .company-subtitle { font-size: 9px; color: #555; letter-spacing: 1.5px; text-transform: uppercase; }
    .invoice-title { font-size: 13px; font-weight: bold; margin-bottom: 12px; }
    .combined-info-box { display: table; width: 100%; table-layout: fixed; margin-bottom: 24px; border: 1px solid #999; background-color: #f9f9f9; page-break-inside: avoid; }
    .combined-info-col { display: table-cell; vertical-align: top; width: 50%; padding: 14px 16px; }
    .combined-info-col.left-col { border-right: 1px solid #ccc; }
    .combined-info-col .col-title { font-weight: bold; font-size: 10px; text-decoration: underline; text-align: center; margin-bottom: 8px; text-transform: uppercase; }
    .invoice-info table { width: 100%; border-collapse: collapse; }
    .invoice-info td { padding: 3px 0; vertical-align: top; font-size: 9.5px; }
    .invoice-info .label { font-weight: bold; width: 45%; }
    .invoice-info .value { width: 55%; }
    .client-info-row { margin-bottom: 3px; font-size: 9.5px; }
    .client-info-row .label { font-weight: bold; display: inline-block; width: 65px; }
    table.items { width: 100%; border-collapse: collapse; margin: 8px 0 12px; font-size: 9px; }
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
    .totals { margin-top: 15px; margin-bottom: 15px; page-break-inside: avoid; }
    .totals-table { float: right; width: 280px; font-size: 10px; }
    .totals-table table { width: 100%; border-collapse: collapse; }
    .totals-table tr { border-bottom: 1px solid #ddd; }
    .totals-table td { padding: 6px 8px; }
    .totals-table .label { font-weight: bold; text-align: left; width: 55%; }
    .totals-table .amount { text-align: right; font-weight: 600; width: 45%; font-family: 'DejaVu Sans Mono', monospace; }
    .row-ht { background-color: #f0f0f0; }
    .remise-row .amount { color: #d32f2f; }
    .total-ttc { background-color: #eff6ff; border-top: 2px solid #1d4ed8 !important; border-bottom: 2px solid #1d4ed8 !important; }
    .total-ttc .label { font-weight: bold; color: #1d4ed8; padding: 8px; }
    .total-ttc .amount { font-weight: bold; color: #1d4ed8; padding: 8px; font-size: 11px; }
    .amount-words { clear: both; margin-top: 25px; margin-bottom: 20px; padding: 12px 15px; background-color: #f8f8f8; border: 1px solid #ddd; font-size: 9px; line-height: 1.5; page-break-inside: avoid; }
    .footer { margin-top: 20px; padding-top: 12px; border-top: 2px solid #000; font-size: 9px; line-height: 1.6; page-break-inside: avoid; text-align: center; }
    .footer-row { margin: 2px 0; text-align: center; }
    .footer-inline { display: block; text-align: center; }
    .footer-inline span { margin-right: 15px; display: inline-block; }
    .legal-mentions { margin-top: 8px; font-size: 8px; color: #555; font-style: italic; }
    .clearfix::after { content: ""; display: table; clear: both; }
</style>
</head>
<body>
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
    <div class="combined-info-box">
        <div class="combined-info-col left-col">
            <div class="col-title">Information</div>
            <div class="invoice-info">
                <table>
                    <tr>
                        <td class="label">N° FACTURE :</td>
                        <td class="value">{{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td class="label">DATE :</td>
                        <td class="value">{{ \Carbon\Carbon::parse($invoice->invoice_date)->locale('fr')->isoFormat('D MMMM YYYY') }}</td>
                    </tr>
                    <tr>
                        <td class="label">MODE DE PAY :</td>
                        <td class="value">{{ $invoice->payment_mode ?? 'ESP' }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="combined-info-col">
            <div class="col-title">Destinataire</div>
            <div class="client-info-row"><span class="label">Nom :</span> <span>{{ $invoice->client_name }}</span></div>
            @if($invoice->client_phone)<div class="client-info-row"><span class="label">Téléphone :</span> <span>{{ $invoice->client_phone }}</span></div>@endif
            @if($invoice->client_ice)<div class="client-info-row"><span class="label">ICE :</span> <span>{{ $invoice->client_ice }}</span></div>@endif
            @if($invoice->client_address)<div class="client-info-row"><span class="label">Adresse :</span> <span>{{ $invoice->client_address }}</span></div>@endif
        </div>
    </div>
    @php
        $itemCount = $invoice->items->count();
        $itemsDensityClass = $itemCount > 45 ? 'items-ultra' : ($itemCount > 25 ? 'items-tight' : ($itemCount > 15 ? 'items-compact' : ''));
    @endphp
    <table class="items {{ $itemsDensityClass }}">
        <thead>
            <tr>
                <th>Description</th>
                <th>Qté</th>
                <th>Prix unitaire TTC</th>
                <th>Total TTC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td class="description">{{ $item->product->name ?? $item->description }}</td>
                    <td class="qty">{{ number_format($item->quantity, 0) }}</td>
                    <td class="price">{{ number_format($item->unit_price, 2) }} {{ $company->currency }}</td>
                    <td class="total">{{ number_format($item->total, 2) }} {{ $company->currency }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="totals clearfix">
        <div class="totals-table">
            <table>
                @php
                    $tvaRate = (float) ($invoice->tva_rate ?? $company->tva_rate);
                    $divisor = 1 + ($tvaRate / 100);
                    $ttcBrut = $invoice->items->sum(fn($item) => $item->quantity * $item->unit_price);
                    $htBrut  = round($ttcBrut / $divisor, 2);
                    $remise  = round($invoice->remise_amount ?? 0, 2);
                    $htNet   = round($htBrut - $remise, 2);
                    $tva     = round($htNet * ($tvaRate / 100), 2);
                    $ttcNet  = round($htNet + $tva, 2);
                @endphp
                @if($remise > 0)
                    <tr class="total-ttc"><td class="label">TTC BRUT</td><td class="amount">{{ number_format($ttcBrut, 2) }} {{ $company->currency }}</td></tr>
                    <tr class="row-ht"><td class="label">MONTANT HT</td><td class="amount">{{ number_format($htBrut, 2) }} {{ $company->currency }}</td></tr>
                    <tr class="remise-row"><td class="label">Remise ({{ number_format($invoice->remise_percentage, 2) }}%)</td><td class="amount">- {{ number_format($remise, 2) }} {{ $company->currency }}</td></tr>
                    <tr class="row-tva"><td class="label">TVA ({{ $tvaRate }}%)</td><td class="amount">{{ number_format($tva, 2) }} {{ $company->currency }}</td></tr>
                    <tr class="total-ttc"><td class="label">TTC NET</td><td class="amount">{{ number_format($ttcNet, 2) }} {{ $company->currency }}</td></tr>
                @else
                    <tr class="row-ht"><td class="label">MONTANT HT</td><td class="amount">{{ number_format($htBrut, 2) }} {{ $company->currency }}</td></tr>
                    <tr class="row-tva"><td class="label">TVA ({{ $tvaRate }}%)</td><td class="amount">{{ number_format($tva, 2) }} {{ $company->currency }}</td></tr>
                    <tr class="total-ttc"><td class="label">TOTAL TTC</td><td class="amount">{{ number_format($ttcBrut, 2) }} {{ $company->currency }}</td></tr>
                @endif
            </table>
        </div>
    </div>
    <div class="amount-words">
        <strong>Arrêtée la présente facture à la somme de :</strong> {{ strtoupper($amountInWords) }}
    </div>
    <div class="footer">
        @if($company->address)<div class="footer-row"><strong>Adresse :</strong> {{ $company->address }}</div>@endif
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
        @if($company->legal_mentions)<div class="legal-mentions">{{ $company->legal_mentions }}</div>@endif
    </div>
</body>
</html>
