@extends('layouts.app')
@section('title', 'Dashboard Suivi Vendeurs')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    
    * { font-family: 'Plus Jakarta Sans', sans-serif; }
    
    /* ── Gradients & Colors ────────────────────────── */
    .gradient-blue   { background: linear-gradient(135deg, #4F46E5 0%, #6366F1 100%); }
    .gradient-orange { background: linear-gradient(135deg, #F97316 0%, #FB923C 100%); }
    .gradient-green  { background: linear-gradient(135deg, #10B981 0%, #34D399 100%); }
    .gradient-red    { background: linear-gradient(135deg, #EF4444 0%, #F87171 100%); }
    .gradient-purple { background: linear-gradient(135deg, #8B5CF6 0%, #A78BFA 100%); }
    .gradient-yellow { background: linear-gradient(135deg, #F59E0B 0%, #FCD34D 100%); }
    
    /* ── Page Hero ────────────────────────────────── */
    .page-hero {
        background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
        border-radius: 24px;
        position: relative;
        overflow: hidden;
    }
    .page-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
        animation: float 20s ease-in-out infinite;
    }
    @keyframes float {
        0%, 100% { transform: translate(0, 0) rotate(0deg); }
        50% { transform: translate(-30px, 30px) rotate(180deg); }
    }
    
    /* ── KPI Cards ───────────────────────────────── */
    .kpi-card {
        background: white;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.05);
    }
    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
    }
    .kpi-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 32px rgba(0,0,0,0.12);
    }
    .kpi-card.blue::before   { background: linear-gradient(90deg, #4F46E5, #6366F1); }
    .kpi-card.green::before  { background: linear-gradient(90deg, #10B981, #34D399); }
    .kpi-card.red::before    { background: linear-gradient(90deg, #EF4444, #F87171); }
    .kpi-card.purple::before { background: linear-gradient(90deg, #8B5CF6, #A78BFA); }
    .kpi-card.yellow::before { background: linear-gradient(90deg, #F59E0B, #FCD34D); }
    
    .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 12px;
    }
    .kpi-value {
        font-size: 32px;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 6px;
    }
    .kpi-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748B;
    }
    
    /* ── Vendor Cards ────────────────────────────── */
    .vendor-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(0,0,0,0.05);
        position: relative;
        overflow: hidden;
    }
    .vendor-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
    }
    .vendor-card.blue::before   { background: linear-gradient(90deg, #4F46E5, #6366F1); }
    .vendor-card.orange::before { background: linear-gradient(90deg, #F97316, #FB923C); }
    .vendor-card.green::before  { background: linear-gradient(90deg, #10B981, #34D399); }
    .vendor-card.red::before    { background: linear-gradient(90deg, #EF4444, #F87171); }
    .vendor-card.purple::before { background: linear-gradient(90deg, #8B5CF6, #A78BFA); }
    
    .vendor-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(0,0,0,0.12);
    }
    
    .vendor-badge {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 16px;
        color: white;
        margin-bottom: 12px;
    }
    
    .progress-ring {
        height: 8px;
        background: #F1F5F9;
        border-radius: 999px;
        overflow: hidden;
        margin-top: 12px;
    }
    .progress-fill {
        height: 100%;
        border-radius: 999px;
        transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* ── Table ───────────────────────────────────── */
    .table-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.05);
    }
    .table-header {
        background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
        padding: 20px 28px;
        color: white;
    }
    .dashboard-table thead th {
        background: #F8FAFC;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 16px 20px;
        border-bottom: 2px solid #E2E8F0;
    }
    .dashboard-table tbody td {
        padding: 16px 20px;
        border-bottom: 1px solid #F1F5F9;
        vertical-align: middle;
    }
    .dashboard-table tbody tr:hover {
        background: #F8FAFC;
    }
    
    /* ── Animations ──────────────────────────────── */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(24px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .fade-in-up { animation: fadeInUp 0.5s ease-out backwards; }
    
    /* ── Badge ───────────────────────────────────── */
    .badge-rank {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 14px;
    }
    .rank-1 { background: linear-gradient(135deg, #F59E0B 0%, #FCD34D 100%); color: white; }
    .rank-2 { background: linear-gradient(135deg, #94A3B8 0%, #CBD5E1 100%); color: white; }
    .rank-3 { background: linear-gradient(135deg, #F97316 0%, #FB923C 100%); color: white; }
    .rank-other { background: #F1F5F9; color: #64748B; }
</style>
@endpush

@section('content')
<div class="min-h-screen" style="background: linear-gradient(to bottom, #F8FAFC 0%, #EFF6FF 100%);">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- ══════════════════════════════════════════════════
         PAGE HERO
    ══════════════════════════════════════════════════ --}}
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
                    {{ \Carbon\Carbon::create($annee ?? now()->year, $mois ?? now()->month)->translatedFormat('F Y') }}
                </p>
            </div>
            <div class="flex gap-3">
                <form method="GET" class="flex gap-2">
                    <select name="mois" onchange="this.form.submit()"
                            class="bg-white/20 backdrop-blur-sm border border-white/30 text-white text-sm font-semibold
                                   rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-white/50">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" @selected(($mois ?? now()->month) == $m) class="text-gray-800">
                                {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                    <select name="annee" onchange="this.form.submit()"
                            class="bg-white/20 backdrop-blur-sm border border-white/30 text-white text-sm font-semibold
                                   rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-white/50">
                        @foreach([2024, 2025, 2026] as $y)
                            <option value="{{ $y }}" @selected(($annee ?? now()->year) == $y) class="text-gray-800">{{ $y }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('suivi-vendeurs.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-white hover:bg-white/90
                          text-indigo-600 font-bold rounded-xl shadow-lg hover:shadow-xl
                          transition-all duration-200 text-sm">
                    ➕ <span>Saisie</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         KPI CARDS
    ══════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        @php
            $kpis = [
                ['icon' => '💰', 'label' => 'CA Total', 'value' => number_format($statsGlobales['ca_total'] ?? 0, 0, ',', ' ') . ' DH', 'color' => 'blue', 'bg' => 'bg-indigo-50', 'text' => 'text-indigo-600'],
                ['icon' => '✅', 'label' => 'Encaissé', 'value' => number_format($statsGlobales['encaisse'] ?? 0, 0, ',', ' ') . ' DH', 'color' => 'green', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
                ['icon' => '⚠️', 'label' => 'Reste à Payer', 'value' => number_format($statsGlobales['reste'] ?? 0, 0, ',', ' ') . ' DH', 'color' => 'red', 'bg' => 'bg-red-50', 'text' => 'text-red-600'],
                ['icon' => '📦', 'label' => 'BL Total', 'value' => $statsGlobales['bl_total'] ?? 0, 'color' => 'purple', 'bg' => 'bg-purple-50', 'text' => 'text-purple-600'],
                ['icon' => '📈', 'label' => 'Taux Global', 'value' => ($statsGlobales['taux_global'] ?? 0) . '%', 'color' => 'yellow', 'bg' => 'bg-amber-50', 'text' => 'text-amber-600'],
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

    {{-- ══════════════════════════════════════════════════
         VENDOR CARDS
    ══════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        @php
            $vColors = [
                ['class' => 'blue', 'gradient' => 'gradient-blue', 'bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'fill' => 'from-indigo-500 to-indigo-400'],
                ['class' => 'orange', 'gradient' => 'gradient-orange', 'bg' => 'bg-orange-50', 'text' => 'text-orange-600', 'fill' => 'from-orange-500 to-orange-400'],
                ['class' => 'green', 'gradient' => 'gradient-green', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'fill' => 'from-emerald-500 to-emerald-400'],
                ['class' => 'red', 'gradient' => 'gradient-red', 'bg' => 'bg-red-50', 'text' => 'text-red-600', 'fill' => 'from-red-500 to-red-400'],
                ['class' => 'purple', 'gradient' => 'gradient-purple', 'bg' => 'bg-purple-50', 'text' => 'text-purple-600', 'fill' => 'from-purple-500 to-purple-400'],
            ];
        @endphp

        @foreach(($suiviParVendeur ?? collect()) as $nom => $stats)
        @php
            $i = $loop->index;
            $c = $vColors[$i % 5];
            $taux = $stats['taux'] ?? 0;
        @endphp
        <div class="vendor-card {{ $c['class'] }} fade-in-up" style="animation-delay: {{ 0.2 + $i * 0.06 }}s">
            <div class="vendor-badge {{ $c['gradient'] }}">
                {{ strtoupper(substr($nom, 0, 2)) }}
            </div>
            <h3 class="font-bold text-gray-900 text-base mb-1">{{ $nom }}</h3>
            <p class="text-xs text-gray-500 font-semibold mb-4">{{ $stats['bl'] ?? 0 }} BL</p>
            
            <div class="space-y-2 text-sm">
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
            
            <div class="progress-ring">
                <div class="progress-fill bg-gradient-to-r {{ $c['fill'] }}"
                     style="width: {{ min($taux, 100) }}%"></div>
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
        @endforeach
    </div>

    {{-- ══════════════════════════════════════════════════
         MEILLEUR & PIRE JOUR TABLE
    ══════════════════════════════════════════════════ --}}
    <div class="table-card fade-in-up" style="animation-delay: 0.5s">
        <div class="table-header">
            <h2 class="text-lg font-extrabold flex items-center gap-2">
                🏆 MEILLEUR & PIRE JOUR PAR VENDEUR
            </h2>
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
                        <th class="text-center">RANK</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rank = 1; @endphp
                    @foreach(($suiviParVendeur ?? collect())->sortByDesc('taux') as $nom => $stats)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                @php $c = $vColors[array_search($nom, ($suiviParVendeur ?? collect())->keys()->toArray()) % 5]; @endphp
                                <div class="w-9 h-9 {{ $c['gradient'] }} rounded-lg flex items-center justify-center text-white font-bold text-xs">
                                    {{ strtoupper(substr($nom, 0, 2)) }}
                                </div>
                                <span class="font-bold text-gray-900">{{ $nom }}</span>
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
                                $badgeColor = $t >= 80 ? 'bg-emerald-100 text-emerald-700' : ($t >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700');
                            @endphp
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-extrabold {{ $badgeColor }}">
                                {{ $t }}%
                            </span>
                        </td>
                        <td class="text-center">
                            @php
                                $rankClass = $rank == 1 ? 'rank-1' : ($rank == 2 ? 'rank-2' : ($rank == 3 ? 'rank-3' : 'rank-other'));
                            @endphp
                            <span class="badge-rank {{ $rankClass }}">{{ $rank++ }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         CHARTS ROW 1
    ══════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 fade-in-up" style="animation-delay: 0.6s">
        <div class="table-card">
            <div class="p-6">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    📊 CA vs Encaissé vs Reste par Vendeur
                </h3>
                <canvas id="barChart" height="220"></canvas>
            </div>
        </div>
        <div class="table-card">
            <div class="p-6">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    🥧 Répartition CA par Vendeur
                </h3>
                <canvas id="pieChart" height="220"></canvas>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         LINE CHART — Évolution CA par Jour
    ══════════════════════════════════════════════════ --}}
    <div class="table-card fade-in-up" style="animation-delay: 0.7s">
        <div class="p-6">
            <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                📈 Évolution CA par Jour & Vendeur
            </h3>
            <canvas id="lineChart" height="280"></canvas>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         DERNIÈRES SAISIES TABLE
    ══════════════════════════════════════════════════ --}}
    <div class="table-card fade-in-up" style="animation-delay: 0.8s">
        <div class="table-header flex justify-between items-center">
            <h2 class="text-lg font-extrabold flex items-center gap-2">
                📋 Dernières Saisies
            </h2>
            <span class="text-sm font-semibold text-white/80">
                {{ isset($lignes) ? $lignes->total() : 0 }} entrées
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full dashboard-table">
                <thead>
                    <tr>
                        <th class="text-left">DATE</th>
                        <th class="text-left">VENDEUR</th>
                        <th class="text-right">CHIFFRE</th>
                        <th class="text-right">PAYÉ</th>
                        <th class="text-right">RESTE</th>
                        <th class="text-center">BL</th>
                        <th class="text-center">HEURES</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($lignes ?? collect())->take(10) as $ligne)
                    @php
                        $reste = $ligne->chiffre - $ligne->total_paye;
                        $vendeurIndex = array_search($ligne->vendeur_nom, ($suiviParVendeur ?? collect())->keys()->toArray());
                        $c = $vColors[$vendeurIndex % 5] ?? $vColors[0];
                    @endphp
                    <tr>
                        <td class="text-gray-600 font-semibold">
                            {{ \Carbon\Carbon::parse($ligne->date)->format('d/m/Y') }}
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 {{ $c['gradient'] }} rounded-lg flex items-center justify-center text-white font-bold text-xs">
                                    {{ strtoupper(substr($ligne->vendeur_nom, 0, 2)) }}
                                </div>
                                <span class="font-bold text-gray-900">{{ $ligne->vendeur_nom }}</span>
                            </div>
                        </td>
                        <td class="text-right font-bold text-gray-900">
                            {{ number_format($ligne->chiffre, 0, ',', ' ') }} DH
                        </td>
                        <td class="text-right font-bold text-emerald-600">
                            {{ number_format($ligne->total_paye, 0, ',', ' ') }} DH
                        </td>
                        <td class="text-right font-bold {{ $reste > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                            {{ number_format($reste, 0, ',', ' ') }} DH
                        </td>
                        <td class="text-center">
                            <span class="inline-block px-2 py-1 bg-purple-50 text-purple-700 rounded-lg text-xs font-bold">
                                {{ $ligne->bl }}
                            </span>
                        </td>
                        <td class="text-center text-gray-500 text-xs font-medium">
                            {{ $ligne->heure_debut ? \Carbon\Carbon::parse($ligne->heure_debut)->format('H:i') : '—' }}
                            –
                            {{ $ligne->heure_fin ? \Carbon\Carbon::parse($ligne->heure_fin)->format('H:i') : '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-400">
                            Aucune saisie ce mois-ci
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const COLORS = ['#4F46E5', '#F97316', '#10B981', '#EF4444', '#8B5CF6'];

const vendeurLabels = @json(($suiviParVendeur ?? collect())->keys());
const caData        = @json(($suiviParVendeur ?? collect())->pluck('ca_total')->values());
const encaisseData  = @json(($suiviParVendeur ?? collect())->pluck('encaisse')->values());
const resteData     = @json(($suiviParVendeur ?? collect())->pluck('reste')->values());

// Données journalières pour Line Chart
const datesLabels   = @json($chartDates ?? []);
const dailyCAData   = @json($chartDataCA ?? []);

// ── 1. Bar Chart — CA vs Encaissé vs Reste ──────────
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
        plugins: { 
            legend: { 
                position: 'bottom', 
                labels: { 
                    font: { size: 11, weight: 600 }, 
                    boxWidth: 12,
                    padding: 12
                } 
            } 
        },
        scales: {
            y: { 
                beginAtZero: true, 
                ticks: { 
                    callback: v => (v/1000).toFixed(0)+'k DH', 
                    font: { size: 11 } 
                }, 
                grid: { color: '#F1F5F9' } 
            },
            x: { 
                ticks: { font: { size: 11, weight: 600 } }, 
                grid: { display: false } 
            }
        }
    }
});

// ── 2. Pie Chart — Répartition CA ────────────────────
new Chart(document.getElementById('pieChart'), {
    type: 'doughnut',
    data: {
        labels: vendeurLabels,
        datasets: [{ 
            data: caData, 
            backgroundColor: COLORS, 
            borderWidth: 3, 
            borderColor: '#fff' 
        }]
    },
    options: {
        responsive: true,
        cutout: '60%',
        plugins: {
            legend: { 
                position: 'bottom', 
                labels: { 
                    font: { size: 11, weight: 600 }, 
                    boxWidth: 12,
                    padding: 12
                } 
            },
            tooltip: { 
                callbacks: { 
                    label: ctx => ` ${ctx.label}: ${ctx.parsed.toLocaleString('fr-MA')} DH`
                } 
            }
        }
    }
});

// ── 3. Line Chart — Évolution CA par Jour ───────────
if (datesLabels.length > 0) {
    const lineDatasets = [];
    
    Object.keys(dailyCAData).forEach((vendeur, index) => {
        lineDatasets.push({
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
        });
    });
    
    new Chart(document.getElementById('lineChart'), {
        type: 'line',
        data: {
            labels: datesLabels,
            datasets: lineDatasets
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: { 
                    position: 'bottom',
                    labels: { 
                        font: { size: 11, weight: 600 },
                        boxWidth: 12,
                        padding: 14
                    }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString('fr-MA')} DH`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => (v/1000).toFixed(0)+'k DH',
                        font: { size: 11 }
                    },
                    grid: { color: '#F1F5F9' }
                },
                x: {
                    ticks: { font: { size: 10, weight: 600 } },
                    grid: { display: false }
                }
            }
        }
    });
}
</script>
@endpush