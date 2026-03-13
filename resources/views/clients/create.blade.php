@extends('layouts.app')

@section('title', 'Nouveau Client')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('clients.index') }}"
           class="p-2 rounded-xl bg-white card-shadow text-gray-500 hover:text-gray-700 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Nouveau Client</h1>
            <p class="text-sm text-gray-500 mt-0.5">Ajouter un client à la base de données</p>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="bg-white rounded-2xl card-shadow p-6">
        <form action="{{ route('clients.store') }}" method="POST" class="space-y-5">
            @csrf

            {{-- Nom --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Raison Sociale <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nom" value="{{ old('nom') }}" required
                       placeholder="Ex: SARL Atlas Trading"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nom') border-red-400 @enderror">
                @error('nom')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- ICE + IF --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">ICE</label>
                    <input type="text" name="ice" value="{{ old('ice') }}"
                           placeholder="000123456789012"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('ice') border-red-400 @enderror">
                    @error('ice')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Identifiant Fiscal (IF)</label>
                    <input type="text" name="if_number" value="{{ old('if_number') }}"
                           placeholder="12345678"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            {{-- RC + CNSS --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">RC</label>
                    <input type="text" name="rc" value="{{ old('rc') }}"
                           placeholder="RC 12345"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">CNSS</label>
                    <input type="text" name="cnss" value="{{ old('cnss') }}"
                           placeholder="1234567"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            {{-- Téléphone + Email --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Téléphone</label>
                    <input type="text" name="telephone" value="{{ old('telephone') }}"
                           placeholder="06 00 00 00 00"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           placeholder="contact@client.ma"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            {{-- Adresse + Ville --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Adresse</label>
                    <textarea name="adresse" rows="2" placeholder="Rue, quartier..."
                              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('adresse') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Ville</label>
                    <input type="text" name="ville" value="{{ old('ville') }}"
                           placeholder="Casablanca"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="btn-primary text-white px-6 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer
                </button>
                <a href="{{ route('clients.index') }}"
                   class="px-6 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition-all">
                    Annuler
                </a>
            </div>
        </form>
    </div>

</div>
@endsection