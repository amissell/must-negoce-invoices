@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">📊 Comparaison de Périodes</h1>

    {{-- Filtre --}}
    <form method="GET" action="{{ route('suivi-vendeurs.comparaison') }}" class="flex flex-wrap gap-4 mb-6 items-end">
        <div>
            <label class="text-sm text-gray-600">Période 1</label>
            <div class="flex gap-1">
                <select name="mois1" class="border rounded px-2 py-1">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $mois1 == $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endfor
                </select>
                <input type="number" name="annee1" value="{{ $annee1 }}" class="border rounded px-2 py-1 w-20">
            </div>
        </div>
        <div>
            <label class="text-sm text-gray-600">Période 2</label>
            <div class="flex gap-1">
                <select name="mois2" class="border rounded px-2 py-1">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $mois2 == $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endfor
                </select>
                <input type="number" name="annee2" value="{{ $annee2 }}" class="border rounded px-2 py-1 w-20">
            </div>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-1 rounded">Comparer</button>
    </form>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white shadow rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500">Évolution CA</p>
            <p class="text-2xl font-bold {{ $evolution['ca'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ $evolution['ca'] >= 0 ? '+' : '' }}{{ $evolution['ca'] }}%
            </p>
        </div>
        <div class="bg-white shadow rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500">Évolution Encaissé</p>
            <p class="text-2xl font-bold {{ $evolution['encaisse'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ $evolution['encaisse'] >= 0 ? '+' : '' }}{{ $evolution['encaisse'] }}%
            </p>
        </div>
        <div class="bg-white shadow rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500">Évolution Drop Size</p>
            <p class="text-2xl font-bold {{ $evolution['drops'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ $evolution['drops'] >= 0 ? '+' : '' }}{{ $evolution['drops'] }}%
            </p>
        </div>
    </div>

    {{-- Table comparaison --}}
    <div class="bg-white shadow rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Métrique</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Période 1 ({{ $mois1 }}/{{ $annee1 }})</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Période 2 ({{ $mois2 }}/{{ $annee2 }})</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr>
                    <td class="px-4 py-3">Chiffre d'affaires</td>
                    <td class="px-4 py-3 text-right">{{ number_format($periode1['ca'], 2) }} MAD</td>
                    <td class="px-4 py-3 text-right">{{ number_format($periode2['ca'], 2) }} MAD</td>
                </tr>
                <tr>
                    <td class="px-4 py-3">Encaissé</td>
                    <td class="px-4 py-3 text-right">{{ number_format($periode1['encaisse'], 2) }} MAD</td>
                    <td class="px-4 py-3 text-right">{{ number_format($periode2['encaisse'], 2) }} MAD</td>
                </tr>
                <tr>
                    <td class="px-4 py-3">Reste à payer</td>
                    <td class="px-4 py-3 text-right">{{ number_format($periode1['reste'], 2) }} MAD</td>
                    <td class="px-4 py-3 text-right">{{ number_format($periode2['reste'], 2) }} MAD</td>
                </tr>
                <tr>
                    <td class="px-4 py-3">BL total</td>
                    <td class="px-4 py-3 text-right">{{ $periode1['bl'] }}</td>
                    <td class="px-4 py-3 text-right">{{ $periode2['bl'] }}</td>
                </tr>
                <tr>
                    <td class="px-4 py-3">Drop Size</td>
                    <td class="px-4 py-3 text-right">{{ number_format($periode1['drops'], 2) }} MAD</td>
                    <td class="px-4 py-3 text-right">{{ number_format($periode2['drops'], 2) }} MAD</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
