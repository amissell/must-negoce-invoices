@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-6">

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-bold mb-4">
            {{ $objectif ? '✏️ Modifier Objectif' : '➕ Nouvel Objectif' }}
        </h2>

        @if(session('success'))
            <div class="mb-4 text-green-600 font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST"
              action="{{ $objectif ? route('objectifs.update', $objectif->id) : route('objectifs.store') }}">

            @csrf
            @if($objectif) @method('PUT') @endif

            {{-- VENDEUR --}}
            <div class="mb-4">
                <label class="block font-semibold mb-1">Vendeur</label>
                <select name="vendeur_nom" class="w-full border rounded p-2">
                    @foreach($vendeurs as $v)
                        <option value="{{ $v }}"
                            {{ ($objectif->vendeur_nom ?? '') == $v ? 'selected' : '' }}>
                            {{ $v }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- MOIS --}}
            <div class="mb-4">
                <label class="block font-semibold mb-1">Mois</label>
                <input type="number" name="mois" min="1" max="12"
                       value="{{ old('mois', $objectif->mois ?? now()->month) }}"
                       class="w-full border rounded p-2">
            </div>

            {{-- ANNEE --}}
            <div class="mb-4">
                <label class="block font-semibold mb-1">Année</label>
                <input type="number" name="annee"
                       value="{{ old('annee', $objectif->annee ?? now()->year) }}"
                       class="w-full border rounded p-2">
            </div>

            {{-- SPARK --}}
            <div class="mb-4">
                <label class="block font-semibold mb-1">Objectif Spark</label>
                <input type="number" name="objectif_spark"
                       value="{{ old('objectif_spark', $objectif->objectif_spark ?? 0) }}"
                       class="w-full border rounded p-2">
            </div>

            {{-- ECOSOLUTIONS --}}
            <div class="mb-4">
                <label class="block font-semibold mb-1">Objectif Ecosolutions</label>
                <input type="number" name="objectif_ecosolutions"
                       value="{{ old('objectif_ecosolutions', $objectif->objectif_ecosolutions ?? 0) }}"
                       class="w-full border rounded p-2">
            </div>

            {{-- TOTAL --}}
            <div class="mb-4">
                <label class="block font-semibold mb-1">Objectif Total</label>
                <input type="number" name="objectif_total"
                       value="{{ old('objectif_total', $objectif->objectif_total ?? 0) }}"
                       class="w-full border rounded p-2">
            </div>

            {{-- PRIME --}}
            <div class="mb-4">
                <label class="block font-semibold mb-1">Prime</label>
                <input type="number" name="prime"
                       value="{{ old('prime', $objectif->prime ?? 0) }}"
                       class="w-full border rounded p-2">
            </div>

            <button class="bg-indigo-600 text-white px-4 py-2 rounded font-bold">
                💾 Enregistrer
            </button>

        </form>
    </div>

</div>
@endsection