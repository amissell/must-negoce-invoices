@extends('layouts.app')

@section('content')
<div class="w-full">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">📊 Suivi Vendeurs</h2>
        <a href="{{ route('suivi-vendeurs.create') }}"
           class="px-4 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-800 transition font-medium">
            + Nouvelle entrée
        </a>
    </div>

    {{-- Filtres --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <select name="mois" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" @selected($m == $mois)>
                    {{ Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                </option>
            @endfor
        </select>

        <input type="number" name="annee" value="{{ $annee }}"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-24" min="2020">

        <select name="vendeur" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Tous les vendeurs</option>
            @foreach($vendeurs as $v)
                <option value="{{ $v }}" @selected($v == request('vendeur'))>{{ $v }}</option>
            @endforeach
        </select>

        <button type="submit"
                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition text-sm">
            Filtrer
        </button>
    </form>
    {{-- ✅ Global Statistics Cards --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
    <div class="bg-white rounded-xl shadow p-4 text-center border-t-4 border-blue-600">
        <p class="text-xs text-gray-500 font-medium mb-1">💰 CA TOTAL</p>
        <p class="text-xl font-bold text-blue-600">{{ number_format($stats['ca_total'], 0, ',', ' ') }} DH</p>
    </div>

    <div class="bg-white rounded-xl shadow p-4 text-center border-t-4 border-green-500">
        <p class="text-xs text-gray-500 font-medium mb-1">✅ ENCAISSÉ</p>
        <p class="text-xl font-bold text-green-600">{{ number_format($stats['encaisse'], 0, ',', ' ') }} DH</p>
    </div>

    <div class="bg-white rounded-xl shadow p-4 text-center border-t-4 border-red-500">
        <p class="text-xs text-gray-500 font-medium mb-1">⚠️ RESTE À PAYER</p>
        <p class="text-xl font-bold text-red-600">{{ number_format($stats['reste'], 0, ',', ' ') }} DH</p>
    </div>

    <div class="bg-white rounded-xl shadow p-4 text-center border-t-4 border-purple-500">
        <p class="text-xs text-gray-500 font-medium mb-1">📦 BL TOTAL</p>
        <p class="text-xl font-bold text-purple-600">{{ $stats['bl_total'] }}</p>
    </div>

    <div class="bg-white rounded-xl shadow p-4 text-center border-t-4 border-yellow-500">
        <p class="text-xs text-gray-500 font-medium mb-1">📊 TAUX GLOBAL</p>
        <p class="text-xl font-bold {{ $stats['taux_global'] >= 80 ? 'text-green-600' : ($stats['taux_global'] >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
            {{ $stats['taux_global'] }}%
        </p>
    </div>
</div>

    {{-- Cartes par vendeur --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($suiviParVendeur as $nom => $data)
        <div class="bg-white rounded-xl shadow border border-blue-200 overflow-hidden">
            {{-- Card Header --}}
            <div class="bg-blue-700 text-white px-4 py-3 font-bold text-sm tracking-wide">
                {{ strtoupper($nom) }}
            </div>

            {{-- Card Body --}}
            <div class="p-4">
                <table class="w-full text-sm">
                    <tr class="border-b border-gray-100">
                        <td class="py-1 text-gray-600">Total Chiffre</td>
                        <td class="py-1 text-right font-bold">{{ number_format($data['total_chiffre'], 2) }} MAD</td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-1 text-gray-600">Total Payé</td>
                        <td class="py-1 text-right font-bold text-green-600">{{ number_format($data['total_paye'], 2) }} MAD</td>
                    </tr>
                    <tr class="{{ $data['total_reste'] > 0 ? 'bg-red-50' : 'bg-green-50' }} rounded">
                        <td class="py-1 px-1 text-gray-600">Reste à Payer</td>
                        <td class="py-1 px-1 text-right font-bold {{ $data['total_reste'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ number_format($data['total_reste'], 2) }} MAD
                        </td>
                    </tr>
                    <tr>
                        <td class="py-1 text-gray-600">Total BL</td>
                        <td class="py-1 text-right">{{ $data['total_bl'] }}</td>
                    </tr>
                </table>
            </div>

            {{-- Détail journalier --}}
            <div class="border-t border-gray-100 px-4 py-3 bg-gray-50">
                <p class="text-xs text-gray-500 mb-2 font-medium">Détail journalier :</p>
                <table class="w-full text-xs">
                    <thead>
                        <tr class="text-gray-500 border-b border-gray-200">
                            <th class="text-left py-1">Date</th>
                            <th class="text-right py-1">Chiffre</th>
                            <th class="text-right py-1">Payé</th>
                            <th class="text-right py-1">Reste</th>
                            <th class="text-right py-1">BL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['entries'] as $e)
                        <tr class="border-b border-gray-100 hover:bg-white">
                            <td class="py-1">{{ $e->date->format('d/m') }}</td>
                            <td class="py-1 text-right">{{ number_format($e->chiffre, 2) }}</td>
                            <td class="py-1 text-right text-green-600">{{ number_format($e->total_paye, 2) }}</td>
                            <td class="py-1 text-right {{ $e->reste_a_payer > 0 ? 'text-red-500' : 'text-green-600' }}">
                                {{ number_format($e->reste_a_payer, 2) }}
                            </td>
                            <td class="py-1 text-right">{{ $e->bl }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center text-gray-400 py-16 text-lg">
            Aucune entrée pour ce mois.
        </div>
        @endforelse
    </div>
</div>
@endsection