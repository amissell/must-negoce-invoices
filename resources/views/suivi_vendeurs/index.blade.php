@extends('layouts.app')
@section('title', 'Dashboard Suivi Vendeurs')

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
        background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
        border-radius: 24px; position: relative; overflow: hidden;
    }
    .page-hero::before {
        content: ''; position: absolute; top: -50%; right: -20%;
        width: 600px; height: 600px;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
        animation: float 20s ease-in-out infinite;
    }
    @keyframes float {
        0%, 100% { transform: translate(0,0) rotate(0deg); }
        50% { transform: translate(-30px,30px) rotate(180deg); }
    }

    .filter-bar {
        background: white; border-radius: 20px; padding: 20px 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.07); border: 1px solid rgba(0,0,0,0.05);
        display: flex; align-items: center; flex-wrap: wrap; gap: 12px;
    }
    .filter-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #94A3B8; margin-bottom: 4px; }
    .filter-select, .filter-input {
        padding: 9px 14px; border: 2px solid #E2E8F0; border-radius: 12px;
        font-size: 13px; font-weight: 600; color: #1E293B; background: #F8FAFC;
        transition: all 0.2s; outline: none; font-family: 'Plus Jakarta Sans', sans-serif; min-width: 140px;
    }
    .filter-select:focus, .filter-input:focus {
        border-color: #4F46E5; background: white; box-shadow: 0 0 0 3px rgba(79,70,229,0.10);
    }
    .filter-active-tag {
        display: inline-flex; align-items: center; gap: 6px;
        background: #EEF2FF; color: #4338CA; padding: 5px 12px; border-radius: 999px;
        font-size: 12px; font-weight: 700; border: 1px solid #C7D2FE;
    }
    .filter-active-tag button { background: none; border: none; cursor: pointer; color: #6366F1; font-size: 14px; line-height: 1; padding: 0; margin: 0; }
    .filter-active-tag button:hover { color: #EF4444; }

    .btn-filter-apply {
        display: inline-flex; align-items: center; gap: 6px; padding: 9px 20px;
        background: linear-gradient(135deg, #4F46E5, #7C3AED); color: white;
        border: none; border-radius: 12px; font-weight: 700; font-size: 13px; cursor: pointer;
        transition: all 0.2s; box-shadow: 0 3px 10px rgba(79,70,229,0.3);
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .btn-filter-apply:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(79,70,229,0.35); }

    .btn-filter-reset {
        display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px;
        background: #F1F5F9; color: #64748B; border: none; border-radius: 12px;
        font-weight: 700; font-size: 13px; cursor: pointer; transition: all 0.2s;
        font-family: 'Plus Jakarta Sans', sans-serif; text-decoration: none;
    }
    .btn-filter-reset:hover { background: #E2E8F0; color: #475569; }

    .kpi-card {
        background: white; border-radius: 20px; padding: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
        position: relative; overflow: hidden; border: 1px solid rgba(0,0,0,0.05);
    }
    .kpi-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; }
    .kpi-card:hover { transform: translateY(-6px); box-shadow: 0 12px 32px rgba(0,0,0,0.12); }
    .kpi-card.blue::before   { background: linear-gradient(90deg, #4F46E5, #6366F1); }
    .kpi-card.green::before  { background: linear-gradient(90deg, #10B981, #34D399); }
    .kpi-card.red::before    { background: linear-gradient(90deg, #EF4444, #F87171); }
    .kpi-card.purple::before { background: linear-gradient(90deg, #8B5CF6, #A78BFA); }
    .kpi-card.yellow::before { background: linear-gradient(90deg, #F59E0B, #FCD34D); }
    .kpi-card.teal::before   { background: linear-gradient(90deg, #14B8A6, #2DD4BF); }
    .kpi-icon  { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 12px; }
    .kpi-value { font-size: 26px; font-weight: 800; line-height: 1.1; margin-bottom: 6px; }
    .kpi-label { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #64748B; }

    .vendor-card {
        background: white; border-radius: 20px; padding: 20px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08); transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
        border: 1px solid rgba(0,0,0,0.05); position: relative; overflow: hidden;
    }
    .vendor-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 6px; }
    .vendor-card.blue::before   { background: linear-gradient(90deg, #4F46E5, #6366F1); }
    .vendor-card.orange::before { background: linear-gradient(90deg, #F97316, #FB923C); }
    .vendor-card.green::before  { background: linear-gradient(90deg, #10B981, #34D399); }
    .vendor-card.red::before    { background: linear-gradient(90deg, #EF4444, #F87171); }
    .vendor-card.purple::before { background: linear-gradient(90deg, #8B5CF6, #A78BFA); }
    .vendor-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,0.12); }
    .vendor-badge { width: 52px; height: 52px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px; color: white; margin-bottom: 12px; }

    .vendor-toggle-btn {
        width: 100%; display: flex; align-items: center; justify-content: space-between;
        background: none; border: none; cursor: pointer; padding: 0 0 8px 0;
        font-size: 11px; font-weight: 700; color: #94A3B8;
        transition: color 0.2s; font-family: 'Plus Jakarta Sans', sans-serif;
        border-bottom: 1px solid #F1F5F9; margin-bottom: 10px;
    }
    .vendor-toggle-btn:hover { color: #475569; }
    .vendor-toggle-btn .toggle-icon {
        width: 16px; height: 16px;
        transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
    }
    .vendor-toggle-btn.collapsed .toggle-icon { transform: rotate(180deg); }
    .vendor-toggle-btn.collapsed { color: #CBD5E1; border-bottom-color: transparent; margin-bottom: 0; }

    .vendor-card-body {
        max-height: 600px;
        opacity: 1;
        overflow: hidden;
        transition: max-height 0.38s cubic-bezier(0.4,0,0.2,1), opacity 0.28s ease;
    }
    .vendor-card-body.collapsed {
        max-height: 0;
        opacity: 0;
    }

    .drops-mini {
        margin-top: 12px; background: linear-gradient(135deg, #FFF7ED 0%, #FFFBEB 100%);
        border: 1.5px solid #FED7AA; border-radius: 12px; padding: 10px 14px;
        display: flex; align-items: center; justify-content: space-between;
    }
    .drops-mini-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #92400E; }
    .drops-mini-value { font-size: 15px; font-weight: 800; color: #C2410C; }

    .progress-ring { height: 8px; background: #F1F5F9; border-radius: 999px; overflow: hidden; margin-top: 12px; }
    .progress-fill { height: 100%; border-radius: 999px; transition: width 1.2s cubic-bezier(0.4,0,0.2,1); }

    .table-card { background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05); }
    .table-header { background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%); padding: 20px 28px; color: white; }
    .dashboard-table thead th { background: #F8FAFC; color: #475569; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 16px 20px; border-bottom: 2px solid #E2E8F0; }
    .dashboard-table tbody td { padding: 16px 20px; border-bottom: 1px solid #F1F5F9; vertical-align: middle; }
    .dashboard-table tbody tr:hover { background: #F8FAFC; }

    @keyframes fadeInUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
    .fade-in-up { animation: fadeInUp 0.5s ease-out backwards; }

    .badge-rank  { width: 32px; height: 32px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; }
    .rank-1      { background: linear-gradient(135deg, #F59E0B 0%, #FCD34D 100%); color: white; }
    .rank-2      { background: linear-gradient(135deg, #94A3B8 0%, #CBD5E1 100%); color: white; }
    .rank-3      { background: linear-gradient(135deg, #F97316 0%, #FB923C 100%); color: white; }
    .rank-other  { background: #F1F5F9; color: #64748B; }

    .badge-ok        { background: #DCFCE7; color: #15803D; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 800; }
    .badge-attention { background: #FEF9C3; color: #A16207; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 800; }
    .badge-critique  { background: #FEE2E2; color: #B91C1C; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 800; }
    .badge-drops     { background: #FFF7ED; color: #C2410C; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 800; border: 1px solid #FED7AA; white-space: nowrap; }
    .badge-repos     { background: #FFF7ED; color: #EA580C; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 700; }

    .row-highlight { background: #FFF9C4 !important; }

    .btn-voir-details {
        display: block; margin-top: 10px; padding: 8px;
        background: #EEF2FF; color: #4338CA;
        border: 1.5px solid #C7D2FE; border-radius: 10px;
        font-weight: 700; font-size: 12px; text-decoration: none;
        text-align: center; transition: all 0.2s;
    }
    .btn-voir-details:hover {
        background: #4F46E5; color: white; border-color: #4F46E5;
        transform: translateY(-1px);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen" style="background: linear-gradient(to bottom, #F8FAFC 0%, #EFF6FF 100%);">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <div class="page-hero p-8 fade-in-up">
        <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-content-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-extrabold text-white">DASHBOARD SUIVI VENDEURS</h1>
                </div>
                <p class="text-indigo-200 text-sm font-medium">
                    {{ \Carbon\Carbon::create($annee ?? now()->year, $mois ?? now()->month)->translatedFormat('F Y') }}
                    &nbsp;·&nbsp; {{ $totalJoursOuvres }} jours ouvrés
                    @if(request('vendeur'))
                        &nbsp;·&nbsp;
                        <span class="bg-white/20 px-2 py-0.5 rounded-lg text-white font-bold">
                            👤 {{ request('vendeur') }}
                        </span>
                    @endif
                </p>
            </div>

            @if(auth()->user()->isAdmin())
            <a href="{{ route('suivi-vendeurs.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-white hover:bg-white/90 text-indigo-600 font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 text-sm">
                ➕ <span>Nouvelle Saisie</span>
            </a>
            @endif
        </div>
    </div>

    <div class="filter-bar fade-in-up" style="animation-delay:0.05s">
        <form method="GET" id="filterForm" class="flex flex-wrap items-end gap-3 w-full">

            <div>
                <div class="filter-label">📅 Mois</div>
                <select name="mois" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" @selected(($mois ?? now()->month) == $m)>
                            {{ \Carbon\Carbon::create(null,$m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <div class="filter-label">🗓 Année</div>
                <select name="annee" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                    @foreach([2024,2025,2026] as $y)
                        <option value="{{ $y }}" @selected(($annee ?? now()->year) == $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <div class="filter-label">👤 Vendeur</div>
                <select name="vendeur" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">— Tous les vendeurs —</option>
                    @foreach($vendeurs as $v)
                        <option value="{{ $v }}" @selected(request('vendeur') == $v)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <div class="filter-label">📊 Statut</div>
                <select name="statut" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">— Tous —</option>
                    <option value="actif" @selected(request('statut') == 'actif')>✅ Actif seulement</option>
                    <option value="repos" @selected(request('statut') == 'repos')>🏖 Repos seulement</option>
                </select>
            </div>

            <div class="flex-1" style="min-width:160px">
                <div class="filter-label">🔎 Recherche</div>
                <input type="text" name="q" id="tableSearch"
                       value="{{ request('q') }}"
                       placeholder="Nom vendeur..."
                       class="filter-input w-full"
                       autocomplete="off">
            </div>

            <div class="flex gap-2 items-end">
                <button type="submit" class="btn-filter-apply">🔍 Filtrer</button>
                @if(request()->hasAny(['vendeur','statut','q']) || (request('mois') && request('mois') != now()->month) || (request('annee') && request('annee') != now()->year))
                <a href="{{ route('suivi-vendeurs.index') }}" class="btn-filter-reset">✕ Réinitialiser</a>
                @endif
            </div>
        </form>

        @if(request()->hasAny(['vendeur','statut','q']))
        <div class="w-full flex flex-wrap gap-2 pt-2 border-t border-gray-100 mt-1">
            <span class="text-xs text-gray-400 font-semibold self-center">Filtres actifs :</span>
            @if(request('vendeur'))
                <span class="filter-active-tag">👤 {{ request('vendeur') }}<button onclick="removeFilter('vendeur')">×</button></span>
            @endif
            @if(request('statut'))
                <span class="filter-active-tag">📊 {{ request('statut') === 'actif' ? 'Actif seulement' : 'Repos seulement' }}<button onclick="removeFilter('statut')">×</button></span>
            @endif
            @if(request('q'))
                <span class="filter-active-tag">🔎 "{{ request('q') }}"<button onclick="removeFilter('q')">×</button></span>
            @endif
        </div>
        @endif
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        @php
            $kpis = [
                ['icon' => '💰', 'label' => 'CA Total',      'value' => number_format($statsGlobales['ca_total'] ?? 0, 0, ',', ' ') . ' DH',    'color' => 'blue',   'bg' => 'bg-indigo-50',  'text' => 'text-indigo-600'],
                ['icon' => '✅', 'label' => 'Encaissé',      'value' => number_format($statsGlobales['encaisse'] ?? 0, 0, ',', ' ') . ' DH',    'color' => 'green',  'bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
                ['icon' => '⚠️', 'label' => 'Reste à Payer', 'value' => number_format($statsGlobales['reste'] ?? 0, 0, ',', ' ') . ' DH',       'color' => 'red',    'bg' => 'bg-red-50',     'text' => 'text-red-600'],
                ['icon' => '📦', 'label' => 'BL Total',      'value' => number_format($statsGlobales['bl_total'] ?? 0, 0, ',', ' '),             'color' => 'purple', 'bg' => 'bg-purple-50',  'text' => 'text-purple-600'],
                ['icon' => '📈', 'label' => 'Taux Global',   'value' => ($statsGlobales['taux_global'] ?? 0) . '%',                                'color' => 'yellow', 'bg' => 'bg-amber-50',   'text' => 'text-amber-600'],
                ['icon' => '📉', 'label' => 'Drops Global',  'value' => number_format($statsGlobales['drops_global'] ?? 0, 0, ',', ' ') . ' DH/BL', 'color' => 'teal', 'bg' => 'bg-teal-50', 'text' => 'text-teal-600'],
            ];
        @endphp
        @foreach($kpis as $i => $kpi)
        <div class="kpi-card {{ $kpi['color'] }} fade-in-up" style="animation-delay: {{ $i * 0.05 }}s">
            <div class="kpi-icon {{ $kpi['bg'] }}">{{ $kpi['icon'] }}</div>
            <div class="kpi-value {{ $kpi['text'] }}">{{ $kpi['value'] }}</div>
            <div class="kpi-label">{{ $kpi['label'] }}</div>
        </div>
        @endforeach
    </div>

    <div class="table-card fade-in-up" style="animation-delay: 0.18s">
        <div class="table-header flex items-center justify-between flex-wrap gap-2">
            <h2 class="text-lg font-extrabold flex items-center gap-2">⏱️ INACTIVITÉ PAR VENDEUR — limite 26 jours</h2>
            <span class="text-sm bg-white/20 rounded-lg px-3 py-1 font-semibold">
                🎯 Objectif: {{ $joursAttendus }}j &nbsp;·&nbsp; Disponibles: {{ $totalJoursOuvres }}j
            </span>
        </div>

        @php $nbCritiques = $inactiviteParVendeur->where('statut','critique')->count(); @endphp
        @if($nbCritiques > 0)
        <div class="mx-6 mt-4 flex items-start gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-3">
            <span class="text-red-500 text-lg mt-0.5">🔴</span>
            <p class="text-sm text-red-700 font-medium">
                <strong>{{ $nbCritiques }} vendeur{{ $nbCritiques > 1 ? 's' : '' }}</strong> dépassent significativement la limite de 26 jours.
                @php $pire = $inactiviteParVendeur->first(); @endphp
                @if($pire)
                    Le plus inactif : <strong>{{ $inactiviteParVendeur->keys()->first() }}</strong>
                    avec <strong>+{{ $pire['jours_au_dela'] }} jours</strong> au-delà.
                @endif
            </p>
        </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full dashboard-table">
                <thead>
                    <tr>
                        <th class="text-left">#</th>
                        <th class="text-left">VENDEUR</th>
                        <th class="text-center">Jours actifs</th>
                        <th class="text-center">Absences</th>
                        <th class="text-center">Au-delà 26j</th>
                        <th class="text-center">Statut</th>
                    </tr>
                </thead>
               <tbody>
    @forelse($inactiviteParVendeur as $inf)
    @php $statut = $inf['statut']; @endphp
    <tr class="{{ request('vendeur') == $inf['vendeur_nom'] ? 'row-highlight' : '' }}">
        <td class="text-gray-400 font-bold text-sm">{{ $loop->iteration }}</td>
        <td>
            <button onclick="filterByVendeur('{{ $inf['vendeur_nom'] }}')"
                    class="font-bold text-gray-900 hover:text-indigo-600 transition-colors bg-transparent border-0 cursor-pointer p-0 text-left">
                {{ $inf['vendeur_nom'] }}
            </button>
        </td>
        <td class="text-center font-semibold text-indigo-600">{{ $inf['jours_actifs'] }}</td>
        <td class="text-center font-semibold {{ $inf['jours_absents'] > 3 ? 'text-red-600' : 'text-gray-600' }}">
            {{ $inf['jours_absents'] }}
        </td>
        <td class="text-center">
            @if($inf['jours_au_dela'] > 0)
                <span class="badge-{{ $statut }}">+{{ $inf['jours_au_dela'] }} j</span>
            @else
                <span class="badge-ok">✓ OK</span>
            @endif
        </td>
        <td class="text-center text-xl">
            @if($statut === 'critique') 🔴
            @elseif($statut === 'attention') 🟡
            @else 🟢
            @endif
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="6" class="text-center text-gray-400 py-6">
            Aucune donnée d'inactivité pour cette période.
        </td>
    </tr>
    @endforelse
</tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mt-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold">🎯 Objectifs Mensuels</h3>

        @if(auth()->user()->isAdmin())
            <a href="{{ route('objectifs.index', ['mois' => $mois, 'annee' => $annee]) }}"
               class="text-indigo-600 text-sm hover:underline">
                Gérer les objectifs →
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($objectifsVendeurs as $nom => $obj)
            <div class="border rounded-xl p-4 {{ $obj['atteindra'] ? 'border-green-200 bg-green-50/30' : 'border-red-200 bg-red-50/30' }}">
                <div class="flex justify-between items-center mb-2">
                    <span class="font-bold text-gray-800">{{ $nom }}</span>

                    <span class="text-xs px-2 py-1 rounded-full font-semibold
                        {{ $obj['atteindra'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $obj['atteindra'] ? '✅ En bonne voie' : '⚠️ Attention' }}
                    </span>
                </div>

                {{-- Barre de progression --}}
                <div class="w-full bg-gray-200 rounded-full h-3 mb-2 shadow-inner">
                    <div class="h-3 rounded-full transition-all duration-500
                        {{ $obj['progression'] >= 80 ? 'bg-green-500' : ($obj['progression'] >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}"
                        style="width: {{ min($obj['progression'], 100) }}%; transition: width 1s ease;">
                    </div>
                </div>

                <div class="flex justify-between text-xs text-gray-500 mb-3">
                    <span>{{ number_format($obj['ca_actuel'], 0, ',', ' ') }} MAD</span>
                    <span class="font-bold">{{ $obj['progression'] }}%</span>
                    <span>{{ number_format($obj['objectif_ca'], 0, ',', ' ') }} MAD</span>
                </div>

                {{-- Détail --}}
                <div class="text-xs space-y-1 border-t pt-2 text-gray-600">
                    <div class="flex justify-between">
                        <span>🎯 Objectif CA:</span>
                        <span>{{ number_format($obj['objectif_ca'], 0, ',', ' ') }} MAD</span>
                    </div>

                    <div class="flex justify-between">
                        <span>📦 Objectif BL:</span>
                        <span>{{ number_format($obj['objectif_bl'], 0, ',', ' ') }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>📦 BL actuel:</span>
                        <span>{{ number_format($obj['bl_actuel'], 0, ',', ' ') }}</span>
                    </div>

                    <div class="flex justify-between font-semibold">
                        <span>💰 Prime:</span>
                        <span class="text-green-600">{{ number_format($obj['prime'], 0, ',', ' ') }} MAD</span>
                    </div>
                </div>

                {{-- Prédiction --}}
                <div class="mt-2 text-xs {{ $obj['atteindra'] ? 'text-green-600' : 'text-red-600' }} font-semibold">
                    📈 Prédiction fin de mois: {{ number_format($obj['prediction'], 0, ',', ' ') }} MAD
                </div>
            </div>
        @empty
            <div class="col-span-full text-center text-gray-400 py-6">
                Aucun objectif défini pour ce mois.

                @if(auth()->user()->isAdmin())
                    <a href="{{ route('objectifs.create') }}" class="text-indigo-600 hover:underline ml-1">
                        Créer un objectif
                    </a>
                @endif
            </div>
        @endforelse
    </div>
</div>

    @php
        $vColors = [
            ['class' => 'blue',   'gradient' => 'gradient-blue',   'bg' => 'bg-indigo-50',  'text' => 'text-indigo-600',  'fill' => 'from-indigo-500 to-indigo-400'],
            ['class' => 'orange', 'gradient' => 'gradient-orange', 'bg' => 'bg-orange-50',  'text' => 'text-orange-600',  'fill' => 'from-orange-500 to-orange-400'],
            ['class' => 'green',  'gradient' => 'gradient-green',  'bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'fill' => 'from-emerald-500 to-emerald-400'],
            ['class' => 'red',    'gradient' => 'gradient-red',    'bg' => 'bg-red-50',     'text' => 'text-red-600',     'fill' => 'from-red-500 to-red-400'],
            ['class' => 'purple', 'gradient' => 'gradient-purple', 'bg' => 'bg-purple-50',  'text' => 'text-purple-600',  'fill' => 'from-purple-500 to-purple-400'],
        ];
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        @foreach(($suiviParVendeur ?? collect()) as $nom => $stats)
        @php
            $i = $loop->index;
            $c = $vColors[$i % 5];
            $taux = $stats['taux'] ?? 0;
            $inf = $inactiviteParVendeur[$nom] ?? null;
            $isSelected = request('vendeur') == $nom;
        @endphp
        <div class="vendor-card {{ $c['class'] }} fade-in-up {{ $isSelected ? 'ring-2 ring-indigo-400 ring-offset-2' : '' }}"
             style="animation-delay: {{ 0.2 + $i * 0.06 }}s">

            <button class="vendor-toggle-btn" onclick="toggleVendorCard(this)" title="Masquer / Afficher">
                <span class="toggle-label flex items-center gap-1">
                    <svg class="toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                    </svg>
                    <span class="toggle-text">Masquer</span>
                </span>
                <span class="font-extrabold {{ $c['text'] }} text-sm">{{ $taux }}%</span>
            </button>

            <div class="vendor-card-body">
                <div style="cursor:pointer" onclick="filterByVendeur('{{ $nom }}')">
                    <div class="vendor-badge {{ $c['gradient'] }}">{{ strtoupper(substr($nom, 0, 2)) }}</div>
                    <h3 class="font-bold text-gray-900 text-base mb-1">{{ $nom }}</h3>
                    <p class="text-xs text-gray-500 font-semibold mb-1">{{ $stats['bl'] ?? 0 }} BL</p>

                    @if($inf)
                        @if($inf['jours_au_dela'] > 0)
                            <span class="badge-{{ $inf['statut'] }} text-xs mb-3 inline-block">+{{ $inf['jours_au_dela'] }}j inactif</span>
                        @else
                            <span class="badge-ok text-xs mb-3 inline-block">✓ Actif</span>
                        @endif
                    @endif

                    <div class="space-y-2 text-sm mt-2">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">CA Total</span>
                            <span class="font-bold {{ $c['text'] }}">{{ number_format($stats['ca_total'] ?? 0, 0, ',', ' ') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">Encaissé</span>
                            <span class="font-bold text-emerald-600">{{ number_format($stats['encaisse'] ?? 0, 0, ',', ' ') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">Reste</span>
                            <span class="font-bold {{ ($stats['reste'] ?? 0) > 5000 ? 'text-red-600' : 'text-gray-600' }}">
                                {{ number_format($stats['reste'] ?? 0, 0, ',', ' ') }}
                            </span>
                        </div>
                    </div>

                    <div class="drops-mini">
                        <span class="drops-mini-label">📉 Drops</span>
                        <span class="drops-mini-value">
                            {{ ($stats['bl'] ?? 0) > 0 ? number_format($stats['drops'] ?? 0, 0, ',', ' ') . ' DH/BL' : '—' }}
                        </span>
                    </div>

                    <div class="progress-ring">
                        <div class="progress-fill bg-gradient-to-r {{ $c['fill'] }}" style="width: {{ min($taux, 100) }}%"></div>
                    </div>

                    <div class="text-center mt-2">
                        <span class="font-extrabold {{ $c['text'] }} text-lg">{{ $taux }}%</span>
                    </div>

                    @if(isset($stats['meilleur_jour']) && $stats['meilleur_jour'])
                    <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-500">
                        🏆 Meilleur: {{ \Carbon\Carbon::parse($stats['meilleur_jour']->date)->format('d/m') }}
                        — <span class="font-bold">{{ number_format($stats['meilleur_jour']->chiffre, 0, ',', ' ') }}</span>
                    </div>
                    @endif
                </div>

                <a href="{{ route('suivi-vendeurs.show', ['nom' => $nom]) }}" class="btn-voir-details">
                    📊 Voir détails
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <div class="table-card fade-in-up" style="animation-delay: 0.5s">
        <div class="table-header">
            <h2 class="text-lg font-extrabold flex items-center gap-2">🏆 MEILLEUR & PIRE JOUR PAR VENDEUR</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full dashboard-table">
                <thead>
                    <tr>
                        <th class="text-left">VENDEUR</th>
                        <th class="text-center">MEILLEUR JOUR</th>
                        <th class="text-center">CA MAX</th>
                        <th class="text-center">PIRE JOUR</th>
                        <th class="text-center">CA MIN</th>
                        <th class="text-center">TAUX %</th>
                        <th class="text-center">📉 DROPS</th>
                        <th class="text-center">RANK</th>
                        <th class="text-center">DÉTAILS</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rank = 1; @endphp
                    @foreach(($suiviParVendeur ?? collect())->sortByDesc('taux') as $nom => $stats)
                    <tr class="{{ request('vendeur') == $nom ? 'row-highlight' : '' }}">
                        <td>
                            <div class="flex items-center gap-3">
                                @php $c = $vColors[array_search($nom, ($suiviParVendeur ?? collect())->keys()->toArray()) % 5]; @endphp
                                <div class="w-9 h-9 {{ $c['gradient'] }} rounded-lg flex items-center justify-center text-white font-bold text-xs">
                                    {{ strtoupper(substr($nom, 0, 2)) }}
                                </div>
                                <button onclick="filterByVendeur('{{ $nom }}')"
                                        class="font-bold text-gray-900 hover:text-indigo-600 transition-colors bg-transparent border-0 cursor-pointer p-0">
                                    {{ $nom }}
                                </button>
                            </div>
                        </td>
                        <td class="text-center text-gray-600 font-semibold">
                            {{ isset($stats['meilleur_jour']) ? \Carbon\Carbon::parse($stats['meilleur_jour']->date)->format('d/m') : '—' }}
                        </td>
                        <td class="text-center font-bold text-emerald-600">
                            {{ isset($stats['meilleur_jour']) ? number_format($stats['meilleur_jour']->chiffre, 0, ',', ' ') . ' DH' : '—' }}
                        </td>
                        <td class="text-center text-gray-600 font-semibold">
                            {{ isset($stats['pire_jour']) ? \Carbon\Carbon::parse($stats['pire_jour']->date)->format('d/m') : '—' }}
                        </td>
                        <td class="text-center font-bold text-red-600">
                            {{ isset($stats['pire_jour']) ? number_format($stats['pire_jour']->chiffre, 0, ',', ' ') . ' DH' : '—' }}
                        </td>
                        <td class="text-center">
                            @php
                                $t = $stats['taux'] ?? 0;
                                $bc = $t >= 80 ? 'bg-emerald-100 text-emerald-700' : ($t >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700');
                            @endphp
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-extrabold {{ $bc }}">{{ $t }}%</span>
                        </td>
                        <td class="text-center">
                            @if(($stats['bl'] ?? 0) > 0)
                                <span class="badge-drops">{{ number_format($stats['drops'] ?? 0, 0, ',', ' ') }} DH/BL</span>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php $rankClass = $rank == 1 ? 'rank-1' : ($rank == 2 ? 'rank-2' : ($rank == 3 ? 'rank-3' : 'rank-other')); @endphp
                            <span class="badge-rank {{ $rankClass }}">{{ $rank++ }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('suivi-vendeurs.show', ['nom' => $nom]) }}"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all">
                                📊 Voir détails
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 fade-in-up" style="animation-delay: 0.6s">
        <div class="table-card">
            <div class="p-6">
                <h3 class="font-bold text-gray-900 mb-4">📊 CA vs Encaissé vs Reste par Vendeur</h3>
                <canvas id="barChart" height="220"></canvas>
            </div>
        </div>
        <div class="table-card">
            <div class="p-6">
                <h3 class="font-bold text-gray-900 mb-4">🥧 Répartition CA par Vendeur</h3>
                <canvas id="pieChart" height="220"></canvas>
            </div>
        </div>
    </div>

    <div class="table-card fade-in-up" style="animation-delay: 0.65s">
        <div class="p-6">
            <h3 class="font-bold text-gray-900 mb-1">📉 DROPS par Vendeur</h3>
            <p class="text-xs text-gray-400 mb-4">CA Total ÷ Nombre de BL — CA moyen généré par bon de livraison</p>
            <canvas id="dropsChart" height="140"></canvas>
        </div>
    </div>

    <div class="table-card fade-in-up" style="animation-delay: 0.7s">
        <div class="p-6">
            <h3 class="font-bold text-gray-900 mb-4">📈 Évolution CA par Jour & Vendeur</h3>                          
            <canvas id="lineChart" height="280"></canvas>
        </div>
    </div>

    <div class="table-card fade-in-up" style="animation-delay: 0.8s">
        <div class="table-header flex items-center justify-between flex-wrap gap-3">
            <h2 class="text-lg font-extrabold flex items-center gap-2">📋 Saisies
                @if(request('vendeur'))— <span class="text-indigo-200">{{ request('vendeur') }}</span>@endif
            </h2>
            <div class="flex items-center gap-3">
                <input type="text" id="liveSearch" placeholder="🔎 Chercher dans la liste..."
                       class="text-sm px-4 py-2 rounded-xl border-2 border-white/30 bg-white/20
                              text-white placeholder-white/60 font-semibold focus:outline-none
                              focus:border-white/60 backdrop-blur-sm" style="min-width:200px">
                <span class="text-sm bg-white/20 rounded-lg px-3 py-1 font-semibold">
                    {{ isset($lignes) ? $lignes->total() : 0 }} entrées
                </span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full dashboard-table" id="saisiesTable">
                <thead>
                    <tr>
                        <th class="text-left" style="cursor:pointer" onclick="sortTable(0)">DATE ↕</th>
                        <th class="text-left" style="cursor:pointer" onclick="sortTable(1)">VENDEUR ↕</th>
                        <th class="text-right" style="cursor:pointer" onclick="sortTable(2)">CHIFFRE ↕</th>
                        <th class="text-right">PAYÉ</th>
                        <th class="text-right">RESTE</th>
                        <th class="text-center">BL</th>
                        <th class="text-center">📉 DROPS</th>
                        <th class="text-center">HEURES</th>
                        @if(auth()->user()->isAdmin())
                        <th class="text-center">ACTIONS</th>
                        @endif
                    </tr>
                </thead>
                <tbody id="saisiesBody">
                    @forelse(($lignes ?? collect()) as $ligne)
                    @php
                        $reste = $ligne->chiffre - $ligne->total_paye;
                        $dropsLigne = $ligne->bl > 0 ? round($ligne->chiffre / $ligne->bl, 0) : 0;
                        $vIdx = array_search($ligne->vendeur_nom, ($suiviParVendeur ?? collect())->keys()->toArray());
                        $c = $vColors[($vIdx !== false ? $vIdx : 0) % 5] ?? $vColors[0];
                        $showRow = true;
                        if (request('statut') === 'actif' && $ligne->jour_repos)  $showRow = false;
                        if (request('statut') === 'repos' && !$ligne->jour_repos) $showRow = false;
                    @endphp
                    @if($showRow)
                    <tr class="{{ $ligne->jour_repos ? 'opacity-50' : '' }} searchable-row"
                        data-vendeur="{{ strtolower($ligne->vendeur_nom) }}"
                        data-date="{{ $ligne->date }}">
                        <td class="font-semibold text-gray-700">
                            {{ \Carbon\Carbon::parse($ligne->date)->format('d/m/Y') }}
                            @if($ligne->jour_repos)<span class="badge-repos ml-1">🏖 Repos</span>@endif
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 {{ $c['gradient'] }} rounded-lg flex items-center justify-center text-white font-bold text-xs">
                                    {{ strtoupper(substr($ligne->vendeur_nom, 0, 2)) }}
                                </div>
                                <button onclick="filterByVendeur('{{ $ligne->vendeur_nom }}')"
                                        class="font-semibold text-gray-900 hover:text-indigo-600 bg-transparent border-0 cursor-pointer p-0">
                                    {{ $ligne->vendeur_nom }}
                                </button>
                            </div>
                        </td>
                        <td class="text-right font-bold text-indigo-600">{{ number_format($ligne->chiffre, 0, ',', ' ') }} DH</td>
                        <td class="text-right font-bold text-emerald-600">{{ number_format($ligne->total_paye, 0, ',', ' ') }} DH</td>
                        <td class="text-right font-bold {{ $reste > 5000 ? 'text-red-600' : 'text-gray-600' }}">{{ number_format($reste, 0, ',', ' ') }} DH</td>
                        <td class="text-center">
                            <span class="inline-block bg-purple-100 text-purple-700 rounded-lg px-2 py-0.5 text-xs font-bold">{{ $ligne->bl }}</span>
                        </td>
                        <td class="text-center">
                            @if($dropsLigne > 0)
                                <span class="badge-drops">{{ number_format($dropsLigne, 0, ',', ' ') }}</span>
                            @else
                                <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="text-center text-gray-500 text-sm">
                            {{ $ligne->heure_debut ? \Carbon\Carbon::parse($ligne->heure_debut)->format('H:i') : '—' }}
                            –
                            {{ $ligne->heure_fin ? \Carbon\Carbon::parse($ligne->heure_fin)->format('H:i') : '—' }}
                        </td>

                        @if(auth()->user()->isAdmin())
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('suivi-vendeurs.edit', $ligne->id) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold
                                          bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all">
                                    ✏️ Éditer
                                </a>
                                <form method="POST"
                                      action="{{ route('suivi-vendeurs.destroy', $ligne->id) }}"
                                      onsubmit="return confirm('Supprimer cette entrée ?')"
                                      style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs
                                                   font-bold bg-red-50 text-red-600 hover:bg-red-100
                                                   transition-all border-0 cursor-pointer">
                                        🗑️ Supprimer
                                    </button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isAdmin() ? 9 : 8 }}" class="text-center py-8 text-gray-400">
                            Aucune saisie ce mois-ci
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($lignes) && $lignes->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $lignes->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function toggleVendorCard(btn) {
    const body = btn.closest('.vendor-card').querySelector('.vendor-card-body');
    const text = btn.querySelector('.toggle-text');
    const isCollapsed = body.classList.contains('collapsed');

    if (isCollapsed) {
        body.classList.remove('collapsed');
        btn.classList.remove('collapsed');
        text.textContent = 'Masquer';
    } else {
        body.classList.add('collapsed');
        btn.classList.add('collapsed');
        text.textContent = 'Afficher';
    }
}

function filterByVendeur(nom) {
    const url = new URL(window.location.href);
    if (url.searchParams.get('vendeur') === nom) {
        url.searchParams.delete('vendeur');
    } else {
        url.searchParams.set('vendeur', nom);
    }
    window.location = url.toString();
}

function removeFilter(key) {
    const url = new URL(window.location.href);
    url.searchParams.delete(key);
    window.location = url.toString();
}

document.getElementById('liveSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.searchable-row').forEach(row => {
        const vendeur = row.dataset.vendeur || '';
        const date = row.dataset.date || '';
        row.style.display = (!q || vendeur.includes(q) || date.includes(q)) ? '' : 'none';
    });
});

let sortDir = {};
function sortTable(colIndex) {
    const body = document.getElementById('saisiesBody');
    const rows = Array.from(body.querySelectorAll('tr.searchable-row'));
    sortDir[colIndex] = !sortDir[colIndex];
    rows.sort((a, b) => {
        const aText = a.cells[colIndex]?.innerText.trim().replace(/[^\d.-]/g, '') || '';
        const bText = b.cells[colIndex]?.innerText.trim().replace(/[^\d.-]/g, '') || '';
        const aVal = isNaN(aText) ? a.cells[colIndex]?.innerText.trim() : parseFloat(aText);
        const bVal = isNaN(bText) ? b.cells[colIndex]?.innerText.trim() : parseFloat(bText);
        if (aVal < bVal) return sortDir[colIndex] ? -1 : 1;
        if (aVal > bVal) return sortDir[colIndex] ? 1 : -1;
        return 0;
    });
    rows.forEach(r => body.appendChild(r));
}

const COLORS = ['#4F46E5', '#F97316', '#10B981', '#EF4444', '#8B5CF6'];

const vendeurLabels = @json(($suiviParVendeur ?? collect())->keys());
const caData = @json(($suiviParVendeur ?? collect())->pluck('ca_total')->values());
const encaisseData = @json(($suiviParVendeur ?? collect())->pluck('encaisse')->values());
const resteData = @json(($suiviParVendeur ?? collect())->pluck('reste')->values());
const dropsData = @json(($suiviParVendeur ?? collect())->pluck('drops')->values());
const datesLabels = @json($chartDates ?? []);
const dailyCAData = @json($chartDataCA ?? []);

new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: vendeurLabels,
        datasets: [
            { label: 'CA Total', data: caData, backgroundColor: '#4F46E5', borderRadius: 8 },
            { label: 'Encaissé', data: encaisseData, backgroundColor: '#10B981', borderRadius: 8 },
            { label: 'Reste', data: resteData, backgroundColor: '#EF4444', borderRadius: 8 },
        ]
    },
    options: {
        responsive: true,
        onClick: (e, elements) => { if (elements.length) filterByVendeur(vendeurLabels[elements[0].index]); },
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 11, weight: 600 }, boxWidth: 12, padding: 12 } },
            tooltip: { callbacks: { footer: () => 'Cliquez pour filtrer ↓' } }
        },
        scales: {
            y: { beginAtZero: true, ticks: { callback: v => (v/1000).toFixed(0)+'k DH', font: { size: 11 } }, grid: { color: '#F1F5F9' } },
            x: { ticks: { font: { size: 11, weight: 600 } }, grid: { display: false } }
        }
    }
});

new Chart(document.getElementById('pieChart'), {
    type: 'doughnut',
    data: { labels: vendeurLabels, datasets: [{ data: caData, backgroundColor: COLORS, borderWidth: 3, borderColor: '#fff' }] },
    options: {
        responsive: true, cutout: '60%',
        onClick: (e, elements) => { if (elements.length) filterByVendeur(vendeurLabels[elements[0].index]); },
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 11, weight: 600 }, boxWidth: 12, padding: 12 } },
            tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed.toLocaleString('fr-MA')} DH`, footer: () => 'Cliquez pour filtrer ↓' } }
        }
    }
});

new Chart(document.getElementById('dropsChart'), {
    type: 'bar',
    data: { labels: vendeurLabels, datasets: [{ label: 'Drops (DH/BL)', data: dropsData, backgroundColor: COLORS.map(c => c + 'BB'), borderColor: COLORS, borderWidth: 2, borderRadius: 10 }] },
    options: {
        responsive: true, indexAxis: 'y',
        onClick: (e, elements) => { if (elements.length) filterByVendeur(vendeurLabels[elements[0].index]); },
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.x.toLocaleString('fr-MA')} DH par BL`, footer: () => 'Cliquez pour filtrer ↓' } }
        },
        scales: {
            x: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('fr-MA') + ' DH', font: { size: 11 } }, grid: { color: '#F1F5F9' } },
            y: { ticks: { font: { size: 12, weight: 700 } }, grid: { display: false } }
        }
    }
});

if (datesLabels.length > 0) {
    const lineDatasets = Object.keys(dailyCAData).map((vendeur, index) => ({
        label: vendeur,
        data: dailyCAData[vendeur],
        borderColor: COLORS[index % COLORS.length],
        backgroundColor: COLORS[index % COLORS.length] + '20',
        borderWidth: 2.5,
        tension: 0.4,
        pointRadius: 4,
        pointHoverRadius: 6,
        pointBackgroundColor: COLORS[index % COLORS.length],
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        fill: false,
    }));

    new Chart(document.getElementById('lineChart'), {
        type: 'line',
        data: { labels: datesLabels, datasets: lineDatasets },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 11, weight: 600 }, boxWidth: 12, padding: 14 },
                    onClick: (e, legendItem) => filterByVendeur(legendItem.text)
                },
                tooltip: { callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString('fr-MA')} DH` } }
            },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => (v/1000).toFixed(0)+'k DH', font: { size: 11 } }, grid: { color: '#F1F5F9' } },
                x: { ticks: { font: { size: 10, weight: 600 } }, grid: { display: false } }
            }
        }
    });
}
</script>
@endpush 