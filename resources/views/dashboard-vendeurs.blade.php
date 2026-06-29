@extends('layouts.app')
@section('title', 'Dashboard Vendeurs API')

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
    .filter-select:focus { border-color: #4F46E5; background: white; box-shadow: 0 0 0 3px rgba(79,70,229,0.10); }
    .btn-filter-apply {
        display: inline-flex; align-items: center; gap: 6px; padding: 9px 20px;
        background: linear-gradient(135deg, #4F46E5, #7C3AED); color: white; border: none;
        border-radius: 12px; font-weight: 700; font-size: 13px; cursor: pointer; transition: all 0.2s;
        box-shadow: 0 3px 10px rgba(79,70,229,0.3); font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .btn-filter-apply:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(79,70,229,0.35); }

    .kpi-card {
        background: white; border-radius: 20px; padding: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
        position: relative; overflow: hidden; border: 1px solid rgba(0,0,0,0.05);
    }
    .kpi-card::before { content:''; position:absolute; top:0; left:0; right:0; height:4px; }
    .kpi-card:hover { transform: translateY(-6px); box-shadow: 0 12px 32px rgba(0,0,0,0.12); }
    .kpi-card.blue::before   { background: linear-gradient(90deg,#4F46E5,#6366F1); }
    .kpi-card.green::before  { background: linear-gradient(90deg,#10B981,#34D399); }
    .kpi-card.red::before    { background: linear-gradient(90deg,#EF4444,#F87171); }
    .kpi-card.purple::before { background: linear-gradient(90deg,#8B5CF6,#A78BFA); }
    .kpi-card.yellow::before { background: linear-gradient(90deg,#F59E0B,#FCD34D); }
    .kpi-card.teal::before   { background: linear-gradient(90deg,#14B8A6,#2DD4BF); }
    .kpi-icon  { width:48px; height:48px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:20px; margin-bottom:12px; }
    .kpi-value { font-size:26px; font-weight:800; line-height:1.1; margin-bottom:6px; }
    .kpi-label { font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; color:#64748B; }

    .vendor-card {
        background: white; border-radius: 20px; padding: 20px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08); transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
        border: 1px solid rgba(0,0,0,0.05); position: relative; overflow: hidden;
    }
    .vendor-card::before { content:''; position:absolute; top:0; left:0; right:0; height:6px; }
    .vendor-card.blue::before   { background: linear-gradient(90deg,#4F46E5,#6366F1); }
    .vendor-card.orange::before { background: linear-gradient(90deg,#F97316,#FB923C); }
    .vendor-card.green::before  { background: linear-gradient(90deg,#10B981,#34D399); }
    .vendor-card.red::before    { background: linear-gradient(90deg,#EF4444,#F87171); }
    .vendor-card.purple::before { background: linear-gradient(90deg,#8B5CF6,#A78BFA); }
    .vendor-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,0.12); }

    .vendor-badge { width:52px; height:52px; border-radius:16px; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:16px; color:white; margin-bottom:12px; }

    .vendor-toggle-btn {
        width:100%; display:flex; align-items:center; justify-content:space-between;
        background:none; border:none; cursor:pointer; padding:0 0 8px 0;
        font-size:11px; font-weight:700; color:#94A3B8; transition:color 0.2s;
        font-family:'Plus Jakarta Sans',sans-serif; border-bottom:1px solid #F1F5F9; margin-bottom:10px;
    }
    .vendor-toggle-btn:hover { color: #475569; }
    .vendor-toggle-btn .toggle-icon { width:16px; height:16px; transition:transform 0.3s cubic-bezier(0.4,0,0.2,1); }
    .vendor-toggle-btn.collapsed .toggle-icon { transform: rotate(180deg); }
    .vendor-toggle-btn.collapsed { color:#CBD5E1; border-bottom-color:transparent; margin-bottom:0; }
    .vendor-card-body { max-height:600px; opacity:1; overflow:hidden; transition:max-height 0.38s cubic-bezier(0.4,0,0.2,1), opacity 0.28s ease; }
    .vendor-card-body.collapsed { max-height:0; opacity:0; }

    .drops-mini {
        margin-top:12px; background:linear-gradient(135deg,#FFF7ED,#FFFBEB);
        border:1.5px solid #FED7AA; border-radius:12px; padding:10px 14px;
        display:flex; align-items:center; justify-content:space-between;
    }
    .drops-mini-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#92400E; }
    .drops-mini-value { font-size:15px; font-weight:800; color:#C2410C; }

    .progress-ring  { height:8px; background:#F1F5F9; border-radius:999px; overflow:hidden; margin-top:12px; }
    .progress-fill  { height:100%; border-radius:999px; transition:width 1.2s cubic-bezier(0.4,0,0.2,1); }

    .table-card { background:white; border-radius:20px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08); border:1px solid rgba(0,0,0,0.05); }
    .table-header { background:linear-gradient(135deg,#4F46E5,#7C3AED); padding:20px 28px; color:white; }
    .dashboard-table thead th { background:#F8FAFC; color:#475569; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; padding:16px 20px; border-bottom:2px solid #E2E8F0; }
    .dashboard-table tbody td { padding:16px 20px; border-bottom:1px solid #F1F5F9; vertical-align:middle; }
    .dashboard-table tbody tr:hover { background:#F8FAFC; }

    @keyframes fadeInUp { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:translateY(0); } }
    .fade-in-up { animation: fadeInUp 0.5s ease-out backwards; }

    .badge-rank  { width:32px; height:32px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; font-weight:800; font-size:14px; }
    .rank-1      { background:linear-gradient(135deg,#F59E0B,#FCD34D); color:white; }
    .rank-2      { background:linear-gradient(135deg,#94A3B8,#CBD5E1); color:white; }
    .rank-3      { background:linear-gradient(135deg,#F97316,#FB923C); color:white; }
    .rank-other  { background:#F1F5F9; color:#64748B; }

    .badge-ok        { background:#DCFCE7; color:#15803D; padding:4px 12px; border-radius:999px; font-size:12px; font-weight:800; }
    .badge-attention { background:#FEF9C3; color:#A16207; padding:4px 12px; border-radius:999px; font-size:12px; font-weight:800; }
    .badge-critique  { background:#FEE2E2; color:#B91C1C; padding:4px 12px; border-radius:999px; font-size:12px; font-weight:800; }
    .badge-drops     { background:#FFF7ED; color:#C2410C; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:800; border:1px solid #FED7AA; white-space:nowrap; }
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
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-extrabold text-white">DASHBOARD SUIVI VENDEURS</h1>
                </div>
                <p class="text-indigo-200 text-sm font-medium">
                    @if($data)
                        {{ \Carbon\Carbon::create($annee, (int)$mois)->translatedFormat('F Y') }}
                        &nbsp;·&nbsp; {{ $data['disponibles'] }} jours ouvrés
                    @else
                        Données non disponibles
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- ══════════════ FILTRES ══════════════ --}}
    <div class="filter-bar fade-in-up" style="animation-delay:0.05s">
        <form method="GET" id="filterForm" action="{{ route('dashboard.vendeurs') }}" class="flex flex-wrap items-end gap-3 w-full">
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
        $vColors = [
            ['class'=>'blue',   'gradient'=>'gradient-blue',   'bg'=>'bg-indigo-50',  'text'=>'text-indigo-600',  'fill'=>'from-indigo-500 to-indigo-400',  'hex'=>'#4F46E5'],
            ['class'=>'orange', 'gradient'=>'gradient-orange', 'bg'=>'bg-orange-50',  'text'=>'text-orange-600',  'fill'=>'from-orange-500 to-orange-400',  'hex'=>'#F97316'],
            ['class'=>'green',  'gradient'=>'gradient-green',  'bg'=>'bg-emerald-50', 'text'=>'text-emerald-600', 'fill'=>'from-emerald-500 to-emerald-400', 'hex'=>'#10B981'],
            ['class'=>'red',    'gradient'=>'gradient-red',    'bg'=>'bg-red-50',     'text'=>'text-red-600',     'fill'=>'from-red-500 to-red-400',         'hex'=>'#EF4444'],
            ['class'=>'purple', 'gradient'=>'gradient-purple', 'bg'=>'bg-purple-50',  'text'=>'text-purple-600',  'fill'=>'from-purple-500 to-purple-400',   'hex'=>'#8B5CF6'],
        ];
    @endphp

    {{-- ══════════════ KPI CARDS ══════════════ --}}
    @php
        $kpis = [
            ['icon'=>'💰','label'=>'CA Total',     'value'=>number_format($data['ca_total'],0,',',' ').' DH',      'color'=>'blue',  'bg'=>'bg-indigo-50',  'text'=>'text-indigo-600'],
            ['icon'=>'✅','label'=>'Encaissé',      'value'=>number_format($data['ca_encaisse'],0,',',' ').' DH',   'color'=>'green', 'bg'=>'bg-emerald-50', 'text'=>'text-emerald-600'],
            ['icon'=>'⚠️','label'=>'Reste à Payer', 'value'=>number_format($data['reste_a_payer'],0,',',' ').' DH','color'=>'red',   'bg'=>'bg-red-50',     'text'=>'text-red-600'],
            ['icon'=>'📦','label'=>'BL Total',      'value'=>number_format($data['total_bl'],0,',',' '),            'color'=>'purple','bg'=>'bg-purple-50',  'text'=>'text-purple-600'],
            ['icon'=>'📈','label'=>'Taux Global',   'value'=>number_format($data['taux_global'],1).'%',             'color'=>'yellow','bg'=>'bg-amber-50',   'text'=>'text-amber-600'],
            ['icon'=>'📉','label'=>'Drops Global',  'value'=>number_format($data['drops_global'],0,',',' ').' DH/BL','color'=>'teal','bg'=>'bg-teal-50',    'text'=>'text-teal-600'],
        ];
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        @foreach($kpis as $i => $kpi)
            <div class="kpi-card {{ $kpi['color'] }} fade-in-up" style="animation-delay:{{ $i*0.05 }}s">
                <div class="kpi-icon {{ $kpi['bg'] }}">{{ $kpi['icon'] }}</div>
                <div class="kpi-value {{ $kpi['text'] }}">{{ $kpi['value'] }}</div>
                <div class="kpi-label">{{ $kpi['label'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- ══════════════ INACTIVITÉ ══════════════ --}}
    <div class="table-card fade-in-up" style="animation-delay:0.18s">
        <div class="table-header flex items-center justify-between flex-wrap gap-2">
            <h2 class="text-lg font-extrabold flex items-center gap-2">
                ⏱️ INACTIVITÉ PAR VENDEUR — limite {{ $data['objectif_jours'] }} jours
            </h2>
            <span class="text-sm bg-white/20 rounded-lg px-3 py-1 font-semibold">
                🎯 Objectif: {{ $data['objectif_jours'] }}j &nbsp;·&nbsp; Disponibles: {{ $data['disponibles'] }}j
            </span>
        </div>

        @php $nbCritiques = collect($data['inactivite'])->where('statut','red')->count(); @endphp
        @if($nbCritiques > 0)
            <div class="mx-6 mt-4 flex items-start gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-3">
                <span class="text-red-500 text-lg mt-0.5">🔴</span>
                <p class="text-sm text-red-700 font-medium">
                    <strong>{{ $nbCritiques }} vendeur{{ $nbCritiques > 1 ? 's' : '' }}</strong>
                    dépassent significativement la limite de {{ $data['objectif_jours'] }} jours.
                    @php $pire = collect($data['inactivite'])->sortByDesc('au_dela')->first(); @endphp
                    @if($pire)
                        Le plus inactif : <strong>{{ $pire['nom'] }}</strong>
                        avec <strong>+{{ $pire['au_dela'] }} jours</strong> au-delà.
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
                        <th class="text-center">Au-delà {{ $data['objectif_jours'] }}j</th>
                        <th class="text-center">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['inactivite'] as $i => $inf)
                        <tr>
                            <td class="text-gray-400 font-bold text-sm">{{ $i+1 }}</td>
                            <td class="font-bold text-gray-900">{{ $inf['nom'] }}</td>
                            <td class="text-center font-semibold text-indigo-600">{{ $inf['jours_actifs'] }}</td>
                            <td class="text-center font-semibold {{ $inf['absences'] > 3 ? 'text-red-600' : 'text-gray-600' }}">
                                {{ $inf['absences'] }}
                            </td>
                            <td class="text-center">
                                @if($inf['au_dela'] > 0)
                                    @if($inf['statut'] === 'red')
                                        <span class="badge-critique">+{{ $inf['au_dela'] }} j</span>
                                    @else
                                        <span class="badge-attention">+{{ $inf['au_dela'] }} j</span>
                                    @endif
                                @else
                                    <span class="badge-ok">✓ OK</span>
                                @endif
                            </td>
                            <td class="text-center text-xl">
                                @if($inf['statut'] === 'red') 🔴
                                @elseif($inf['statut'] === 'yellow') 🟡
                                @else 🟢
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-gray-400 py-6">Aucune donnée d'inactivité pour cette période.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ══════════════ OBJECTIFS ══════════════ --}}
    <div class="bg-white rounded-2xl shadow p-6 fade-in-up" style="animation-delay:0.22s">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">🎯 Objectifs Mensuels</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($data['objectifsData'] as $obj)
                @php $atteint = $obj['prime_gagnee']; @endphp
                <div class="border rounded-xl p-4 {{ $atteint ? 'border-green-200 bg-green-50/30' : 'border-red-200 bg-red-50/30' }}">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-bold text-gray-800">{{ $obj['nom'] }}</span>
                        <span class="text-xs px-2 py-1 rounded-full font-semibold {{ $atteint ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $atteint ? '✅ Prime gagnée' : '⚠️ Objectif non atteint' }}
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3 mb-2 shadow-inner">
                        <div class="h-3 rounded-full {{ $obj['taux_global_obj'] >= 100 ? 'bg-green-500' : ($obj['taux_global_obj'] >= 70 ? 'bg-yellow-500' : 'bg-red-500') }}"
                             style="width:{{ min($obj['taux_global_obj'],100) }}%; transition:width 1s ease;"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mb-3">
                        <span class="font-semibold">Progression globale</span>
                        <span class="font-bold">{{ $obj['taux_global_obj'] }}%</span>
                    </div>
                    <div class="text-xs space-y-2 border-t pt-2 text-gray-600">
                        <div class="flex justify-between">
                            <span>🎯 CA:</span>
                            <span>{{ number_format($obj['ca_actuel'],0,',',' ') }} / {{ number_format($obj['obj_ca'],0,',',' ') }} MAD ({{ $obj['taux_ca'] }}%)</span>
                        </div>
                        <div class="flex justify-between">
                            <span>📦 BL:</span>
                            <span>{{ $obj['bl_actuel'] }} / {{ $obj['obj_bl'] }} ({{ $obj['taux_bl'] }}%)</span>
                        </div>
                        <div class="flex justify-between font-semibold">
                            <span>💰 Prime:</span>
                            <span class="{{ $atteint ? 'text-green-600' : 'text-gray-500' }}">{{ number_format($obj['prime'],0,',',' ') }} MAD</span>
                        </div>
                        <div class="flex justify-between">
                            <span>🏁 Statut CA:</span>
                            <span class="{{ $obj['statut_ca'] === 'atteint' ? 'text-green-600 font-semibold' : 'text-red-500' }}">
                                {{ $obj['statut_ca'] === 'atteint' ? 'Atteint' : 'Pas encore' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span>🏁 Statut BL:</span>
                            <span class="{{ $obj['statut_bl'] === 'atteint' ? 'text-green-600 font-semibold' : 'text-red-500' }}">
                                {{ $obj['statut_bl'] === 'atteint' ? 'Atteint' : 'Pas encore' }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-t text-xs font-semibold {{ $atteint ? 'text-green-600' : 'text-amber-600' }}">
                        {{ $atteint ? '🎉 Ce vendeur gagne la prime de '.number_format($obj['prime'],0,',',' ').' MAD' : '⌛ Prime non gagnée pour le moment' }}
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center text-gray-400 py-6">Aucun objectif défini pour ce mois.</div>
            @endforelse
        </div>
    </div>

    {{-- ══════════════ FICHES VENDEURS ══════════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        @foreach($data['vendorCards'] as $idx => $v)
            @php
                $c    = $vColors[$idx % 5];
                $taux = $v['taux'] ?? 0;
            @endphp
            <div class="vendor-card {{ $c['class'] }} fade-in-up" style="animation-delay:{{ 0.2 + $idx * 0.06 }}s">

                <button class="vendor-toggle-btn" onclick="toggleVendorCard(this)" title="Masquer / Afficher">
                    <span class="toggle-label flex items-center gap-1">
                        <svg class="toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                        </svg>
                        <span class="toggle-text">Masquer</span>
                    </span>
                    <span class="font-extrabold {{ $c['text'] }} text-sm">{{ number_format($taux,1) }}%</span>
                </button>

                <div class="vendor-card-body">
                    <div class="vendor-badge {{ $c['gradient'] }}">
                        {{ $v['initials'] ?? strtoupper(substr($v['nom'],0,2)) }}
                    </div>
                    <h3 class="font-bold text-gray-900 text-base mb-1">{{ $v['nom'] }}</h3>
                    @if($v['tel'])
                        <p class="text-xs text-gray-500 font-semibold mb-1">📞 {{ $v['tel'] }}</p>
                    @endif
                    <p class="text-xs text-gray-500 font-semibold mb-1">{{ $v['total_bl'] }} BL</p>

                    {{-- Statut inactivité --}}
                    @if($v['au_dela'] > 0)
                        @if($v['statut'] === 'red')
                            <span class="badge-critique text-xs mb-3 inline-block">+{{ $v['au_dela'] }}j inactif</span>
                        @else
                            <span class="badge-attention text-xs mb-3 inline-block">+{{ $v['au_dela'] }}j inactif</span>
                        @endif
                    @else
                        <span class="badge-ok text-xs mb-3 inline-block">✓ Actif</span>
                    @endif

                    <div class="space-y-2 text-sm mt-2">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">CA Total</span>
                            <span class="font-bold {{ $c['text'] }}">{{ number_format($v['ca_total'],0,',',' ') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">Encaissé</span>
                            <span class="font-bold text-emerald-600">{{ number_format($v['ca_encaisse'],0,',',' ') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">Reste</span>
                            <span class="font-bold {{ $v['reste'] > 5000 ? 'text-red-600' : 'text-gray-600' }}">
                                {{ number_format($v['reste'],0,',',' ') }}
                            </span>
                        </div>
                    </div>

                    <div class="drops-mini">
                        <span class="drops-mini-label">📉 Drops</span>
                        <span class="drops-mini-value">{{ $v['total_bl'] > 0 ? number_format($v['drops'],0,',',' ').' DH/BL' : '—' }}</span>
                    </div>

                    <div class="progress-ring">
                        <div class="progress-fill bg-gradient-to-r {{ $c['fill'] }}" style="width:{{ min($taux,100) }}%"></div>
                    </div>
                    <div class="text-center mt-2">
                        <span class="font-extrabold {{ $c['text'] }} text-lg">{{ number_format($taux,1) }}%</span>
                    </div>

                    {{-- Meilleur / Pire jour --}}
                    @if($v['best_date'])
                        <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-500">
                            🏆 Meilleur: {{ $v['best_date'] }} — <span class="font-bold">{{ number_format($v['best_ca'],0,',',' ') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- ══════════════ TABLEAU MEILLEUR & PIRE JOUR ══════════════ --}}
    <div class="table-card fade-in-up" style="animation-delay:0.5s">
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
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sorted = collect($data['vendorCards'])->sortByDesc('taux')->values();
                        $rank   = 1;
                    @endphp
                    @foreach($sorted as $idx => $v)
                        @php
                            $c = $vColors[$idx % 5];
                            $t = $v['taux'] ?? 0;
                            $bc = $t >= 80 ? 'bg-emerald-100 text-emerald-700' : ($t >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700');
                            $rankClass = $rank == 1 ? 'rank-1' : ($rank == 2 ? 'rank-2' : ($rank == 3 ? 'rank-3' : 'rank-other'));
                        @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 {{ $c['gradient'] }} rounded-lg flex items-center justify-center text-white font-bold text-xs">
                                        {{ $v['initials'] ?? strtoupper(substr($v['nom'],0,2)) }}
                                    </div>
                                    <span class="font-bold text-gray-900">{{ $v['nom'] }}</span>
                                </div>
                            </td>
                            <td class="text-center text-gray-600 font-semibold">{{ $v['best_date'] ?? '—' }}</td>
                            <td class="text-center font-bold text-emerald-600">
                                {{ $v['best_ca'] ? number_format($v['best_ca'],0,',',' ').' DH' : '—' }}
                            </td>
                            <td class="text-center text-gray-600 font-semibold">{{ $v['worst_date'] ?? '—' }}</td>
                            <td class="text-center font-bold text-red-600">
                                {{ $v['worst_ca'] ? number_format($v['worst_ca'],0,',',' ').' DH' : '—' }}
                            </td>
                            <td class="text-center">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-extrabold {{ $bc }}">{{ number_format($t,1) }}%</span>
                            </td>
                            <td class="text-center">
                                @if($v['total_bl'] > 0)
                                    <span class="badge-drops">{{ number_format($v['drops'],0,',',' ') }} DH/BL</span>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge-rank {{ $rankClass }}">{{ $rank++ }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ══════════════ GRAPHIQUES ══════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 fade-in-up" style="animation-delay:0.6s">
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

    <div class="table-card fade-in-up" style="animation-delay:0.65s">
        <div class="p-6">
            <h3 class="font-bold text-gray-900 mb-1">📉 DROPS par Vendeur</h3>
            <p class="text-xs text-gray-400 mb-4">CA Total ÷ Nombre de BL — CA moyen par bon de livraison</p>
            <canvas id="dropsChart" height="140"></canvas>
        </div>
    </div>

    <div class="table-card fade-in-up" style="animation-delay:0.7s">
        <div class="p-6">
            <h3 class="font-bold text-gray-900 mb-4">📈 Évolution CA par Jour & Vendeur</h3>
            <canvas id="lineChart" height="280"></canvas>
        </div>
    </div>

    @endif {{-- end @if($data) --}}

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

@if($data)
const COLORS = ['#4F46E5','#F97316','#10B981','#EF4444','#8B5CF6'];

// Données depuis l'API
const vendorCards  = @json($data['vendorCards']);
const caJourDates  = @json($data['caJourDates']);
const caJourData   = @json($data['caJourData']);

const vendeurLabels = vendorCards.map(v => v.nom);
const caData        = vendorCards.map(v => parseFloat(v.ca_total)   || 0);
const encaisseData  = vendorCards.map(v => parseFloat(v.ca_encaisse)|| 0);
const resteData     = vendorCards.map(v => parseFloat(v.reste)       || 0);
const dropsData     = vendorCards.map(v => parseFloat(v.drops)       || 0);

// Bar chart
new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: vendeurLabels,
        datasets: [
            { label: 'CA Total',  data: caData,       backgroundColor: '#4F46E5', borderRadius: 8 },
            { label: 'Encaissé',  data: encaisseData, backgroundColor: '#10B981', borderRadius: 8 },
            { label: 'Reste',     data: resteData,    backgroundColor: '#EF4444', borderRadius: 8 },
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 11, weight: 600 }, boxWidth: 12, padding: 12 } }
        },
        scales: {
            y: { beginAtZero: true, ticks: { callback: v => (v/1000).toFixed(0)+'k DH', font: { size:11 } }, grid: { color:'#F1F5F9' } },
            x: { ticks: { font: { size:11, weight:600 } }, grid: { display:false } }
        }
    }
});

// Pie chart
new Chart(document.getElementById('pieChart'), {
    type: 'doughnut',
    data: {
        labels: vendeurLabels,
        datasets: [{ data: caData, backgroundColor: COLORS, borderWidth: 3, borderColor: '#fff' }]
    },
    options: {
        responsive: true, cutout: '60%',
        plugins: {
            legend: { position: 'bottom', labels: { font: { size:11, weight:600 }, boxWidth:12, padding:12 } },
            tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed.toLocaleString('fr-MA')} DH` } }
        }
    }
});

// Drops chart
new Chart(document.getElementById('dropsChart'), {
    type: 'bar',
    data: {
        labels: vendeurLabels,
        datasets: [{
            label: 'Drops (DH/BL)', data: dropsData,
            backgroundColor: COLORS.map(c => c+'BB'), borderColor: COLORS, borderWidth: 2, borderRadius: 10
        }]
    },
    options: {
        responsive: true, indexAxis: 'y',
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.x.toLocaleString('fr-MA')} DH par BL` } }
        },
        scales: {
            x: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('fr-MA')+' DH', font:{ size:11 } }, grid:{ color:'#F1F5F9' } },
            y: { ticks: { font:{ size:12, weight:700 } }, grid:{ display:false } }
        }
    }
});

// Line chart — CA par jour par vendeur
if (caJourDates.length > 0) {
    const lineDatasets = caJourData.map((serie, index) => ({
        label: serie.nom,
        data: serie.data,
        borderColor: COLORS[index % COLORS.length],
        backgroundColor: COLORS[index % COLORS.length] + '20',
        borderWidth: 2.5, tension: 0.4, pointRadius: 4, pointHoverRadius: 6,
        pointBackgroundColor: COLORS[index % COLORS.length],
        pointBorderColor: '#fff', pointBorderWidth: 2, fill: false,
    }));

    new Chart(document.getElementById('lineChart'), {
        type: 'line',
        data: { labels: caJourDates, datasets: lineDatasets },
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