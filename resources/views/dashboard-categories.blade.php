@extends('layouts.app')
@section('title', 'Dashboard Catégories API')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    * { font-family: 'Plus Jakarta Sans', sans-serif; }

    .gradient-blue   { background: linear-gradient(135deg, #4F46E5 0%, #6366F1 100%); }
    .gradient-orange { background: linear-gradient(135deg, #F97316 0%, #FB923C 100%); }
    .gradient-green  { background: linear-gradient(135deg, #10B981 0%, #34D399 100%); }
    .gradient-red    { background: linear-gradient(135deg, #EF4444 0%, #F87171 100%); }
    .gradient-purple { background: linear-gradient(135deg, #8B5CF6 0%, #A78BFA 100%); }
    .gradient-yellow { background: linear-gradient(135deg, #F59E0B 0%, #FCD34D 100%); }
    .gradient-teal   { background: linear-gradient(135deg, #14B8A6 0%, #2DD4BF 100%); }

    .page-hero {
        background: linear-gradient(135deg, #1e3a5f 0%, #3b7cb8 100%);
        border-radius: 24px; position: relative; overflow: hidden;
    }
    .page-hero::before {
        content: ''; position: absolute; top: -50%; right: -20%;
        width: 600px; height: 600px;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
        animation: float 20s ease-in-out infinite;
    }
    @keyframes float {
        0%,100% { transform: translate(0,0) rotate(0deg); }
        50%      { transform: translate(-30px,30px) rotate(180deg); }
    }

    .filter-bar {
        background: white; border-radius: 20px; padding: 20px 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.07); border: 1px solid rgba(0,0,0,0.05);
        display: flex; align-items: center; flex-wrap: wrap; gap: 12px;
    }
    .filter-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.6px; color:#94A3B8; margin-bottom:4px; }
    .filter-select {
        padding: 9px 14px; border: 2px solid #E2E8F0; border-radius: 12px;
        font-size: 13px; font-weight: 600; color: #1E293B; background: #F8FAFC;
        transition: all 0.2s; outline: none; font-family: 'Plus Jakarta Sans', sans-serif; min-width: 140px;
    }
    .filter-select:focus { border-color: #3b7cb8; background: white; box-shadow: 0 0 0 3px rgba(59,124,184,0.10); }
    .btn-filter-apply {
        display: inline-flex; align-items: center; gap: 6px; padding: 9px 20px;
        background: linear-gradient(135deg, #1e3a5f, #3b7cb8); color: white; border: none;
        border-radius: 12px; font-weight: 700; font-size: 13px; cursor: pointer; transition: all 0.2s;
        box-shadow: 0 3px 10px rgba(30,58,95,0.3); font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .btn-filter-apply:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(30,58,95,0.35); }

    .kpi-card {
        background: white; border-radius: 20px; padding: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
        position: relative; overflow: hidden; border: 1px solid rgba(0,0,0,0.05);
    }
    .kpi-card::before { content:''; position:absolute; top:0; left:0; right:0; height:4px; }
    .kpi-card:hover { transform: translateY(-6px); box-shadow: 0 12px 32px rgba(0,0,0,0.12); }
    .kpi-card.blue::before   { background: linear-gradient(90deg,#4F46E5,#6366F1); }
    .kpi-card.teal::before   { background: linear-gradient(90deg,#14B8A6,#2DD4BF); }
    .kpi-icon  { width:48px; height:48px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:20px; margin-bottom:12px; }
    .kpi-value { font-size:26px; font-weight:800; line-height:1.1; margin-bottom:6px; }
    .kpi-label { font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; color:#64748B; }

    .table-card { background:white; border-radius:20px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08); border:1px solid rgba(0,0,0,0.05); }
    .table-header { background:linear-gradient(135deg,#1e3a5f,#3b7cb8); padding:20px 28px; color:white; }
    .dashboard-table thead th { background:#F8FAFC; color:#475569; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; padding:16px 20px; border-bottom:2px solid #E2E8F0; }
    .dashboard-table tbody td { padding:16px 20px; border-bottom:1px solid #F1F5F9; vertical-align:middle; }
    .dashboard-table tbody tr:hover { background:#F8FAFC; }

    @keyframes fadeInUp { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:translateY(0); } }
    .fade-in-up { animation: fadeInUp 0.5s ease-out backwards; }

    .badge-drops { background:#EFF6FF; color:#1e3a5f; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:800; border:1px solid #BFDBFE; white-space:nowrap; }
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
        <div class="flex items-start gap-3 bg-red-50 border border-red-200 rounded-xl px-5 py-4">
            <span class="text-red-500 text-lg mt-0.5">🔴</span>
            <div>
                <p class="font-bold text-red-700">Erreur API</p>
                <p class="text-sm text-red-600 mt-1">{{ $erreur }}</p>
            </div>
        </div>
    @endif

    @if($data)
    @php
        $catColors = [
            ['hex'=>'#1e3a5f', 'text'=>'text-[#1e3a5f]'],
            ['hex'=>'#F97316', 'text'=>'text-orange-600'],
            ['hex'=>'#10B981', 'text'=>'text-emerald-600'],
            ['hex'=>'#EF4444', 'text'=>'text-red-600'],
            ['hex'=>'#8B5CF6', 'text'=>'text-purple-600'],
            ['hex'=>'#F59E0B', 'text'=>'text-amber-600'],
            ['hex'=>'#14B8A6', 'text'=>'text-teal-600'],
            ['hex'=>'#EC4899', 'text'=>'text-pink-600'],
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
const COLORS = ['#1e3a5f','#F97316','#10B981','#EF4444','#8B5CF6','#F59E0B','#14B8A6','#EC4899'];

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

// Courbes : évolution du CA par catégorie, jour par jour
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
