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
            <div class="bg-gray-800 text-white p-4 flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold">MUST NEGOCE</h1>
                    <p class="text-sm text-gray-400">Gestion de Factures</p>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm">amailissellav01@gmail.com</span>
                    <button class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded text-sm">
                        Share
                    </button>
                    <button class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded text-sm">
                        🔄 Upgrade
                    </button>
                    <button class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-sm">
                        Publish
                    </button>
                </div>
            </div>

            <div class="p-8">
                <!-- Success Message -->
                @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
                @endif

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
                                <table class="w-full">
                                    <thead class="bg-gray-50 border-b">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">N° Facture</th>
                                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Date</th>
                                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Montant HT</th>
                                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">TVA</th>
                                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Total TTC</th>
                                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Statut</th>
                                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        @forelse($invoices as $invoice)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm">{{ $invoice->invoice_number }}</td>
                                            <td class="px-4 py-3 text-sm">{{ $invoice->invoice_date->format('d F Y') }}</td>
                                            <td class="px-4 py-3 text-sm">{{ number_format($invoice->montant_ht, 2) }} MAD</td>
                                            <td class="px-4 py-3 text-sm">{{ number_format($invoice->tva, 2) }} MAD</td>
                                            <td class="px-4 py-3 text-sm font-semibold">{{ number_format($invoice->total_ttc, 2) }} MAD</td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-green-100 text-green-800">
                                                    🔒 {{ $invoice->status }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <a 
                                                    href="{{ route('invoices.download', $invoice->id) }}" 
                                                    class="inline-flex items-center text-blue-600 hover:text-blue-800"
                                                >
                                                    ⬇️
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                                Aucune facture générée. Commencez par entrer un montant global ci-dessus.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Products -->
                    <div>
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <h2 class="text-lg font-bold">🧹 Produits de Nettoyage</h2>
                                    <p class="text-gray-600 text-sm">{{ $products->count() }} produit(s) disponible(s)</p>
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
</body>
</html>