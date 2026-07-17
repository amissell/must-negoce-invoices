@extends('layouts.app')

@section('title', 'Dashboard Exécutif')

@section('content')
@php
    $k          = $kpis;
    $recv       = $k['receivables'];
    $deltaPct   = $k['month_delta_percent'];
    $deltaClass = $k['month_delta'] >= 0 ? 'text-green-600' : 'text-red-600';
    $deltaArrow = $k['month_delta'] >= 0 ? '▲' : '▼';

    $fmt = fn ($n) => number_format((float) $n, 2, ',', ' ') . ' MAD';
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📊 Dashboard Exécutif</h1>
            <p class="text-gray-500 mt-1">Vue synthétique de l'activité — {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
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
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl card-shadow p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide">CA facturé aujourd'hui</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $fmt($k['today_revenue']) }}</p>
        </div>

        <div class="bg-white rounded-2xl card-shadow p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Encaissements aujourd'hui</p>
            <p class="text-2xl font-bold text-green-600 mt-2">{{ $fmt($k['today_payments']) }}</p>
        </div>

        <div class="bg-white rounded-2xl card-shadow p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide">CA mois en cours</p>
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
            <p class="text-xs text-gray-500 uppercase tracking-wide">Créances totales</p>
            <p class="text-2xl font-bold text-red-600 mt-2">{{ $fmt($recv['total']) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $k['alertes_count'] }} alerte(s) active(s)</p>
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

    const currency = (v) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(v) + ' MAD';

    const commonScales = {
        y: { beginAtZero: true, ticks: { callback: (v) => currency(v) } },
        x: { grid: { display: false } },
    };

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
