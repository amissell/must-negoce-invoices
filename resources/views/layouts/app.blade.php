<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MUST NEGOCE') - Gestion de Factures</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .gradient-header { background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 50%, #3b7cb8 100%); }
        .card-shadow { box-shadow: 0 4px 20px -4px rgba(30, 58, 95, 0.15); }
        .btn-primary { background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); }
        .btn-primary:hover { background: linear-gradient(135deg, #2d5a87 0%, #3b7cb8 100%); }
        #sidebar { scrollbar-width: thin; }
        #sidebar::-webkit-scrollbar { width: 6px; }
        #sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen">

<div class="flex min-h-screen">

    {{-- ══════════════════════════════════════════════════════════
         SIDEBAR
    ══════════════════════════════════════════════════════════ --}}
    <aside id="sidebar"
           class="gradient-header text-white w-72 flex-shrink-0 fixed md:sticky top-0 h-screen z-50 overflow-y-auto
                  transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col">

        {{-- Logo --}}
        <a href="{{ (auth()->user()->isAdmin() || auth()->user()->isAdv()) ? route('invoices.index') : route('suivi-vendeurs.index') }}"
           class="flex items-center gap-3 px-5 py-5 border-b border-white/10">
            <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-lg font-bold leading-tight">MUST NEGOCE</h1>
                <p class="text-xs text-white/60">Gestion de Factures</p>
            </div>
        </a>

        {{-- Navigation Links --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">

            {{-- ADMIN & ADV links --}}
            @if(auth()->user()->isAdmin() || auth()->user()->isAdv())
                <p class="px-3 pt-2 pb-1 text-xs font-semibold uppercase tracking-wider text-white/40">Gestion</p>

                <a href="{{ route('invoices.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('invoices.index') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    <span class="text-lg">📊</span> Tableau de Bord
                </a>
                <a href="{{ route('products.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('products.*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    <span class="text-lg">📦</span> Produits
                </a>
                <a href="{{ route('clients.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('clients.*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    <span class="text-lg">👥</span> Clients
                </a>
                <a href="{{ route('invoices.manual.create') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('invoices.manual.*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    <span class="text-lg">✏️</span> Facture Manuelle
                </a>
            @endif

            @if(!auth()->user()->isAdv())
                <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-white/40">Suivi</p>

                <a href="{{ route('suivi-vendeurs.index') }}"
                   class="relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                   {{ request()->routeIs('suivi-vendeurs.*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="flex-1">Suivi Vendeurs</span>
                    @if(isset($sidebarStats) && $sidebarStats->total_reste > 5000)
                    <span class="w-5 h-5 bg-red-500 text-white text-xs font-bold
                    rounded-full flex items-center justify-center animate-pulse">!</span>
                    @endif
                </a>
            @endif

            @if(auth()->user()->isAdmin())
                <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-white/40">Administration</p>

                <a href="{{ route('statistics.daily') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('statistics.*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    <span class="text-lg">📈</span> Statistiques
                </a>
                <a href="{{ route('vendeurs.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('vendeurs.*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    <span class="text-lg">🧑‍💼</span> Vendeurs
                </a>
                <a href="{{ route('categories.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('categories.*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    <span class="text-lg">📦</span> Catégories
                </a>
                <a href="{{ route('creances.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('creances.*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    <span class="text-lg">💰</span> Créances
                </a>
                <a href="{{ route('alertes.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('alertes.*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    <span class="text-lg">🔔</span> Alertes
                </a>
                <a href="{{ route('suivi-vendeurs.comparaison') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('suivi-vendeurs.comparaison') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    <span class="text-lg">📊</span> Comparaison
                </a>
                <a href="{{ route('objectifs.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('objectifs.*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    <span class="text-lg">🎯</span> Objectifs
                </a>
                <a href="{{ route('admin.users') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('admin.users*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    <span class="text-lg">👤</span> Utilisateurs
                </a>
                <a href="{{ route('dashboard.vendeurs') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('dashboard.vendeurs*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    <span class="text-lg">👤</span> 📊 Dashboard Vendeurs
                </a>
                <a href="{{ route('dashboard.vendeur-categorie') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('dashboard.vendeur-categorie*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    <span class="text-lg">📋</span> Vendeur × Catégorie
                </a>
            @endif
        </nav>

        {{-- User Menu (bottom of sidebar) --}}
        <div class="border-t border-white/10 p-3 relative group">
            <button class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 transition-all">
                <div class="w-9 h-9 bg-white/20 rounded-full flex items-center justify-center shrink-0">
                    <span class="text-sm font-semibold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                </div>
                <div class="flex-1 text-left min-w-0">
                    <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-white/50 truncate">{{ ucfirst(auth()->user()->role) }}</p>
                </div>
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                </svg>
            </button>
            <div class="absolute left-3 right-3 bottom-full mb-2 bg-white rounded-xl shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                <div class="p-3 border-b border-gray-100">
                    <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                    <span class="inline-block mt-1 text-xs font-bold px-2 py-0.5 rounded-full
                        {{ auth()->user()->isAdmin() ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ ucfirst(auth()->user()->role) }}
                    </span>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="p-2">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Overlay for mobile, click to close sidebar --}}
    <div id="sidebarOverlay" class="hidden fixed inset-0 bg-black/50 z-40 md:hidden"></div>

    {{-- ══════════════════════════════════════════════════════════
         MAIN COLUMN
    ══════════════════════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Mobile top bar --}}
        <div class="md:hidden sticky top-0 z-30 gradient-header text-white flex items-center justify-between px-4 h-14 shadow-md">
            <button id="sidebarToggle" class="p-2 rounded-lg hover:bg-white/10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <span class="font-bold">MUST NEGOCE</span>
            <div class="w-10"></div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             FLASH MESSAGES
        ══════════════════════════════════════════════════════════ --}}
        @if(session('success'))
            <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════
             MAIN CONTENT
        ══════════════════════════════════════════════════════════ --}}
        <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
            @yield('content')
        </main>
    </div>
</div>

{{-- Statistics Modal --}}
<div id="statsModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeStatsModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg bg-white rounded-2xl shadow-2xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-800">📈 Statistiques du Jour</h2>
            <button onclick="closeStatsModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="mb-4">
            <input type="date" id="statsDate" value="{{ now()->toDateString() }}"
                onchange="loadStats()"
                class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div id="statsContent">
            <div class="text-center py-8 text-gray-400">Chargement...</div>
        </div>
    </div>
</div>

<script>
function openStatsModal() {
    document.getElementById('statsModal').classList.remove('hidden');
    loadStats();
}
function closeStatsModal() {
    document.getElementById('statsModal').classList.add('hidden');
}
function loadStats() {
    const date = document.getElementById('statsDate').value;
    document.getElementById('statsContent').innerHTML = '<div class="text-center py-8 text-gray-400">Chargement...</div>';
    fetch(`/statistics/daily?date=${date}`)
        .then(r => r.json())
        .then(data => {
            if (data.stats.length === 0) {
                document.getElementById('statsContent').innerHTML =
                    '<div class="text-center py-8 text-gray-400">Aucune vente pour cette date</div>';
                return;
            }
            let totalCA = 0, totalQte = 0, rows = '';
            data.stats.forEach(s => {
                totalCA += parseFloat(s.total_ca);
                totalQte += parseInt(s.total_quantite);
                rows += `
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-3 px-4 font-medium text-gray-700">${s.categorie}</td>
                        <td class="py-3 px-4 text-center text-gray-600">${s.total_quantite}</td>
                        <td class="py-3 px-4 text-right font-semibold text-green-600">${parseFloat(s.total_ca).toLocaleString('fr-FR')} DH</td>
                    </tr>`;
            });
            document.getElementById('statsContent').innerHTML = `
                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 px-4 text-left text-gray-600 font-semibold">Catégorie</th>
                                <th class="py-3 px-4 text-center text-gray-600 font-semibold">Quantité</th>
                                <th class="py-3 px-4 text-right text-gray-600 font-semibold">CA</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                        <tfoot class="bg-blue-50">
                            <tr>
                                <td class="py-3 px-4 font-bold text-gray-800">Total</td>
                                <td class="py-3 px-4 text-center font-bold text-gray-800">${totalQte}</td>
                                <td class="py-3 px-4 text-right font-bold text-blue-700">${totalCA.toLocaleString('fr-FR')} DH</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>`;
        })
        .catch(() => {
            document.getElementById('statsContent').innerHTML =
                '<div class="text-center py-8 text-red-400">Erreur de chargement</div>';
        });
}
</script>

<script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
    }
    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    }

    document.getElementById('sidebarToggle')?.addEventListener('click', openSidebar);
    overlay?.addEventListener('click', closeSidebar);
</script>
@stack('scripts')

</body>
</html>
