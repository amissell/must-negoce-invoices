@extends('layouts.app')

@section('title', 'Dashboard Exécutif')

@section('content')
@php
    $k          = $kpis;
    $recv       = $k['receivables'];
    $deltaPct   = $k['month_delta_percent'];
    $deltaClass = $k['month_delta'] >= 0 ? 'text-green-600' : 'text-red-600';
    $deltaArrow = $k['month_delta'] >= 0 ? '▲' : '▼';
    $pulse       = $sales_pulse;
    $target      = $company_target;
    $financial   = $financial_analysis;
    $cashFlow    = $cash_flow;
    $currency    = $currency ?? 'MAD';

    $fmt = fn ($n) => number_format((float) $n, 2, ',', ' ') . ' ' . $currency;
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📊 Dashboard Exécutif</h1>
            <p class="text-gray-500 mt-1">Vue synthétique fondée sur les factures, paiements, objectifs et suivi locaux — {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p class="mt-2 text-xs text-slate-600">Les marges sont calculées uniquement sur les lignes avec coût connu. Les lignes sans coût sont exclues pour éviter toute surestimation.</p>
        </div>

        {{-- Quick actions --}}
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('invoices.manual.create') }}" class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">
                ➕ Nouvelle facture
            </a>
            <a href="{{ route('clients.create') }}" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                👤 Nouveau client
            </a>
            <a href="{{ route('creances.index') }}" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                💰 Créances
            </a>
            <a href="{{ route('alertes.index') }}" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                🔔 Alertes
                @if($k['alertes_count'] > 0)
                    <span class="ml-1 inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-500 text-white text-xs">{{ $k['alertes_count'] }}</span>
                @endif
            </a>
            <a href="{{ route('dashboard.executive.board_pdf') }}" class="px-4 py-2 rounded-xl bg-green-600 text-white text-sm font-medium hover:bg-green-700 transition">📄 Télécharger Board PDF</a>
        </div>
        <div id="vue-scheduled-reports" class="w-full mt-4"></div>
    </div>

    {{-- Vue widgets mount points (Vite-built components will mount here) --}}
    <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div id="vue-sales-pulse" class="bg-white rounded-2xl card-shadow p-4" data-initial='@json($sales_pulse)'></div>
        <div id="vue-ceo-scorecard" class="bg-white rounded-2xl card-shadow p-4" data-initial='@json(["mtd_revenue" => $k["current_month_revenue"], "outstanding_receivables" => $recv["total"], "overdue_invoices" => $k["overdue_invoice_count"]])'></div>
    </div>

    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        <strong>Attention :</strong> ce tableau reflète les données enregistrées localement et ne comprend pas les coûts historiques, stocks, fournisseurs ou attributs vendeur Amevo non disponibles dans le schéma actuel.
    </div>

    {{-- Daily sales pulse and target attainment --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl card-shadow p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Pouls des ventes — aujourd'hui</p>
                    <p class="text-2xl font-bold text-gray-900 mt-2">{{ $fmt($pulse['current']) }}</p>
                    @php $weekPct = $pulse['vs_last_week']['percent']; @endphp
                    <p class="text-xs mt-1 {{ $pulse['vs_last_week']['delta'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $pulse['vs_last_week']['delta'] >= 0 ? '▲' : '▼' }}
                        {{ is_null($weekPct) ? 'n/a' : number_format(abs($weekPct), 1, ',', ' ') . '%' }} vs même jour S-1
                    </p>
                </div>
                <div class="w-40 h-16"><canvas id="chartSalesPulse"></canvas></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl card-shadow p-5">
            <div class="flex justify-between gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">CA vs objectif société</p>
                    <p class="text-2xl font-bold text-gray-900 mt-2">{{ $fmt($target['actual']) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Objectif : {{ $target['target'] > 0 ? $fmt($target['target']) : 'non défini' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xl font-bold {{ ($target['attainment_percent'] ?? 0) >= 100 ? 'text-green-600' : 'text-amber-600' }}">
                        {{ is_null($target['attainment_percent']) ? '—' : number_format($target['attainment_percent'], 1, ',', ' ') . '%' }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Projection : {{ $fmt($target['projection']) }}</p>
                </div>
            </div>
            <div class="mt-3 h-2 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full {{ ($target['attainment_percent'] ?? 0) >= 100 ? 'bg-green-500' : 'bg-teal-500' }}" style="width: {{ min(100, $target['attainment_percent'] ?? 0) }}%"></div>
            </div>
        </div>
    </div>

    {{-- CEO five-number scorecard --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl card-shadow p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide">CA MTD</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $fmt($k['current_month_revenue']) }}</p>
            <p class="text-xs mt-1 {{ $deltaClass }}">
                {{ $deltaArrow }}
                @if(is_null($deltaPct))
                    n/a
                @else
                    {{ number_format(abs($deltaPct), 1, ',', ' ') }}%
                @endif
                <span class="text-gray-400">vs {{ $fmt($k['previous_month_revenue']) }}</span>
            </p>
        </div>

        <div class="bg-white rounded-2xl card-shadow p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide">MTD vs objectif</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ is_null($target['attainment_percent']) ? '—' : number_format($target['attainment_percent'], 1, ',', ' ') . '%' }}</p>
            <p class="text-xs text-gray-500 mt-1">Projection {{ $fmt($target['projection']) }}</p>
        </div>

        <div class="bg-white rounded-2xl card-shadow p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Créances totales</p>
            <p class="text-2xl font-bold text-red-600 mt-2">{{ $fmt($recv['total']) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $k['alertes_count'] }} alerte(s) active(s)</p>
        </div>

        <div class="bg-white rounded-2xl card-shadow p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Clients actifs</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $k['active_clients']['last_30_days'] }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $k['active_clients']['last_60_days'] }} / {{ $k['active_clients']['last_90_days'] }} sur 60 / 90 j.</p>
        </div>

        <div class="bg-white rounded-2xl card-shadow p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Factures en retard</p>
            <p class="text-2xl font-bold text-red-600 mt-2">{{ $k['overdue_invoice_count'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Solde restant exigible</p>
        </div>
    </div>

    {{-- Period-over-period context --}}
    <div class="bg-white rounded-2xl card-shadow p-6 overflow-x-auto">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Comparaison du chiffre d'affaires</h2>
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Période</th><th class="py-2 text-right">CA actuel</th><th class="py-2">Référence</th><th class="py-2 text-right">CA référence</th><th class="py-2 text-right">Évolution</th></tr></thead>
            <tbody>
                @foreach($revenue_comparison as $comparison)
                    <tr class="border-b last:border-0"><td class="py-3 font-medium">{{ $comparison['label'] }}</td><td class="py-3 text-right">{{ $fmt($comparison['current']) }}</td><td class="py-3 text-gray-600">{{ $comparison['comparison_label'] }}</td><td class="py-3 text-right">{{ $fmt($comparison['comparison']) }}</td><td class="py-3 text-right font-semibold {{ $comparison['delta'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ is_null($comparison['percent']) ? 'n/a' : ($comparison['delta'] >= 0 ? '+' : '') . number_format($comparison['percent'], 1, ',', ' ') . '%' }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Financial analysis --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl card-shadow p-6">
            <div class="flex justify-between gap-3 mb-4"><h2 class="text-lg font-semibold text-gray-900">Marge brute par produit</h2><span class="text-xs text-gray-500">Coût connu uniquement — prix TTC − coût TTC</span></div>
            <div style="height: 280px;"><canvas id="chartProductMargin"></canvas></div>
            @if($financial['missing_cost_lines'] > 0)
                <p class="mt-3 text-xs text-amber-700">{{ $financial['missing_cost_lines'] }} ligne(s) exclue(s) : coût produit manquant dans le système local.</p>
            @endif
            <div id="vue-margin-details"></div>
        </div>
        <div class="bg-white rounded-2xl card-shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">TVA collectée — 12 mois</h2>
            <div style="height: 280px;"><canvas id="chartTvaLiability"></canvas></div>
            <p class="mt-3 text-xs text-gray-500">Taux société configuré : {{ number_format($charts['tva_liability']['configured_rate'], 2, ',', ' ') }}%</p>
            <div id="vue-tva-details"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        @foreach(['clients' => 'Marge par client', 'sellers' => 'Marge par vendeur (créateur de facture)'] as $dimension => $title)
            <div class="bg-white rounded-2xl card-shadow p-6 overflow-x-auto">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ $title }}</h2>
                <p class="mb-3 text-xs text-slate-500">Source locale : factures et coût produit connu uniquement.</p>
                <table class="w-full text-sm"><thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Libellé</th><th class="py-2 text-right">Marge</th><th class="py-2 text-right">Marge %</th></tr></thead><tbody>
                    @forelse($financial[$dimension]['labels'] as $index => $label)
                        <tr class="border-b last:border-0"><td class="py-2 font-medium">{{ $label }}</td><td class="py-2 text-right">{{ $fmt($financial[$dimension]['margins'][$index]) }}</td><td class="py-2 text-right {{ $financial[$dimension]['percentages'][$index] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($financial[$dimension]['percentages'][$index], 1, ',', ' ') }}%</td></tr>
                    @empty
                        <tr><td colspan="3" class="py-4 text-center text-gray-400">Aucune ligne avec coût renseigné.</td></tr>
                    @endforelse
                </tbody></table>
            </div>
        @endforeach
    </div>

    {{-- Cash flow --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl card-shadow p-6">
            <div class="flex justify-between items-start gap-4 mb-4"><div><h2 class="text-lg font-semibold text-gray-900">Encaissements vs CA facturé</h2><p class="text-xs text-gray-500 mt-1">Comparaison mensuelle du cash et du revenu comptabilisé</p></div>
                @php $latestRatio = collect($cashFlow['collected_vs_invoiced']['collection_ratios'])->filter(fn ($ratio) => !is_null($ratio))->last(); @endphp
                <span class="text-sm font-semibold {{ ($latestRatio ?? 0) >= 100 ? 'text-green-600' : 'text-amber-600' }}">{{ is_null($latestRatio) ? '—' : number_format($latestRatio, 1, ',', ' ') . '%' }}</span>
            </div>
            <div style="height: 300px;"><canvas id="chartCashFlow"></canvas></div>
        </div>
        <div class="bg-white rounded-2xl card-shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Mix des modes de paiement</h2>
            <p class="text-xs text-gray-500 mb-4">Les chèques et traites sont suivis séparément pour visualiser le risque de liquidité.</p>
            <div style="height: 300px;"><canvas id="chartPaymentModes"></canvas></div>
        </div>
    </div>

    {{-- Receivables aging --}}
    <div class="bg-white rounded-2xl card-shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Vieillissement des créances</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-xl bg-green-50 border border-green-100">
                <p class="text-xs text-green-700 uppercase tracking-wide">0 – 30 jours</p>
                <p class="text-xl font-bold text-green-800 mt-1">{{ $fmt($recv['j30']) }}</p>
            </div>
            <div class="p-4 rounded-xl bg-yellow-50 border border-yellow-100">
                <p class="text-xs text-yellow-700 uppercase tracking-wide">31 – 60 jours</p>
                <p class="text-xl font-bold text-yellow-800 mt-1">{{ $fmt($recv['j60']) }}</p>
            </div>
            <div class="p-4 rounded-xl bg-red-50 border border-red-100">
                <p class="text-xs text-red-700 uppercase tracking-wide">+ 60 jours</p>
                <p class="text-xl font-bold text-red-800 mt-1">{{ $fmt($recv['j90_plus']) }}</p>
            </div>
        </div>
    </div>

    {{-- Charts row 1: Revenue by month --}}
    <div class="bg-white rounded-2xl card-shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">CA facturé — 12 derniers mois</h2>
        <div style="height: 320px;"><canvas id="chartRevenueMonth"></canvas></div>
    </div>

    {{-- Charts row 2: category + user --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl card-shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">CA par catégorie (mois en cours)</h2>
            <div style="height: 300px;"><canvas id="chartRevenueCategory"></canvas></div>
            @if(empty($charts['revenue_by_category']['labels']))
                <p class="text-sm text-gray-400 text-center mt-4">Aucune donnée pour ce mois.</p>
            @endif
        </div>

        <div class="bg-white rounded-2xl card-shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">CA par utilisateur (mois en cours)</h2>
            <div style="height: 300px;"><canvas id="chartRevenueUser"></canvas></div>
            @if(empty($charts['revenue_by_user']['labels']))
                <p class="text-sm text-gray-400 text-center mt-4">Aucune donnée pour ce mois.</p>
            @endif
        </div>
    </div>

    {{-- Top clients + Top products --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl card-shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Top 5 clients (12 mois)</h2>
            @if($top_clients->isEmpty())
                <p class="text-sm text-gray-400">Aucune donnée.</p>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2">Client</th>
                            <th class="py-2 text-right">Factures</th>
                            <th class="py-2 text-right">CA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($top_clients as $c)
                            <tr class="border-b last:border-0">
                                <td class="py-2 font-medium text-gray-800">{{ $c['client'] }}</td>
                                <td class="py-2 text-right text-gray-600">{{ $c['nb_factures'] }}</td>
                                <td class="py-2 text-right font-semibold text-gray-900">{{ $fmt($c['total']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="bg-white rounded-2xl card-shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Top 5 produits (12 mois)</h2>
            @if($top_products->isEmpty())
                <p class="text-sm text-gray-400">Aucune donnée.</p>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2">Produit</th>
                            <th class="py-2 text-right">Quantité</th>
                            <th class="py-2 text-right">CA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($top_products as $p)
                            <tr class="border-b last:border-0">
                                <td class="py-2 font-medium text-gray-800">{{ $p['produit'] }}</td>
                                <td class="py-2 text-right text-gray-600">{{ $p['quantite'] }}</td>
                                <td class="py-2 text-right font-semibold text-gray-900">{{ $fmt($p['total']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Recent invoices --}}
    <div class="bg-white rounded-2xl card-shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Factures récentes</h2>
        @if($recent_invoices->isEmpty())
            <p class="text-sm text-gray-400">Aucune facture récente.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2">N°</th>
                            <th class="py-2">Date</th>
                            <th class="py-2">Client</th>
                            <th class="py-2">Créé par</th>
                            <th class="py-2 text-right">Total TTC</th>
                            <th class="py-2 text-right">Statut</th>
                            <th class="py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recent_invoices as $inv)
                            @php
                                $statut = $inv->statut_paiement ?? 'impayée';
                                $statutClass = match($statut) {
                                    'payée'     => 'bg-green-100 text-green-700',
                                    'partielle' => 'bg-yellow-100 text-yellow-700',
                                    default     => 'bg-red-100 text-red-700',
                                };
                            @endphp
                            <tr class="border-b last:border-0 hover:bg-gray-50">
                                <td class="py-2 font-mono text-xs text-gray-700">{{ $inv->invoice_number }}</td>
                                <td class="py-2 text-gray-600">{{ \Carbon\Carbon::parse($inv->invoice_date)->format('d/m/Y') }}</td>
                                <td class="py-2 text-gray-800">{{ $inv->client?->nom ?? $inv->client_name ?? '—' }}</td>
                                <td class="py-2 text-gray-600">{{ $inv->creator_name }}</td>
                                <td class="py-2 text-right font-semibold">{{ $fmt($inv->total_ttc) }}</td>
                                <td class="py-2 text-right">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statutClass }}">{{ $statut }}</span>
                                </td>
                                <td class="py-2 text-right">
                                    <a href="{{ route('invoices.download', $inv->id) }}" class="text-blue-600 hover:underline text-xs">PDF</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

{{-- Chart.js (CDN — pas de nouveau package) --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const revenueMonth    = @json($charts['revenue_by_month']);
    const revenueCategory = @json($charts['revenue_by_category']);
    const revenueUser     = @json($charts['revenue_by_user']);
    const salesPulse      = @json($sales_pulse);
    const financial       = @json($financial_analysis);
    const tvaLiability    = @json($charts['tva_liability']);
    const cashFlow        = @json($cash_flow);

    const currency = (v) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(v) + ' MAD';

    const commonScales = {
        y: { beginAtZero: true, ticks: { callback: (v) => currency(v) } },
        x: { grid: { display: false } },
    };

    new Chart(document.getElementById('chartSalesPulse'), {
        type: 'line',
        data: { labels: salesPulse.sparkline.map((_, i) => i + 1), datasets: [{ data: salesPulse.sparkline, borderColor: '#14B8A6', borderWidth: 2, pointRadius: 0, tension: 0.35, fill: false }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { enabled: false } }, scales: { x: { display: false }, y: { display: false } } },
    });

    new Chart(document.getElementById('chartProductMargin'), {
        type: 'bar',
        data: { labels: financial.products.labels, datasets: [{ label: 'Marge brute', data: financial.products.margins, backgroundColor: '#14B8A6', borderRadius: 6 }] },
        options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx) => currency(ctx.parsed.x) } } }, scales: { x: { ticks: { callback: (v) => currency(v) } }, y: { grid: { display: false } } } },
    });

    new Chart(document.getElementById('chartTvaLiability'), {
        type: 'bar',
        data: { labels: tvaLiability.labels, datasets: [{ label: 'TVA collectée', data: tvaLiability.values, backgroundColor: '#2d5380', borderRadius: 6 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx) => currency(ctx.parsed.y) } } }, scales: commonScales },
    });

    new Chart(document.getElementById('chartCashFlow'), {
        type: 'line',
        data: { labels: cashFlow.collected_vs_invoiced.labels, datasets: [
            { label: 'CA facturé', data: cashFlow.collected_vs_invoiced.invoiced, borderColor: '#2d5380', backgroundColor: 'rgba(45,83,128,.1)', fill: true, tension: .35 },
            { label: 'Encaissements', data: cashFlow.collected_vs_invoiced.collected, borderColor: '#14B8A6', backgroundColor: 'rgba(20,184,166,.1)', fill: true, tension: .35 },
        ] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { tooltip: { callbacks: { label: (ctx) => currency(ctx.parsed.y) } } }, scales: commonScales },
    });

    new Chart(document.getElementById('chartPaymentModes'), {
        type: 'line', data: { labels: cashFlow.payment_mode_mix.labels, datasets: cashFlow.payment_mode_mix.datasets },
        options: { responsive: true, maintainAspectRatio: false, plugins: { tooltip: { callbacks: { label: (ctx) => currency(ctx.parsed.y) } } }, elements: { line: { tension: .3 }, point: { radius: 1 } }, scales: commonScales },
    });

    // Revenue by month — line chart
    new Chart(document.getElementById('chartRevenueMonth'), {
        type: 'line',
        data: {
            labels: revenueMonth.labels,
            datasets: [{
                label: 'CA facturé',
                data: revenueMonth.values,
                borderColor: '#1e3a5f',
                backgroundColor: 'rgba(20, 184, 166, 0.15)',
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#14B8A6',
                pointRadius: 4,
            }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (ctx) => currency(ctx.parsed.y) } },
            },
            scales: commonScales,
        },
    });

    // Revenue by category — doughnut
    if (revenueCategory.labels.length) {
        new Chart(document.getElementById('chartRevenueCategory'), {
            type: 'doughnut',
            data: {
                labels: revenueCategory.labels,
                datasets: [{
                    data: revenueCategory.values,
                    backgroundColor: ['#1e3a5f','#14B8A6','#2d5380','#2dd4bf','#0d9488','#64748b','#94a3b8','#0ea5e9','#f59e0b','#ef4444'],
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${currency(ctx.parsed)}` } },
                },
            },
        });
    }

    // Revenue by user — bar
    if (revenueUser.labels.length) {
        new Chart(document.getElementById('chartRevenueUser'), {
            type: 'bar',
            data: {
                labels: revenueUser.labels,
                datasets: [{
                    label: 'CA (mois en cours)',
                    data: revenueUser.values,
                    backgroundColor: '#14B8A6',
                    borderRadius: 6,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => currency(ctx.parsed.y) } },
                },
                scales: commonScales,
            },
        });
    }
})();
</script>
@endsection
