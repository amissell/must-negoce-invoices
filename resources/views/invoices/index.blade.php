<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUST NEGOCE - Gestion de Factures</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .sidebar {
            background: #1a1a1a;
        }
        .btn-primary {
            background: #3b82f6;
        }
        .btn-primary:hover {
            background: #2563eb;
        }
        </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Header -->
<header class="bg-white border-b border-gray-100 sticky top-0 z-50 backdrop-blur-sm bg-white/95">
    <div class="max-w-7xl mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl gradient-primary flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="font-display text-xl font-bold text-gray-900">MUST NEGOCE</h1>
                    <p class="text-xs text-gray-500">Gestion de Factures</p>
                </div>
            </div>
            <a href="{{ route('invoices.manual.create') }}" 
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-xl font-medium btn-glow hover:bg-blue-700 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle Facture
            </a>
        </div>
    </div>
</header>
{{-- Add this after your header, before the invoice generator form --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    {{-- Total Invoices --}}
    <div class="stat-card bg-white rounded-xl p-6 card-shadow card-hover animate-fade-in">
        <div class="flex items-center gap-4">
            <div class="stat-icon p-3 rounded-xl bg-blue-50">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <p class="font-display text-2xl font-bold text-gray-900">{{ $invoices->count() }}</p>
                <p class="text-sm text-gray-500">Total factures</p>
            </div>
        </div>
    </div>

    {{-- Automatic Invoices --}}
    <div class="stat-card bg-white rounded-xl p-6 card-shadow card-hover animate-fade-in animate-delay-1">
        <div class="flex items-center gap-4">
            <div class="stat-icon p-3 rounded-xl bg-indigo-50">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="font-display text-2xl font-bold text-gray-900">{{ $invoices->where('invoice_type', 'automatic')->count() }}</p>
                <p class="text-sm text-gray-500">Automatiques</p>
            </div>
        </div>
    </div>

    {{-- Manual Invoices --}}
    <div class="stat-card bg-white rounded-xl p-6 card-shadow card-hover animate-fade-in animate-delay-2">
        <div class="flex items-center gap-4">
            <div class="stat-icon p-3 rounded-xl bg-emerald-50">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <p class="font-display text-2xl font-bold text-gray-900">{{ $invoices->where('invoice_type', 'manual')->count() }}</p>
                <p class="text-sm text-gray-500">Manuelles</p>
            </div>
        </div>
    </div>

    {{-- Total Revenue --}}
    <div class="stat-card bg-white rounded-xl p-6 card-shadow card-hover animate-fade-in animate-delay-3">
        <div class="flex items-center gap-4">
            <div class="stat-icon p-3 rounded-xl bg-amber-50">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <div>
                <p class="font-display text-2xl font-bold text-emerald-600">{{ number_format($invoices->sum('total_ttc'), 0, ',', ' ') }}</p>
                <p class="text-sm text-gray-500">Chiffre d'affaires (MAD)</p>
            </div>
        </div>
    </div>
</div>



                <div class="grid grid-cols-3 gap-8">
                    <!-- Left Column: Generate Invoices -->
                    <div class="col-span-2">
                        <div class="bg-white rounded-lg shadow p-6 mb-6">
                            <h2 class="text-xl font-bold mb-4">⚙️ Générer des Factures</h2>
                            <p class="text-gray-600 mb-6">
                                Entrez un montant global TTC pour générer automatiquement des factures réalisées (±5000 MAD chacune)
                            </p>

                            <form action="{{ route('invoices.generate') }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Montant Global TTC (MAD)
                                    </label>
                                    <div class="flex gap-4">
                                        <input 
                                            type="number" 
                                            name="montant_global_ttc" 
                                            class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Ex: 15000"
                                            required
                                        >
                                        <button 
                                            type="submit" 
                                            class="btn-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition"
                                        >
                                            📋 Aperçu
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Invoice List -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <h2 class="text-xl font-bold">📋 Liste des Factures</h2>
                                    <p class="text-gray-600 text-sm">{{ $invoices->count() }} facture(s) au total</p>
                                </div>
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        placeholder="🔍 Rechercher..."
                                        class="border border-gray-300 rounded-lg px-4 py-2 pl-10 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                </div>
                            </div>

                            <div class="overflow-x-auto">
{{-- Replace your table body --}}
<tbody>
    @forelse($invoices as $index => $invoice)
    <tr class="table-row hover:bg-gray-50 border-b border-gray-100" style="animation-delay: {{ $index * 0.05 }}s">
        <td class="px-4 py-4">
            <div class="flex items-center gap-3">
                <span class="font-semibold text-gray-900">{{ $invoice->invoice_number }}</span>
                @if($invoice->invoice_type === 'manual')
                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">Manuel</span>
                @else
                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700">Auto</span>
                @endif
            </div>
        </td>
        <td class="px-4 py-4 text-sm text-gray-600">{{ $invoice->invoice_date->format('d/m/Y') }}</td>
        <td class="px-4 py-4 text-sm font-mono text-gray-700">{{ number_format($invoice->montant_ht, 2, ',', ' ') }} MAD</td>
        <td class="px-4 py-4 text-sm font-mono text-gray-500">{{ number_format($invoice->tva, 2, ',', ' ') }} MAD</td>
        <td class="px-4 py-4">
            <span class="font-semibold font-mono text-gray-900">{{ number_format($invoice->total_ttc, 2, ',', ' ') }} MAD</span>
        </td>
        <td class="px-4 py-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('invoices.download', $invoice->id) }}" 
                   class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
                   title="Télécharger PDF">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </a>
                <form action="" method="POST" class="inline" onsubmit="return confirm('Supprimer cette facture?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            </div>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="6" class="px-4 py-16 text-center">
            <div class="flex flex-col items-center">
                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">Aucune facture</h3>
                <p class="text-sm text-gray-500">Générez votre première facture ci-dessus</p>
            </div>
        </td>
    </tr>
    @endforelse
</tbody>

                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Products -->
                    <div>
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <h2 class="text-lg font-bold">🧹 Produits de Nettoyage</h2>
                                    <p class="text-gray-600 text-sm"> produit(s) disponible(s)</p>
                                </div>
                                <button 
                                    onclick="document.getElementById('addProductModal').classList.remove('hidden')"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                                >
                                    + Ajouter
                                </button>
                            </div>

                            <div class="space-y-4">
                                @forelse($products as $product)
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="font-semibold text-gray-800">{{ $product->name }}</h3>
                                        <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Êtes-vous sûr?')">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600">Prix TTC</span>
                                        <span class="font-semibold text-blue-600">{{ number_format($product->prix_ttc, 2) }} MAD</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm mt-1">
                                        <span class="text-gray-600">Unité</span>
                                        <span class="text-gray-800">{{ $product->unite }}</span>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-8 text-gray-500">
                                    Aucun produit disponible. Ajoutez votre premier produit.
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Ajouter un Produit</h3>
                <button onclick="document.getElementById('addProductModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">
                    ✕
                </button>
            </div>
            
            <form action="{{ route('products.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Produit</label>
                        <input 
                            type="text" 
                            name="name" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Ex: Emerald Mcconnell"
                            required
                        >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Prix TTC</label>
                        <input 
                            type="number" 
                            step="0.01"
                            name="prix_ttc" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Ex: 65.00"
                            required
                        >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Unité</label>
                        <input 
                            type="text" 
                            name="unite" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            value="unité"
                            required
                        >
                    </div>
                    
                    <div class="flex gap-3 pt-4">
                        <button 
                            type="button"
                            onclick="document.getElementById('addProductModal').classList.add('hidden')"
                            class="flex-1 border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50"
                        >
                            Annuler
                        </button>
                        <button 
                            type="submit" 
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
                        >
                            Ajouter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
        <script>
        // Dropdown toggle script
        document.getElementById('createInvoiceDropdown')?.addEventListener('click', function() {
            const dropdown = document.getElementById('dropdownMenu');
            dropdown.classList.toggle('hidden');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('dropdownMenu');
            const button = document.getElementById('createInvoiceDropdown');
            if (dropdown && button && !button.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });
        </script>
</body>
</html>