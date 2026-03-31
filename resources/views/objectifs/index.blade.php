@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-6 px-4">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h2 class="text-2xl font-bold text-gray-800">🎯 Objectifs Vendeurs</h2>
        <a href="{{ route('objectifs.create') }}"
           class="btn-primary text-white px-4 py-2 rounded-lg font-semibold text-sm">
            ➕ Nouvel Objectif
        </a>
    </div>

    {{-- Filtres mois/année --}}
    <form method="GET" action="{{ route('objectifs.index') }}"
          class="bg-white rounded-xl shadow p-4 mb-6 flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Mois</label>
            <select name="mois" class="border rounded-lg px-3 py-2 text-sm">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $mois == $m ? 'selected' : '' }}>
                        {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                    </option>
                @endfor
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Année</label>
            <select name="annee" class="border rounded-lg px-3 py-2 text-sm">
                @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" {{ $annee == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <button type="submit" class="btn-primary text-white px-4 py-2 rounded-lg text-sm font-semibold">
            🔍 Filtrer
        </button>
    </form>

    {{-- Success message --}}
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg font-semibold">
            {{ session('success') }}
        </div>
    @endif

    {{-- Tableau --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Vendeur</th>
                    <th class="px-4 py-3 text-right">Obj. Spark</th>
                    <th class="px-4 py-3 text-right">Obj. Ecosolutions</th>
                    <th class="px-4 py-3 text-right">Obj. Total</th>
                    <th class="px-4 py-3 text-right">Prime</th>
                    <th class="px-4 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($objectifs as $obj)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-semibold">{{ $obj->vendeur_nom }}</td>
                    <td class="px-4 py-3 text-right">{{ number_format($obj->objectif_spark, 0, ',', ' ') }} MAD</td>
                    <td class="px-4 py-3 text-right">{{ number_format($obj->objectif_ecosolutions, 0, ',', ' ') }} MAD</td>
                    <td class="px-4 py-3 text-right font-bold">{{ number_format($obj->objectif_total, 0, ',', ' ') }} MAD</td>
                    <td class="px-4 py-3 text-right text-green-600 font-semibold">{{ number_format($obj->prime, 0, ',', ' ') }} MAD</td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('objectifs.edit', $obj->id) }}"
                           class="text-indigo-600 hover:underline text-xs font-semibold mr-2">✏️ Modifier</a>
                        <form action="{{ route('objectifs.destroy', $obj->id) }}" method="POST" class="inline"
                              onsubmit="return confirm('Supprimer cet objectif ?')">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:underline text-xs font-semibold">🗑️ Suppr</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                        Aucun objectif pour cette période.
                        <a href="{{ route('objectifs.create') }}" class="text-indigo-600 hover:underline">Créer un objectif</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Résumé vendeurs sans objectif --}}
    @php
        $vendeursAvecObjectif = $objectifs->pluck('vendeur_nom')->toArray();
        $vendeursSans = array_diff($vendeurs, $vendeursAvecObjectif);
    @endphp
    @if(count($vendeursSans) > 0)
    <div class="mt-4 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg text-sm">
        ⚠️ <strong>Vendeurs sans objectif ce mois:</strong> {{ implode(', ', $vendeursSans) }}
    </div>
    @endif
</div>
@endsection