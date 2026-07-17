<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Facture {{ $invoice->invoice_number }}</title>
<style>
    @page { margin: 0; size: A4; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', 'Helvetica', sans-serif; font-size: 10px; color: #1e293b; line-height: 1.5; }
    .page { padding: 30px 40px; }
    .hero { background: #1e3a5f; color: #fff; padding: 24px 28px; border-radius: 10px; display: table; width: 100%; margin-bottom: 22px; }
    .hero-left { display: table-cell; vertical-align: middle; width: 70%; }
    .hero-right { display: table-cell; vertical-align: middle; text-align: right; }
    .logo { max-width: 70px; max-height: 60px; background: #fff; padding: 6px; border-radius: 8px; }
    .company-name { font-size: 22px; font-weight: bold; margin-top: 8px; }
    .company-tag { font-size: 10px; opacity: 0.85; margin-top: 3px; letter-spacing: 1px; text-transform: uppercase; }
    .doc-title { font-size: 26px; font-weight: bold; color: #14B8A6; letter-spacing: 3px; }
    .doc-number { font-size: 11px; opacity: 0.9; margin-top: 6px; }
    .meta { display: table; width: 100%; margin-bottom: 18px; }
    .meta-box { display: table-cell; width: 50%; padding: 14px 16px; background: #f1f5f9; border-radius: 8px; vertical-align: top; }
    .meta-title { font-size: 9px; font-weight: bold; text-transform: uppercase; color: #64748b; letter-spacing: 1px; margin-bottom: 6px; }
    .meta-value { font-size: 12px; font-weight: 600; }
    .meta-line { font-size: 10px; color: #475569; margin-top: 3px; }
    table.items { width: 100%; border-collapse: collapse; margin: 18px 0; font-size: 10px; border-radius: 8px; overflow: hidden; }
    table.items thead { background: #1e3a5f; color: #fff; }
    table.items thead th { padding: 10px 8px; font-weight: 600; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
    table.items thead th.num { text-align: right; }
    table.items tbody tr { border-bottom: 1px solid #e2e8f0; }
    table.items tbody tr:nth-child(even) { background: #f8fafc; }
    table.items tbody td { padding: 9px 8px; }
    table.items td.num { text-align: right; font-family: 'DejaVu Sans Mono', monospace; }
    .totals { margin-top: 18px; display: table; width: 100%; }
    .totals-spacer { display: table-cell; width: 55%; }
    .totals-box { display: table-cell; width: 45%; background: #f1f5f9; border-radius: 8px; padding: 14px 18px; }
    .totals-row { display: table; width: 100%; padding: 4px 0; }
    .totals-row .label { display: table-cell; font-size: 10px; color: #475569; }
    .totals-row .amount { display: table-cell; text-align: right; font-weight: 600; font-family: 'DejaVu Sans Mono', monospace; }
    .totals-row.remise .amount { color: #dc2626; }
    .grand { margin-top: 8px; padding-top: 10px; border-top: 2px solid #14B8A6; }
    .grand .label { font-weight: bold; color: #1e3a5f; font-size: 12px; }
    .grand .amount { color: #14B8A6; font-size: 14px; font-weight: bold; }
    .words { margin-top: 22px; padding: 14px 18px; background: #fefce8; border-left: 4px solid #f59e0b; border-radius: 4px; font-size: 10px; }
    .footer { margin-top: 28px; padding-top: 14px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #64748b; text-align: center; line-height: 1.7; }
    .footer-legals { margin-top: 8px; display: block; }
    .footer-legals span { margin: 0 6px; }
    .footer-legals strong { color: #1e3a5f; }
    .legal-mentions { margin-top: 10px; font-size: 8px; color: #94a3b8; font-style: italic; }
</style>
</head>
<body>
<div class="page">
    <div class="hero">
        <div class="hero-left">
            @if($company->logo_path_for_pdf)
                <img src="{{ $company->logo_path_for_pdf }}" alt="Logo" class="logo">
            @endif
            <div class="company-name">{{ $company->name }}</div>
            @if($company->footer_text)<div class="company-tag">{{ $company->footer_text }}</div>@endif
        </div>
        <div class="hero-right">
            <div class="doc-title">FACTURE</div>
            <div class="doc-number">N° {{ $invoice->invoice_number }}</div>
            <div class="doc-number">{{ \Carbon\Carbon::parse($invoice->invoice_date)->locale('fr')->isoFormat('D MMM YYYY') }}</div>
        </div>
    </div>

    <div class="meta">
        <div class="meta-box">
            <div class="meta-title">Client</div>
            <div class="meta-value">{{ $invoice->client_name }}</div>
            @if($invoice->client_phone)<div class="meta-line">Tél : {{ $invoice->client_phone }}</div>@endif
            @if($invoice->client_ice)<div class="meta-line">ICE : {{ $invoice->client_ice }}</div>@endif
            @if($invoice->client_address)<div class="meta-line">{{ $invoice->client_address }}</div>@endif
        </div>
        <div class="meta-box">
            <div class="meta-title">Paiement</div>
            <div class="meta-value">{{ $invoice->payment_mode ?? 'ESP' }}</div>
            <div class="meta-line">Date : {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</div>
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th class="num">Qté</th>
                <th class="num">P.U. TTC</th>
                <th class="num">Total TTC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->product->name ?? $item->description }}</td>
                    <td class="num">{{ number_format($item->quantity, 0) }}</td>
                    <td class="num">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="num">{{ number_format($item->total, 2) }} {{ $company->currency }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

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

    <div class="totals">
        <div class="totals-spacer"></div>
        <div class="totals-box">
            @if($remise > 0)
                <div class="totals-row"><span class="label">TTC BRUT</span><span class="amount">{{ number_format($ttcBrut, 2) }} {{ $company->currency }}</span></div>
                <div class="totals-row"><span class="label">Montant HT</span><span class="amount">{{ number_format($htBrut, 2) }} {{ $company->currency }}</span></div>
                <div class="totals-row remise"><span class="label">Remise ({{ number_format($invoice->remise_percentage, 2) }}%)</span><span class="amount">- {{ number_format($remise, 2) }} {{ $company->currency }}</span></div>
                <div class="totals-row"><span class="label">TVA ({{ $tvaRate }}%)</span><span class="amount">{{ number_format($tva, 2) }} {{ $company->currency }}</span></div>
                <div class="totals-row grand"><span class="label">TTC NET</span><span class="amount">{{ number_format($ttcNet, 2) }} {{ $company->currency }}</span></div>
            @else
                <div class="totals-row"><span class="label">Montant HT</span><span class="amount">{{ number_format($htBrut, 2) }} {{ $company->currency }}</span></div>
                <div class="totals-row"><span class="label">TVA ({{ $tvaRate }}%)</span><span class="amount">{{ number_format($tva, 2) }} {{ $company->currency }}</span></div>
                <div class="totals-row grand"><span class="label">TOTAL TTC</span><span class="amount">{{ number_format($ttcBrut, 2) }} {{ $company->currency }}</span></div>
            @endif
        </div>
    </div>

    <div class="words"><strong>Arrêtée la présente facture à la somme de :</strong> {{ strtoupper($amountInWords) }}</div>

    <div class="footer">
        @if($company->address){{ $company->address }}@endif
        @if($company->phone) &nbsp;•&nbsp; Tél : {{ $company->phone }}@endif
        @if($company->email) &nbsp;•&nbsp; {{ $company->email }}@endif
        @if($company->website) &nbsp;•&nbsp; {{ $company->website }}@endif
        <div class="footer-legals">
            @if($company->rc)<span><strong>RC :</strong> {{ $company->rc }}</span>@endif
            @if($company->if_number)<span><strong>IF :</strong> {{ $company->if_number }}</span>@endif
            @if($company->ice)<span><strong>ICE :</strong> {{ $company->ice }}</span>@endif
            @if($company->patente)<span><strong>Patente :</strong> {{ $company->patente }}</span>@endif
            @if($company->cnss)<span><strong>CNSS :</strong> {{ $company->cnss }}</span>@endif
        </div>
        @if($company->legal_mentions)<div class="legal-mentions">{{ $company->legal_mentions }}</div>@endif
    </div>
</div>
</body>
</html>
