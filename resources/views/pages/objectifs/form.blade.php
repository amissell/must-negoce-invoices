@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto py-6 px-4">
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-bold mb-4">
            {{ $objectif ? '✏️ Modifier Objectif' : '➕ Nouvel Objectif' }}
        </h2>
        @if(session('success'))
            <div class="mb-4 text-green-600 font-semibold">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <ul class="list-disc pl-5 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST"
              action="{{ $objectif ? route('objectifs.update', $objectif->id) : route('objectifs.store') }}">
            @csrf
            @if($objectif)
                @method('PUT')
            @endif
            <div class="mb-4">
                <label class="block font-semibold mb-1">Vendeur</label>
                <select name="vendeur_nom" class="w-full border rounded p-2">
                    @foreach($vendeurs as $v)
                        <option value="{{ $v }}"
                            {{ old('vendeur_nom', $objectif->vendeur_nom ?? '') == $v ? 'selected' : '' }}>
                            {{ $v }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1">Mois</label>
                <input type="number" name="mois" min="1" max="12"
                       value="{{ old('mois', $objectif->mois ?? now()->month) }}"
                       class="w-full border rounded p-2">
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1">Année</label>
                <input type="number" name="annee"
                       value="{{ old('annee', $objectif->annee ?? now()->year) }}"
                       class="w-full border rounded p-2">
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-1">Objectif Chiffre d'affaire</label>
                <input type="number" step="0.01" name="objectif_ca"
                       value="{{ old('objectif_ca', $objectif->objectif_ca ?? 0) }}"
                       class="w-full border rounded p-2">
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1">Objectif BL</label>
                <input type="number" name="objectif_bl"
                       value="{{ old('objectif_bl', $objectif->objectif_bl ?? 0) }}"
                       class="w-full border rounded p-2">
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1">Prime</label>
                <input type="number" step="0.01" name="prime"
                       value="{{ old('prime', $objectif->prime ?? 300) }}"
                       class="w-full border rounded p-2">
            </div>
            <button class="bg-indigo-600 text-white px-4 py-2 rounded font-bold">
                💾 Enregistrer
            </button>
        </form>
    </div>

</div>
@endsection
