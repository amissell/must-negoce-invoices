<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MUST NEGOCE - Gestion de Factures')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap');

:root {
    --primary: #3b5bdb;
    --primary-light: #4c6ef5;
    --accent: #20c997;
    --invoice-automatic: #3b5bdb;
    --invoice-manual: #20c997;
}

body {
    font-family: 'Inter', sans-serif;
}

.font-display {
    font-family: 'Space Grotesk', sans-serif;
}

/* Card shadows */
.card-shadow {
    box-shadow: 0 4px 20px -4px rgba(30, 41, 59, 0.08);
}

.card-hover {
    transition: all 0.3s ease;
}
.card-hover:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px -12px rgba(30, 41, 59, 0.15);
}

/* Animations */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fadeInUp 0.4s ease-out forwards;
}

.animate-delay-1 { animation-delay: 0.1s; }
.animate-delay-2 { animation-delay: 0.2s; }
.animate-delay-3 { animation-delay: 0.3s; }

/* Gradient backgrounds */
.gradient-primary {
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
}

.gradient-accent {
    background: linear-gradient(135deg, var(--accent), #38d9a9);
}

/* Stats card icon hover */
.stat-icon {
    transition: transform 0.3s ease;
}
.stat-card:hover .stat-icon {
    transform: scale(1.1);
}

/* Button glow effect */
.btn-glow {
    box-shadow: 0 4px 15px -3px rgba(59, 91, 219, 0.4);
    transition: all 0.3s ease;
}
.btn-glow:hover {
    box-shadow: 0 8px 25px -5px rgba(59, 91, 219, 0.5);
}

/* Table row animation */
.table-row {
    opacity: 0;
    animation: fadeInUp 0.3s ease-out forwards;
}

    </style>
    @stack('styles')
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <div class="bg-gray-800 text-white p-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold">MUST NEGOCE</h1>
            <p class="text-sm text-gray-400">Gestion de Factures</p>
        </div>
        <div class="flex items-center space-x-4">
            <a href="{{ route('invoices.index') }}" 
               class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                ← Retour aux factures
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container mx-auto p-8">
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>