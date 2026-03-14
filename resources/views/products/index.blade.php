@extends('layouts.app')

@section('title', 'Produits')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📦 Produits de Nettoyage</h1>
            <p class="text-gray-500 mt-1">{{ $totalProducts }} produit(s) disponible(s)</p>
        </div>
        <button onclick="document.getElementById('addProductModal').classList.remove('hidden')"
                class="btn-primary text-white px-6 py-3 rounded-xl font-semibold flex items-center gap-2 shadow-lg hover:shadow-xl transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajouter un Produit
        </button>
    </div>

    <div class="bg-white rounded-2xl card-shadow p-4">
        <form method="GET" action="{{ route('products.index') }}" class="flex gap-3">
            <div class="flex-1 relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Rechercher un produit..."
                       class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <button type="submit" class="btn-primary text-white px-6 py-3 rounded-xl font-medium">
                Rechercher
            </button>
            @if(request('search'))
                <a href="{{ route('products.index') }}" class="px-6 py-3 rounded-xl border border-gray-200 hover:bg-gray-50 font-medium">
                    Réinitialiser
                </a>
            @endif
        </form>
    </div>

    {{-- Products Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($products as $product)
            <div class="bg-white rounded-2xl card-shadow p-5 hover:shadow-lg transition-all group">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                        <span class="text-2xl">🧴</span>
                    </div>
                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" 
                          onsubmit="return confirm('Supprimer ce produit?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg opacity-0 group-hover:opacity-100 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">{{ $product->name }}</h3>
                <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                    <div>
                        <p class="text-xs text-gray-500">Prix TTC</p>
                        <p class="text-lg font-bold text-blue-600">{{ number_format($product->prix_ttc, 2, ',', ' ') }} MAD</p>
                    </div>
                    <span class="px-3 py-1 bg-gray-100 text-gray-600 text-sm rounded-full">{{ $product->unite }}</span>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-white rounded-2xl card-shadow p-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl">📦</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun produit</h3>
                    <p class="text-gray-500 mb-4">Commencez par ajouter vos produits de nettoyage</p>
                    <button onclick="document.getElementById('addProductModal').classList.remove('hidden')"
                            class="btn-primary text-white px-6 py-2 rounded-lg font-medium">
                        Ajouter un produit
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $products->links() }}
    </div>
</div>

<div id="addProductModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-lg font-bold">Ajouter un Produit</h3>
            <button onclick="document.getElementById('addProductModal').classList.add('hidden')" 
                    class="p-2 hover:bg-gray-100 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form action="{{ route('products.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nom du Produit</label>
                <input type="text" name="name" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500"
                       placeholder="Ex: Eau de Javel 5L">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Prix TTC (MAD)</label>
                    <input type="number" name="prix_ttc" step="0.01" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500"
                           placeholder="0.00">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Unité</label>
                    <select name="unite" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500">
                        <option value="unité">Unité</option>
                        <option value="litre">Litre</option>
                        <option value="kg">Kilogramme</option>
                        <option value="carton">Carton</option>
                        <option value="pack">Pack</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="document.getElementById('addProductModal').classList.add('hidden')"
                        class="flex-1 px-4 py-3 rounded-xl border border-gray-200 font-medium hover:bg-gray-50">
                    Annuler
                </button>
                <button type="submit" class="flex-1 btn-primary text-white px-4 py-3 rounded-xl font-medium">
                    Ajouter
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
