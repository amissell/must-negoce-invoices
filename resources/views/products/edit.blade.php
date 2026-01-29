@extends('layouts.app')

@section('title', 'Modifier Produit')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <a href="{{ route('products.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour aux produits
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Modifier Produit</h1>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <form action="{{ route('products.update', $product) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    Nom du produit <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $product->name) }}"
                       required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="reference" class="block text-sm font-medium text-gray-700 mb-1">
                    Référence
                </label>
                <input type="text" 
                       name="reference" 
                       id="reference" 
                       value="{{ old('reference', $product->reference) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('reference') border-red-500 @enderror">
                @error('reference')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="price_ttc" class="block text-sm font-medium text-gray-700 mb-1">
                        Prix TTC (MAD) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="price_ttc" 
                           id="price_ttc" 
                           value="{{ old('price_ttc', $product->price_ttc) }}"
                           step="0.01"
                           min="0.01"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('price_ttc') border-red-500 @enderror">
                    @error('price_ttc')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="unit" class="block text-sm font-medium text-gray-700 mb-1">
                        Unité <span class="text-red-500">*</span>
                    </label>
                    <select name="unit" 
                            id="unit" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('unit') border-red-500 @enderror">
                        <option value="unité" {{ old('unit', $product->unit) == 'unité' ? 'selected' : '' }}>Unité</option>
                        <option value="litre" {{ old('unit', $product->unit) == 'litre' ? 'selected' : '' }}>Litre</option>
                        <option value="kg" {{ old('unit', $product->unit) == 'kg' ? 'selected' : '' }}>Kilogramme</option>
                        <option value="carton" {{ old('unit', $product->unit) == 'carton' ? 'selected' : '' }}>Carton</option>
                        <option value="pack" {{ old('unit', $product->unit) == 'pack' ? 'selected' : '' }}>Pack</option>
                    </select>
                    @error('unit')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" 
                       name="is_active" 
                       id="is_active" 
                       value="1"
                       {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="is_active" class="ml-2 text-sm text-gray-700">
                    Produit actif (disponible pour la génération de factures)
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('products.index') }}" 
                   class="px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    Annuler
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
