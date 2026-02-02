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