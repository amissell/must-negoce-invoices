@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">📊 Suivi des Créances</h1>
        <div class="flex gap-2">
            <a href="{{ route('creances.export.excel') }}" class="btn btn-success text-sm px-3 py-1 bg-green-600 text-white rounded">📥 Excel</a>
            <a href="{{ route('creances.export.pdf') }}" class="btn btn-danger text-sm px-3 py-1 bg-red-600 text-white rounded">📥 PDF</a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white shadow rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500">Total Impayé</p>
            <p class="text-xl font-bold text-red-600">{{ number_format($totalCreances, 2) }} MAD</p>
        </div>
        <div class="bg-white shadow rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500">0-30 jours</p>
            <p class="text-xl font-bold text-green-600">{{ number_format($total30, 2) }} MAD</p>
        </div>
        <div class="bg-white shadow rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500">31-60 jours</p>
            <p class="text-xl font-bold text-yellow-600">{{ number_format($total60, 2) }} MAD</p>
        </div>
        <div class="bg-white shadow rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500">60+ jours</p>
            <p class="text-xl font-bold text-red-700">{{ number_format($total90, 2) }} MAD</p>
        </div>
    </div>

    {{-- Panneau AMEVO - Reste a encaisser par vendeur (source: API AMEVO, live) --}}
    <div class="bg-white shadow rounded-lg p-4 mb-6">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-lg font-bold">🔄 Reste à Encaisser (AMEVO) — {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</h2>
            <span class="text-xs text-gray-400">Données vendeur AMEVO, à titre de recoupement — sans lien direct avec les créances clients ci-dessous</span>
        </div>

        @if($amevoErreur)
            <div class="flex items-start gap-3 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
                <span class="text-red-500 text-lg mt-0.5">🔴</span>
                <div>
                    <p class="font-bold text-red-700 text-sm">Erreur API AMEVO</p>
                    <p class="text-sm text-red-600 mt-1">{{ $amevoErreur }}</p>
                </div>
            </div>
        @elseif($amevoData)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div class="bg-red-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-500">Reste à Payer (Global AMEVO)</p>
                    <p class="text-lg font-bold text-red-600">{{ number_format($amevoData['reste_a_payer'] ?? 0, 0, ',', ' ') }} DH</p>
                </div>
                <div class="bg-indigo-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-500">CA Total AMEVO</p>
                    <p class="text-lg font-bold text-indigo-600">{{ number_format($amevoData['ca_total'] ?? 0, 0, ',', ' ') }} DH</p>
                </div>
                <div class="bg-emerald-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-500">Encaissé AMEVO</p>
                    <p class="text-lg font-bold text-emerald-600">{{ number_format($amevoData['ca_encaisse'] ?? 0, 0, ',', ' ') }} DH</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-500">Total Créances (local)</p>
                    <p class="text-lg font-bold text-gray-700">{{ number_format($totalCreances, 0, ',', ' ') }} DH</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Vendeur</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">CA Total</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Encaissé</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Reste à Payer</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse(($amevoData['vendorCards'] ?? []) as $v)
                            <tr>
                                <td class="px-4 py-2 font-medium">{{ $v['nom'] }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($v['ca_total'] ?? 0, 0, ',', ' ') }}</td>
                                <td class="px-4 py-2 text-right text-emerald-600">{{ number_format($v['ca_encaisse'] ?? 0, 0, ',', ' ') }}</td>
                                <td class="px-4 py-2 text-right font-bold {{ ($v['reste'] ?? 0) > 5000 ? 'text-red-600' : 'text-gray-700' }}">
                                    {{ number_format($v['reste'] ?? 0, 0, ',', ' ') }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-gray-400 py-4">Aucune donnée vendeur AMEVO pour ce mois.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-400">Aucune donnée AMEVO disponible.</p>
        @endif
    </div>

    {{-- Aging Table --}}
    <div class="bg-white shadow rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Nb Factures</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">0-30j</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">31-60j</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">60j+</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Impayé</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Risque</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($clients as $data)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $data['client']->nom }}</td>
                    <td class="px-4 py-3 text-center">{{ $data['nb_factures'] }}</td>
                    <td class="px-4 py-3 text-right">{{ number_format($data['j30'], 2) }}</td>
                    <td class="px-4 py-3 text-right">{{ number_format($data['j60'], 2) }}</td>
                    <td class="px-4 py-3 text-right">{{ number_format($data['j90_plus'], 2) }}</td>
                    <td class="px-4 py-3 text-right font-bold">{{ number_format($data['total_impaye'], 2) }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($data['risque'] === 'critique')
                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">🔴 Critique</span>
                        @elseif($data['risque'] === 'attention')
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs">🟡 Attention</span>
                        @else
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">🟢 Normal</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
