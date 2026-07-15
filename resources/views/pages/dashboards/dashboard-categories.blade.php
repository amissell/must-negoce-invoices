@extends('layouts.app')
@section('title', 'Dashboard Catégories API')

@push('styles')
<style>
    .page-hero {
        background: linear-gradient(135deg, #1e3a5f 0%, #2d5380 50%, #14B8A6 100%);
        border-radius: 24px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 12px 32px rgba(30, 58, 95, 0.15);
    }

    .page-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        animation: float 20s ease-in-out infinite;
    }

    @keyframes float {
        0%,100% {
            transform: translate(0, 0) rotate(0deg);
        }
        50% {
            transform: translate(-30px, 30px) rotate(180deg);
        }
    }

    /* ═══════════════ FILTER BAR ═══════════════ */
    .filter-bar {
        background: white;
        border-radius: 16px;
        padding: 20px 24px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .filter-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #64748b;
        margin-bottom: 6px;
        display: block;
    }

    .filter-select {
        padding: 10px 14px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        background: #f8fafc;
        transition: all 0.25s ease;
        outline: none;
        min-width: 140px;
    }

    .filter-select:hover {
        border-color: #cbd5e1;
        background: white;
    }

    .filter-select:focus {
        border-color: #14B8A6;
        background: white;
        box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1);
    }

    .btn-filter-apply {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: linear-gradient(135deg, #1e3a5f, #2d5380);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.25s ease;
        box-shadow: 0 4px 12px rgba(30, 58, 95, 0.2);
    }

    .btn-filter-apply:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(30, 58, 95, 0.3);
        background: linear-gradient(135deg, #152d48, #1e3a5f);
    }

    .kpi-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #1e3a5f, #14B8A6);
    }

    .kpi-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
        border-color: #14B8A6;
    }

    .kpi-card.blue::before {
        background: linear-gradient(90deg, #1e3a5f, #2d5380);
    }

    .kpi-card.teal::before {
        background: linear-gradient(90deg, #14B8A6, #2dd4bf);
    }

    .kpi-icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 12px;
    }

    .kpi-value {
        font-size: 28px;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 6px;
        color: #1e293b;
    }

    .kpi-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
    }
    .table-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        border: 1px solid #e2e8f0;
    }

    .table-header {
        background: linear-gradient(135deg, #1e3a5f, #2d5380);
        padding: 20px 28px;
        color: white;
    }

    .table-header h2 {
        font-size: 16px;
        font-weight: 700;
        margin: 0;
    }

    .dashboard-table {
        width: 100%;
        border-collapse: collapse;
    }

    .dashboard-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 16px 20px;
        border-bottom: 2px solid #e2e8f0;
        text-align: left;
    }

    .dashboard-table tbody td {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: #1e293b;
    }

    .dashboard-table tbody tr:hover {
        background: #f8fafc;
        transition: all 0.2s ease;
    }

    /* ═══════════════ BADGE ═══════════════ */
    .badge-drops {
        background: #eff6ff;
        color: #1e3a5f;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        border: 1px solid #bfdbfe;
        white-space: nowrap;
        display: inline-block;
    }

    /* ═══════════════ ERROR ALERT ═══════════════ */
    .error-alert {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        background: #fef2f2;
        border: 1px solid #fca5a5;
        border-radius: 12px;
        padding: 16px 20px;
        border-left: 4px solid #ef4444;
    }

    .error-alert-title {
        font-weight: 700;
        color: #991b1b;
        margin: 0;
    }

    .error-alert-message {
        font-size: 14px;
        color: #dc2626;
        margin: 6px 0 0 0;
    }

    /* ═══════════════ ANIMATIONS ═══════════════ */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(24px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in-up {
        animation: fadeInUp 0.5s ease-out backwards;
    }

    /* ═══════════════ CHART STYLING ═══════════════ */
    canvas {
        max-height: 300px;
    }

    /* ═══════════════ RESPONSIVE ═══════════════ */
    @media (max-width: 768px) {
        .page-hero {
            padding: 24px !important;
        }

        .filter-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-select,
        .btn-filter-apply {
            width: 100%;
        }

        .kpi-card {
            padding: 20px;
        }

        .kpi-value {
            font-size: 24px;
        }

        .dashboard-table {
            font-size: 13px;
        }

        .dashboard-table th,
        .dashboard-table td {
            padding: 12px 16px;
        }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen" style="background:linear-gradient(to bottom,#F8FAFC 0%,#EFF6FF 100%);">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- ══════════════ HERO ══════════════ --}}
    <div class="page-hero p-8 fade-in-up">
        <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                        <span class="text-xl">🏷️</span>
                    </div>
                    <h1 class="text-3xl font-extrabold text-white">DASHBOARD CATÉGORIES</h1>
                </div>
                <p class="text-sky-100 text-sm font-medium">
                    @if($data)
                        {{ \Carbon\Carbon::create($annee, (int)$mois)->translatedFormat('F Y') }}
                    @else
                        Données non disponibles
                    @endif
                </p>
            </div>
            <a href="{{ route('dashboard.vendeurs') }}"
               class="px-5 py-2.5 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white rounded-xl text-sm font-bold transition-all border border-white/30">
                ← Dashboard Vendeurs
            </a>
        </div>
    </div>

    {{-- ══════════════ FILTRES ══════════════ --}}
    <div class="filter-bar fade-in-up" style="animation-delay:0.05s">
        <form method="GET" id="filterForm" action="{{ route('dashboard.categories') }}" class="flex flex-wrap items-end gap-3 w-full">
            <div>
                <div class="filter-label">📅 Mois</div>
                <select name="mois" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ (int)$mois == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null,$m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <div class="filter-label">🗓 Année</div>
                <select name="annee" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                    @foreach([2024,2025,2026] as $y)
                        <option value="{{ $y }}" {{ $annee == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 items-end">
                <button type="submit" class="btn-filter-apply">🔍 Filtrer</button>
            </div>
        </form>
    </div>

    {{-- ══════════════ ERREUR ══════════════ --}}
    @if($erreur)
        <div class="error-alert">
            <span style="font-size: 20px; flex-shrink: 0;">⚠️</span>
            <div>
                <p class="error-alert-title">Erreur API</p>
                <p class="error-alert-message">{{ $erreur }}</p>
            </div>
        </div>
    @endif

    @if($data)
    @php
        // Modern Professional Color Palette - Blue & Teal Theme
        $catColors = [
            ['hex'=>'#1e3a5f', 'text'=>'text-[#1e3a5f]'],
            ['hex'=>'#2d5380', 'text'=>'text-[#2d5380]'],
            ['hex'=>'#14B8A6', 'text'=>'text-[#14B8A6]'],
            ['hex'=>'#2dd4bf', 'text'=>'text-[#2dd4bf]'],
            ['hex'=>'#0d9488', 'text'=>'text-[#0d9488]'],
            ['hex'=>'#3b7cb8', 'text'=>'text-[#3b7cb8]'],
            ['hex'=>'#152d48', 'text'=>'text-[#152d48]'],
            ['hex'=>'#006b5b', 'text'=>'text-[#006b5b]'],
        ];
        $categoriesData = $data['categoriesData'] ?? [];
    @endphp

    {{-- ══════════════ KPI CARDS ══════════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-2 gap-4">
        <div class="kpi-card blue fade-in-up" style="animation-delay:0s">
            <div class="kpi-icon bg-indigo-50">💰</div>
            <div class="kpi-value text-indigo-600">{{ number_format($data['cat_ca_total'] ?? 0,0,',',' ') }} DH</div>
            <div class="kpi-label">CA Total Catégories</div>
        </div>
        <div class="kpi-card teal fade-in-up" style="animation-delay:0.05s">
            <div class="kpi-icon bg-teal-50">📦</div>
            <div class="kpi-value text-teal-600">{{ number_format($data['cat_qte_total'] ?? 0,0,',',' ') }}</div>
            <div class="kpi-label">Quantité Totale Vendue</div>
        </div>
    </div>
{{-- ══════════════ RÉPARTITION PAR CATÉGORIE ══════════════ --}}
    <div class="table-card fade-in-up" style="animation-delay:0.15s">
        <div class="table-header">
            <h2 class="text-lg font-extrabold flex items-center gap-2">🏷️ RÉPARTITION PAR CATÉGORIE</h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 p-6">
            {{-- Tableau --}} <div class="overflow-x-auto">
                <table class="min-w-full dashboard-table">
                    <thead>
                        <tr>
                            <th class="text-left">CATÉGORIE</th>
                            <th class="text-center">CA</th>
                            <th class="text-center">QTÉ</th>
                            <th class="text-center">PART</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categoriesData as $idx => $cat)
                            @php $cc = $catColors[$idx % count($catColors)]; @endphp
                            <tr>
                                <td class="font-bold text-gray-900">
                                    <span class="inline-block w-2.5 h-2.5 rounded-full mr-2" style="background:{{ $cc['hex'] }}"></span>
                                    {{ $cat['libelle'] }}
                                </td>
                                <td class="text-center font-semibold" style="color:{{ $cc['hex'] }}">{{ number_format($cat['ca'],0,',',' ') }} DH</td>
                                <td class="text-center text-gray-600">{{ number_format($cat['qte'],0,',',' ') }}</td>
                                <td class="text-center">
                                    <span class="badge-drops">{{ number_format($cat['pct'],1) }}%</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-gray-400 py-6">Aucune donnée de catégorie pour cette période.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Camembert --}}
            <div>
                <canvas id="categoryPieChart" height="240"></canvas>
            </div>
        </div>
    </div>
    {{-- ══════════════ ÉVOLUTION PAR CATÉGORIE ══════════════ --}}
    <div class="table-card fade-in-up" style="animation-delay:0.25s">
        <div class="p-6">
            <h3 class="font-bold text-gray-900 mb-4">📈 Évolution du CA par Catégorie</h3>
            <canvas id="categoryEvoChart" height="280"></canvas>
        </div>
    </div>

    @endif

</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@if($data)
// Professional Modern Color Palette - Corporate Blue & Teal
const COLORS = ['#1e3a5f','#2d5380','#14B8A6','#2dd4bf','#0d9488','#3b7cb8','#152d48','#006b5b'];

const categoriesData = @json($data['categoriesData'] ?? []);
const catEvoLabels   = @json($data['catEvoLabels'] ?? []);
const catEvoSeries   = @json($data['catEvoSeries'] ?? []);

// Camembert : répartition du CA par catégorie
if (categoriesData.length > 0) {
    new Chart(document.getElementById('categoryPieChart'), {
        type: 'doughnut',
        data: {
            labels: categoriesData.map(c => c.libelle),
            datasets: [{
                data: categoriesData.map(c => parseFloat(c.ca) || 0),
                backgroundColor: COLORS,
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true, cutout: '60%',
            plugins: {
                legend: { position: 'bottom', labels: { font: { size:11, weight:600 }, boxWidth:12, padding:12 } },
                tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed.toLocaleString('fr-MA')} DH` } }
            }
        }
    });
}
if (catEvoLabels.length > 0 && catEvoSeries.length > 0) {
    const catLineDatasets = catEvoSeries.map((serie, index) => ({
        label: serie.nom,
        data: serie.data,
        borderColor: COLORS[index % COLORS.length],
        backgroundColor: COLORS[index % COLORS.length] + '20',
        borderWidth: 2.5, tension: 0.4, pointRadius: 3, pointHoverRadius: 6,
        pointBackgroundColor: COLORS[index % COLORS.length],
        pointBorderColor: '#fff', pointBorderWidth: 2, fill: false,
    }));

    new Chart(document.getElementById('categoryEvoChart'), {
        type: 'line',
        data: { labels: catEvoLabels, datasets: catLineDatasets },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'bottom', labels: { font: { size:11, weight:600 }, boxWidth:12, padding:14 } },
                tooltip: { callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString('fr-MA')} DH` } }
            },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => (v/1000).toFixed(0)+'k DH', font:{ size:11 } }, grid:{ color:'#F1F5F9' } },
                x: { ticks: { font:{ size:10, weight:600 } }, grid:{ display:false } }
            }
        }
    });
}
@endif
</script>
@endpush
