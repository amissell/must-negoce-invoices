
<aside id="sidebar"
       class="fixed top-0 left-0 h-full w-72 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900
              shadow-2xl z-40 flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">

    {{-- ── Logo / Brand ─────────────────────────────── --}}
    <div class="flex items-center gap-3 px-6 py-6 border-b border-white/10">
        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <h2 class="text-white font-bold text-base leading-tight truncate">Suivi Vendeurs</h2>
            <p class="text-slate-400 text-xs mt-0.5">{{ \Carbon\Carbon::now()->isoFormat('MMMM YYYY') }}</p>
        </div>
    </div>

    {{-- ── Navigation Links ──────────────────────────── --}}
    <nav class="px-3 py-4 border-b border-white/10 space-y-1">
        <a href="{{ route('dashboard') }}"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-xl
                  {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:bg-white/10 hover:text-white' }}
                  transition-all duration-200 text-sm font-medium">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard Factures
        </a>
        <a href="{{ route('dashboard') }}?tab=vendeurs"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-xl
                  {{ request()->get('tab') === 'vendeurs' ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:bg-white/10 hover:text-white' }}
                  transition-all duration-200 text-sm font-medium">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Suivi Vendeurs
        </a>
    </nav>

    {{-- ── Scrollable Content ────────────────────────── --}}
    <div class="flex-1 overflow-y-auto px-3 py-4 space-y-4
                scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent">

        {{-- ┌─ KPIs Globaux ──────────────────────────┐ --}}
        <div>
            <p class="text-slate-500 text-xs font-semibold uppercase tracking-widest px-2 mb-3">
                Vue Globale
            </p>

            {{-- CA Total --}}
            <div class="group relative bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl p-4 mb-2
                        hover:from-blue-500 hover:to-blue-600 transition-all duration-300 cursor-default overflow-hidden">
                <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-blue-200 text-xs font-medium">💰 CA Total</p>
                        <p class="text-white font-bold text-xl mt-0.5 leading-tight">
                            @if(isset($sidebarStats))
                                {{ number_format($sidebarStats['total_ca'], 0, ',', ' ') }}
                            @else 0 @endif
                            <span class="text-blue-300 text-xs font-normal">DH</span>
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Reste à Payer --}}
            <div class="group relative bg-gradient-to-br from-red-600 to-rose-700 rounded-2xl p-4 mb-2
                        hover:from-red-500 hover:to-rose-600 transition-all duration-300 cursor-default overflow-hidden">
                <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-red-200 text-xs font-medium">⚠️ Reste à Payer</p>
                        <p class="text-white font-bold text-xl mt-0.5 leading-tight">
                            @if(isset($sidebarStats))
                                {{ number_format($sidebarStats['total_reste'], 0, ',', ' ') }}
                            @else 0 @endif
                            <span class="text-red-300 text-xs font-normal">DH</span>
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Taux Global --}}
            <div class="bg-slate-800/80 rounded-2xl p-4 border border-white/5">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-slate-400 text-xs font-medium">📈 Taux Encaissement</p>
                    <span class="text-white font-bold text-sm">
                        @if(isset($sidebarStats) && $sidebarStats['total_ca'] > 0)
                            {{ round($sidebarStats['total_encaisse'] / $sidebarStats['total_ca'] * 100, 1) }}%
                        @else 0% @endif
                    </span>
                </div>
                {{-- Progress Bar --}}
                @php
                    $taux = isset($sidebarStats) && $sidebarStats['total_ca'] > 0
                        ? round($sidebarStats['total_encaisse'] / $sidebarStats['total_ca'] * 100, 1)
                        : 0;
                    $barColor = $taux >= 80 ? 'from-green-500 to-emerald-400'
                              : ($taux >= 50 ? 'from-yellow-500 to-amber-400'
                              : 'from-red-500 to-rose-400');
                @endphp
                <div class="w-full bg-slate-700 rounded-full h-2 overflow-hidden">
                    <div class="h-full bg-gradient-to-r {{ $barColor }} rounded-full transition-all duration-700"
                         style="width: {{ min($taux, 100) }}%"></div>
                </div>
            </div>
        </div>

        {{-- ┌─ Par Vendeur ───────────────────────────┐ --}}
        <div>
            <p class="text-slate-500 text-xs font-semibold uppercase tracking-widest px-2 mb-3">
                Par Vendeur
            </p>

            @php
                $vendeurColors = [
                    ['from-blue-500','to-blue-600','text-blue-400','bg-blue-500/20'],
                    ['from-orange-500','to-amber-600','text-orange-400','bg-orange-500/20'],
                    ['from-emerald-500','to-green-600','text-emerald-400','bg-emerald-500/20'],
                    ['from-red-500','to-rose-600','text-red-400','bg-red-500/20'],
                    ['from-purple-500','to-violet-600','text-purple-400','bg-purple-500/20'],
                ];
            @endphp

            @if(isset($sidebarVendeurs) && $sidebarVendeurs->count())
                @foreach($sidebarVendeurs as $i => $v)
                @php $c = $vendeurColors[$i % 5]; @endphp
                <div class="group bg-slate-800/60 hover:bg-slate-800 border border-white/5 hover:border-white/10
                            rounded-2xl p-4 mb-2 transition-all duration-200 cursor-default">

                    {{-- Header: nom + taux badge --}}
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 bg-gradient-to-br {{ $c[0] }} {{ $c[1] }} rounded-lg
                                        flex items-center justify-center flex-shrink-0 shadow-md">
                                <span class="text-white font-bold text-xs">
                                    {{ strtoupper(substr($v->vendeur, 0, 2)) }}
                                </span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-white text-sm font-semibold truncate leading-tight">{{ $v->vendeur }}</p>
                                <p class="text-slate-500 text-xs">{{ $v->bl }} BL</p>
                            </div>
                        </div>
                        {{-- Taux badge --}}
                        <span class="px-2 py-1 {{ $c[3] }} {{ $c[2] }} rounded-lg text-xs font-bold flex-shrink-0">
                            {{ $v->taux ?? 0 }}%
                        </span>
                    </div>

                    {{-- Stats row --}}
                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-slate-900/50 rounded-xl p-2.5">
                            <p class="text-slate-500 text-xs mb-0.5">CA</p>
                            <p class="text-white text-xs font-bold truncate">
                                {{ number_format($v->ca_total, 0, ',', ' ') }} DH
                            </p>
                        </div>
                        <div class="bg-slate-900/50 rounded-xl p-2.5">
                            <p class="text-slate-500 text-xs mb-0.5">Encaissé</p>
                            <p class="text-emerald-400 text-xs font-bold truncate">
                                {{ number_format($v->encaisse, 0, ',', ' ') }} DH
                            </p>
                        </div>
                    </div>

                    {{-- Reste Warning --}}
                    @if($v->reste > 0)
                    <div class="mt-2 flex items-center justify-between bg-red-500/10 border border-red-500/20 rounded-xl px-3 py-2">
                        <span class="text-red-400 text-xs">Reste</span>
                        <span class="text-red-300 text-xs font-bold">
                            {{ number_format($v->reste, 0, ',', ' ') }} DH
                            @if($v->reste > 5000)
                                🔴
                            @endif
                        </span>
                    </div>
                    @else
                    <div class="mt-2 flex items-center gap-1.5 bg-emerald-500/10 border border-emerald-500/20 rounded-xl px-3 py-2">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-emerald-400 text-xs font-medium">Soldé ✓</span>
                    </div>
                    @endif
                </div>
                @endforeach
            @else
                <div class="text-center py-8">
                    <div class="w-12 h-12 bg-slate-800 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <p class="text-slate-500 text-xs">Aucune donnée vendeur</p>
                </div>
            @endif
        </div>

        {{-- ┌─ Meilleur Jour (compact) ───────────────┐ --}}
        @if(isset($sidebarMeilleurJour) && $sidebarMeilleurJour)
        <div>
            <p class="text-slate-500 text-xs font-semibold uppercase tracking-widest px-2 mb-3">
                🏆 Meilleur Jour
            </p>
            <div class="bg-gradient-to-br from-amber-500/20 to-orange-500/10 border border-amber-500/20 rounded-2xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-amber-400 text-xs font-medium">{{ $sidebarMeilleurJour->vendeur }}</p>
                        <p class="text-white font-bold text-lg">
                            {{ number_format($sidebarMeilleurJour->max_ca, 0, ',', ' ') }}
                            <span class="text-amber-300 text-xs font-normal">DH</span>
                        </p>
                        <p class="text-slate-500 text-xs mt-0.5">{{ $sidebarMeilleurJour->best_date }}</p>
                    </div>
                    <div class="text-3xl">🥇</div>
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- ── User Footer ───────────────────────────────── --}}
    <div class="px-3 py-4 border-t border-white/10">
        <div class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-white/5 transition-colors duration-200 cursor-pointer">
            <div class="w-9 h-9 bg-gradient-to-br from-purple-500 to-blue-600 rounded-xl flex items-center justify-center text-white text-sm font-bold shadow-md flex-shrink-0">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white text-sm font-semibold truncate">{{ auth()->user()->name ?? 'Utilisateur' }}</p>
                <p class="text-slate-500 text-xs truncate">{{ auth()->user()->email ?? '' }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Déconnexion"
                        class="text-slate-500 hover:text-red-400 transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- ════════════════════════════════════════════
     MOBILE OVERLAY
════════════════════════════════════════════ --}}
<div id="sidebarOverlay"
     class="fixed inset-0 bg-black/60 z-30 lg:hidden hidden backdrop-blur-sm"
     onclick="toggleSidebar()">
</div>

{{-- ════════════════════════════════════════════
     MOBILE TOGGLE BUTTON
════════════════════════════════════════════ --}}
<button onclick="toggleSidebar()"
        class="fixed top-4 left-4 z-50 lg:hidden w-10 h-10 bg-slate-900 hover:bg-slate-800
               text-white rounded-xl shadow-xl flex items-center justify-center transition-all duration-200">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
</button>

<script>
function toggleSidebar() {
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebarOverlay');
    const isOpen   = !sidebar.classList.contains('-translate-x-full');
    sidebar.classList.toggle('-translate-x-full', isOpen);
    overlay.classList.toggle('hidden', isOpen);
}
</script>