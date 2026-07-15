@extends('layouts.app')
@section('title', 'Ventes par Vendeur / Catégorie')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    * { font-family: 'Plus Jakarta Sans', sans-serif; }
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
    .kpi-card::before { content:''; position:absolute; top:0; left:0; right:0; height:4px; background: linear-gradient(90deg,#4F46E5,#6366F1); }
    .kpi-icon  { width:48px; height:48px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:20px; margin-bottom:12px; }
    .kpi-value { font-size:26px; font-weight:800; line-height:1.1; margin-bottom:6px; }
    .kpi-label { font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; color:#64748B; }

    .table-card { background:white; border-radius:20px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08); border:1px solid rgba(0,0,0,0.05); }
    .table-header { background:linear-gradient(135deg,#1e3a5f,#3b7cb8); padding:20px 28px; color:white; }

    .pivot-table { border-collapse: collapse; white-space: nowrap; }
    .pivot-table thead th {
        background:#F8FAFC; color:#475569; font-size:11px; font-weight:700; text-transform:uppercase;
        letter-spacing:0.5px; padding:14px 18px; border-bottom:2px solid #E2E8F0; position: sticky; top: 0;
    }
    .pivot-table thead th:first-child { text-align:left; position: sticky; left: 0; z-index: 2; background:#F8FAFC; }
    .pivot-table tbody td { padding:14px 18px; border-bottom:1px solid #F1F5F9; text-align:right; color:#334155; font-weight:500; }
    .pivot-table tbody td:first-child {
        text-align:left; font-weight:700; color:#1E293B; position: sticky; left: 0; background:white; z-index: 1;
    }
    .pivot-table tbody tr:hover td { background:#F8FAFC; }
    .pivot-table tbody tr:hover td:first-child { background:#F1F5F9; }
    .pivot-table tbody td.total-cell { font-weight:800; color:#1e3a5f; background:#EFF6FF; }
    .pivot-table tbody tr:hover td.total-cell { background:#DBEAFE; }
    .pivot-table tfoot td {
        padding:14px 18px; text-align:right; font-weight:800; color:white;
        background:linear-gradient(135deg,#1e3a5f,#3b7cb8); border-top:2px solid #1e3a5f;
    }
    .pivot-table tfoot td:first-child { text-align:left; position: sticky; left:0; z-index: 2; }
    .cell-zero { color:#CBD5E1; font-weight:400; }

    @keyframes fadeInUp { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:translateY(0); } }
    .fade-in-up { animation: fadeInUp 0.5s ease-out backwards; }
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
                        <span class="text-xl">📊</span>
                    </div>
                    <h1 class="text-3xl font-extrabold text-white">VENTES PAR VENDEUR / CATÉGORIE</h1>
                </div>
                <p class="text-sky-100 text-sm font-medium">
                    {{ \Carbon\Carbon::create($annee, (int)$mois)->translatedFormat('F Y') }}
                    · données locales (suivi_vendeurs / suivi_categories), agrégées par SUM / GROUP BY
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('dashboard.vendeurs') }}"
                   class="px-5 py-2.5 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white rounded-xl text-sm font-bold transition-all border border-white/30">
                    Vendeurs
                </a>
                <a href="{{ route('dashboard.categories') }}"
                   class="px-5 py-2.5 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white rounded-xl text-sm font-bold transition-all border border-white/30">
                    Catégories
                </a>
            </div>
        </div>
    </div>

    {{-- ══════════════ FILTRES ══════════════ --}}
    <div class="filter-bar fade-in-up" style="animation-delay:0.05s">
        <form method="GET" id="filterForm" action="{{ route('dashboard.vendeur-categorie') }}" class="flex flex-wrap items-end gap-3 w-full">
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


    {{-- ══════════════ KPI ══════════════ --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

    <div class="kpi-card fade-in-up">
        <div class="kpi-icon bg-indigo-50">💰</div>
        <div class="kpi-value text-indigo-600">
            {{ number_format($totalGeneral,0,',',' ') }} DH
        </div>
        <div class="kpi-label">
            Chiffre d'affaires Total
        </div>
    </div>

    <div class="kpi-card fade-in-up">
        <div class="kpi-icon bg-teal-50">🧑‍💼</div>
        <div class="kpi-value text-teal-600">
            {{ $vendorCards->count() }}
        </div>
        <div class="kpi-label">
            Nombre de vendeurs
        </div>
    </div>

    <div class="kpi-card fade-in-up">
        <div class="kpi-icon bg-orange-50">🏷️</div>
        <div class="kpi-value text-orange-600">
            {{ $categoriesData->count() }}
        </div>
        <div class="kpi-label">
            Nombre de catégories
        </div>
    </div>

</div>
<div class="table-card fade-in-up mt-6">

    <div class="table-header">
        <h2 class="text-lg font-extrabold">
            📊 Estimation du CA par vendeur / catégorie
        </h2>
    </div>

    <div class="overflow-x-auto">

        <table class="pivot-table min-w-full">

            <thead>

                <tr>

                    <th>Vendeur</th>

                    <th class="text-right">
                        CA Total
                    </th>

                    @foreach($categoriesData as $cat)

                        <th class="text-right">
                            {{ $cat['libelle'] }}
                        </th>

                    @endforeach

                </tr>

            </thead>

            <tbody>

            @foreach($vendorCards as $vendeur)

                <tr>

                    <td>
                        {{ $vendeur['nom'] }}
                    </td>

                    <td class="total-cell">
                        {{ number_format($vendeur['ca_total'],0,',',' ') }} DH
                    </td>

                    @foreach($categoriesData as $cat)

                        @php
                            $estimation = ($vendeur['ca_total'] * $cat['pct']) / 100;
                        @endphp

                        <td>

                            <strong>
                                {{ number_format($estimation,0,',',' ') }}
                            </strong>

                            <br>

                            <small class="text-gray-500">
                                {{ number_format($cat['pct'],1) }} %
                            </small>

                        </td>

                    @endforeach

                </tr>

            @endforeach

            </tbody>

        </table>

    </div>

</div>

<div class="mt-6 bg-yellow-50 border border-yellow-300 rounded-xl p-4">

    <strong>ℹ️ Information</strong>
    <p class="mt-2 text-sm text-gray-700">

        Les montants affichés par catégorie sont des estimations calculées
        à partir du chiffre d'affaires du vendeur et de la répartition
        globale des catégories fournie par l'API AMEVO.

        Ils ne représentent pas les ventes réelles du vendeur par catégorie.

    </p>
</div>
<p class="text-xs text-gray-500 mt-6">
    Les données affichées proviennent directement de l'API AMEVO
    (<strong>vendorCards</strong> et <strong>categoriesData</strong>).
</p>

</div>
</div>
@endsection
