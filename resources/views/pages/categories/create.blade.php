@extends('layouts.app')

@section('title', 'Nouvelle Catégorie')

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('categories.index') }}"
           class="p-2 hover:bg-gray-100 rounded-xl transition-all text-gray-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📦 Nouvelle Catégorie</h1>
            <p class="text-sm text-gray-500 mt-0.5">Ajouter une catégorie manuellement</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('categories.store') }}" class="space-y-5">
            @csrf

            {{-- Nom --}}
            <div>
                <label for="nom" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Nom de la catégorie <span class="text-red-500">*</span>
                </label>
                <input type="text" id="nom" name="nom"
                       value="{{ old('nom') }}"
                       placeholder="Ex: LAIT, FROMAGE, EPICES..."
                       class="w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                              {{ $errors->has('nom') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                @error('nom')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- AMEVO ID --}}
            <div>
                <label for="amevo_id" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    ID AMEVO
                    <span class="text-gray-400 font-normal">(optionnel — rempli automatiquement par l'import)</span>
                </label>
                <input type="text" id="amevo_id" name="amevo_id"
                       value="{{ old('amevo_id') }}"
                       placeholder="Ex: 15"
                       class="w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono
                              {{ $errors->has('amevo_id') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                @error('amevo_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-400 mt-1">
                    Laissez vide si vous ne connaissez pas encore l'ID AMEVO de cette catégorie.
                </p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="btn-primary text-white px-6 py-2.5 rounded-xl text-sm font-semibold flex-1 transition-all">
                    ✅ Enregistrer
                </button>
                <a href="{{ route('categories.index') }}"
                   class="px-6 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-all text-center">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection