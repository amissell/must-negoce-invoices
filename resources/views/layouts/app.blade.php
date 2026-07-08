<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MUST NEGOCE') - Gestion de Factures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+qdLa+GQYdQ3e5FzC3S1adx0EC4U8g5P4xx8E3P7y0vhu4CpC3kxv2Hr+8dWZ3j6" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color-scheme: light;
            font-size: 15px;
        }
        body {
            background-color: #f8fafc;
            min-height: 100vh;
        }
        .sidebar {
            width: 280px;
            min-width: 280px;
            background: linear-gradient(180deg, #0d6efd 0%, #0b5ed7 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.88);
            transition: background .2s ease, color .2s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255,255,255,.12);
        }
        .sidebar .nav-link svg {
            width: 1.25rem;
            height: 1.25rem;
            margin-right: .5rem;
        }
        .content-wrapper {
            margin-left: 0;
            transition: margin .3s ease;
        }
        @media (min-width: 768px) {
            .content-wrapper {
                margin-left: 280px;
            }
        }
        .bg-brand {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        }
        .card-glass {
            background: rgba(255,255,255,.98);
            border: 1px solid rgba(209, 213, 219, 0.4);
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.08);
        }
        .sidebar-close-btn {
            background: transparent;
            border: none;
            color: rgba(255,255,255,.9);
        }
        .sidebar-backdrop {
            background: rgba(0,0,0,.45);
        }
        .navbar-brand strong {
            letter-spacing: .02em;
        }
    </style>
    @stack('styles')
</head>
<body>

<div class="d-flex min-vh-100">

    {{-- ══════════════════════════════════════════════════════════
         SIDEBAR
    ══════════════════════════════════════════════════════════ --}}
    <aside id="sidebar"
           class="sidebar text-white position-fixed top-0 start-0 vh-100 overflow-auto d-md-block d-none">

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

                <a href="#"
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

                <a href="#"
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
                <a href="#"
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
        <div class="position-absolute bottom-0 start-0 w-100 p-3 border-top border-white-25">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-white-10 rounded-3 p-2 d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                    <span class="text-white fw-semibold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                </div>
                <div class="text-white small">
                    <div class="fw-semibold">{{ auth()->user()->name }}</div>
                    <div class="text-white-75">{{ ucfirst(auth()->user()->role) }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm w-100">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Déconnexion
                </button>
            </form>
        </div>
    </aside>

    {{-- Overlay for mobile, click to close sidebar --}}
    <div id="sidebarBackdrop" class="d-none position-fixed top-0 start-0 w-100 h-100 sidebar-backdrop d-md-none"></div>

    {{-- ══════════════════════════════════════════════════════════
         MAIN COLUMN
    ══════════════════════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Mobile top bar --}}
        <div class="d-md-none position-sticky top-0 zindex-sticky bg-brand text-white d-flex align-items-center justify-content-between px-3 py-2 shadow-sm">
            <button id="sidebarToggle" class="btn btn-link text-white p-0">
                <i class="bi bi-list fs-4"></i>
            </button>
            <span class="fw-bold">MUST NEGOCE</span>
            <div style="width:2.5rem;"></div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             FLASH MESSAGES
        ══════════════════════════════════════════════════════════ --}}
        <div class="content-wrapper">
            <div class="container-fluid py-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- ══════════════════════════════════════════════════════════
                     MAIN CONTENT
                ══════════════════════════════════════════════════════════ --}}
                <main class="pb-5">
                    @yield('content')
                </main>
            </div>
        </div>

    </div>
</div>

{{-- Statistics Modal --}}
<div id="statsModal" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="z-index:1050;">
    <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" onclick="closeStatsModal()"></div>
    <div class="position-absolute top-50 start-50 translate-middle w-100" style="max-width: 680px;">
        <div class="bg-white rounded-4 shadow-lg border border-200 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h5 mb-0">📈 Statistiques du Jour</h2>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="closeStatsModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="mb-4">
                <input type="date" id="statsDate" value="{{ now()->toDateString() }}"
                    onchange="loadStats()"
                    class="form-control form-control-sm">
            </div>
            <div id="statsContent">
                <div class="text-center py-4 text-secondary">Chargement...</div>
            </div>
        </div>
    </div>
</div>

<script>
function openStatsModal() {
    document.getElementById('statsModal').classList.remove('d-none');
    loadStats();
}
function closeStatsModal() {
    document.getElementById('statsModal').classList.add('d-none');
}
function loadStats() {
    const date = document.getElementById('statsDate').value;
    document.getElementById('statsContent').innerHTML = '<div class="text-center py-4 text-secondary">Chargement...</div>';
    fetch(`/statistics/daily?date=${date}`)
        .then(r => r.json())
        .then(data => {
            if (data.stats.length === 0) {
                document.getElementById('statsContent').innerHTML =
                    '<div class="text-center py-4 text-secondary">Aucune vente pour cette date</div>';
                return;
            }
            let totalCA = 0, totalQte = 0, rows = '';
            data.stats.forEach(s => {
                totalCA += parseFloat(s.total_ca);
                totalQte += parseInt(s.total_quantite);
                rows += `
                    <tr>
                        <td class="py-2 px-3 text-sm text-secondary">${s.categorie}</td>
                        <td class="py-2 px-3 text-sm text-center text-secondary">${s.total_quantite}</td>
                        <td class="py-2 px-3 text-sm text-end text-success">${parseFloat(s.total_ca).toLocaleString('fr-FR')} DH</td>
                    </tr>`;
            });
            document.getElementById('statsContent').innerHTML = `
                <div class="table-responsive rounded-3 border border-200">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Catégorie</th>
                                <th class="text-center">Quantité</th>
                                <th class="text-end">CA</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                        <tfoot class="table-secondary fw-semibold">
                            <tr>
                                <td>Total</td>
                                <td class="text-center">${totalQte}</td>
                                <td class="text-end">${totalCA.toLocaleString('fr-FR')} DH</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>`;
        })
        .catch(() => {
            document.getElementById('statsContent').innerHTML =
                '<div class="text-center py-4 text-danger">Erreur de chargement</div>';
        });
}
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-zQYYfd490+Qb0TupbXzHZZR8xfYvOLqvHGvQ8BDzaePUnh3ct78R+gXktv6cWR3s" crossorigin="anonymous"></script>
    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');
        const contentWrapper = document.querySelector('.content-wrapper');

        function openSidebar() {
            sidebar.classList.add('d-block');
            sidebarBackdrop.classList.remove('d-none');
        }
        function closeSidebar() {
            sidebar.classList.remove('d-block');
            sidebarBackdrop.classList.add('d-none');
        }

        sidebarToggle?.addEventListener('click', function () {
            if (sidebar.classList.contains('d-block')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        sidebarBackdrop?.addEventListener('click', closeSidebar);
    </script>
    @stack('scripts')

</body>
</html>
