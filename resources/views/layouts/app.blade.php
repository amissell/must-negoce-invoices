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
    {{-- Navigation --}}
    <nav class="gradient-header text-white sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="{{ route('invoices.index') }}" class="flex items-center gap-3">
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
                <a href="{{ route('invoices.index') }}" class="block px-4 py-2 rounded-lg hover:bg-white/10">📊 Tableau de Bord</a>
                <a href="{{ route('products.index') }}" class="block px-4 py-2 rounded-lg hover:bg-white/10">📦 Produits</a>
                <a href="{{ route('invoices.manual.create') }}" class="block px-4 py-2 rounded-lg hover:bg-white/10">✏️ Facture Manuelle</a>
            </div>
        </div>
    </nav>

    {{-- Flash Messages --}}
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

    {{-- Main Content --}}
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
