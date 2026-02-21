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
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen">

{{-- ══════════════════════════════════════════════════════════
     NAVBAR
══════════════════════════════════════════════════════════ --}}
<nav class="gradient-header text-white sticky top-0 z-50">
    <div class="max-w-full px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <a href="{{ auth()->user()->isAdmin() ? route('invoices.index') : route('suivi-vendeurs.index') }}"
               class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold">MUST NEGOCE</h1>
                    <p class="text-xs text-white/60">Gestion de Factures</p>
                </div>
            </a>

            {{-- Navigation Links --}}
            <div class="hidden md:flex items-center gap-1">

                {{-- ADMIN ONLY links --}}
                @if(auth()->user()->isAdmin())
                <a href="{{ route('invoices.index') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('invoices.index') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    📊 Tableau de Bord
                </a>
                <a href="{{ route('products.index') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('products.*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    📦 Produits
                </a>
                <a href="{{ route('invoices.manual.create') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('invoices.manual.*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    ✏️ Facture Manuelle
                </a>
                @endif

                {{-- Suivi Vendeurs — visible to ALL authenticated users --}}
                <a href="{{ route('suivi-vendeurs.index') }}"
                   class="relative flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all
                          {{ request()->routeIs('suivi-vendeurs.*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Suivi Vendeurs
                    @if(isset($sidebarStats) && $sidebarStats->total_reste > 5000)
                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold
                                 rounded-full flex items-center justify-center animate-pulse">!</span>
                    @endif
                </a>

                {{-- ADMIN ONLY: Users management --}}
                @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.users') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('admin.users*') ? 'bg-white/20' : 'hover:bg-white/10' }}">
                    👥 Utilisateurs
                </a>
                @endif

                {{-- User Menu --}}
                <div class="ml-2 relative group">
                    <button class="flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-white/10 transition-all">
                        <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                            <span class="text-sm font-semibold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                        </div>
                        <span class="text-sm font-medium">{{ auth()->user()->name }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
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
            </div>

            {{-- Mobile Menu Button --}}
            <button id="mobileMenuBtn" class="md:hidden p-2 rounded-lg hover:bg-white/10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div id="mobileMenu" class="hidden md:hidden border-t border-white/10">
        <div class="px-4 py-3 space-y-2">
            @if(auth()->user()->isAdmin())
            <a href="{{ route('invoices.index') }}" class="block px-4 py-2 rounded-lg hover:bg-white/10">📊 Tableau de Bord</a>
            <a href="{{ route('products.index') }}" class="block px-4 py-2 rounded-lg hover:bg-white/10">📦 Produits</a>
            <a href="{{ route('invoices.manual.create') }}" class="block px-4 py-2 rounded-lg hover:bg-white/10">✏️ Facture Manuelle</a>
            @endif

            <a href="{{ route('suivi-vendeurs.index') }}" class="flex items-center justify-between px-4 py-2 rounded-lg hover:bg-white/10">
                <span>👥 Suivi Vendeurs</span>
                @if(isset($sidebarStats) && $sidebarStats->total_reste > 5000)
                <span class="w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center">!</span>
                @endif
            </a>

            @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.users') }}" class="block px-4 py-2 rounded-lg hover:bg-white/10">👥 Utilisateurs</a>
            @endif

            <div class="border-t border-white/10 pt-3 mt-3">
                <div class="px-4 py-2 text-white/80 text-sm">
                    <p class="font-semibold">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-white/60">{{ auth()->user()->email }}</p>
                    <span class="text-xs font-bold {{ auth()->user()->isAdmin() ? 'text-indigo-300' : 'text-amber-300' }}">
                        {{ ucfirst(auth()->user()->role) }}
                    </span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 rounded-lg hover:bg-white/10 text-red-300 hover:text-red-200 transition-all">
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

{{-- ══════════════════════════════════════════════════════════
     FLASH MESSAGES
══════════════════════════════════════════════════════════ --}}
@if(session('success'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    </div>
@endif

@if(session('error'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
            {{ session('error') }}
        </div>
    </div>
@endif

{{-- ══════════════════════════════════════════════════════════
     MAIN CONTENT
══════════════════════════════════════════════════════════ --}}
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @yield('content')
</main>

<script>
    document.getElementById('mobileMenuBtn')?.addEventListener('click', function() {
        document.getElementById('mobileMenu').classList.toggle('hidden');
    });
</script>
@stack('scripts')

</body>
</html>