@extends('layouts.app')

@section('title', 'Créances')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📊 Suivi des Créances</h1>
            <p class="text-gray-500 mt-1">{{ $clients->count() }} client(s) avec impayés</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('creances.export.excel') }}"
               class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium text-gray-700 transition-all">
                📥 Excel
            </a>
            <a href="{{ route('creances.export.pdf') }}"
               class="flex items-center gap-2 px-4 py-2.5 rounded-xl btn-primary text-white text-sm font-semibold transition-all">
                📥 PDF
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl card-shadow p-5">
            <p class="text-sm text-gray-500">Total Impayé</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ number_format($totalCreances, 2, ',', ' ') }} MAD</p>
        </div>
        <div class="bg-white rounded-2xl card-shadow p-5">
            <p class="text-sm text-gray-500">0-30 jours</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ number_format($total30, 2, ',', ' ') }} MAD</p>
        </div>
        <div class="bg-white rounded-2xl card-shadow p-5">
            <p class="text-sm text-gray-500">31-60 jours</p>
            <p class="text-2xl font-bold text-yellow-600 mt-1">{{ number_format($total60, 2, ',', ' ') }} MAD</p>
        </div>
        <div class="bg-white rounded-2xl card-shadow p-5">
            <p class="text-sm text-gray-500">60+ jours</p>
            <p class="text-2xl font-bold text-red-700 mt-1">{{ number_format($total90, 2, ',', ' ') }} MAD</p>
        </div>
    </div>

    {{-- Panneau AMEVO --}}
    <div class="bg-white rounded-2xl card-shadow p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
            <h2 class="text-lg font-bold text-gray-900">🔄 Reste à Encaisser (AMEVO) — {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</h2>
            <span class="text-xs text-gray-400">Données vendeur AMEVO, à titre de recoupement — sans lien direct avec les créances clients ci-dessous</span>
        </div>

        @if($amevoErreur)
            <div class="flex items-start gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-3">
                <span class="text-red-500 text-lg mt-0.5">🔴</span>
                <div>
                    <p class="font-bold text-red-700 text-sm">Erreur API AMEVO</p>
                    <p class="text-sm text-red-600 mt-1">{{ $amevoErreur }}</p>
                </div>
            </div>
        @elseif($amevoData)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div class="bg-red-50 rounded-xl p-3 text-center">
                    <p class="text-xs text-gray-500">Reste à Payer (Global AMEVO)</p>
                    <p class="text-lg font-bold text-red-600">{{ number_format($amevoData['reste_a_payer'] ?? 0, 0, ',', ' ') }} DH</p>
                </div>
                <div class="bg-indigo-50 rounded-xl p-3 text-center">
                    <p class="text-xs text-gray-500">CA Total AMEVO</p>
                    <p class="text-lg font-bold text-indigo-600">{{ number_format($amevoData['ca_total'] ?? 0, 0, ',', ' ') }} DH</p>
                </div>
                <div class="bg-emerald-50 rounded-xl p-3 text-center">
                    <p class="text-xs text-gray-500">Encaissé AMEVO</p>
                    <p class="text-lg font-bold text-emerald-600">{{ number_format($amevoData['ca_encaisse'] ?? 0, 0, ',', ' ') }} DH</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 text-center">
                    <p class="text-xs text-gray-500">Total Créances (local)</p>
                    <p class="text-lg font-bold text-gray-700">{{ number_format($totalCreances, 0, ',', ' ') }} DH</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-100">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500 uppercase">Vendeur</th>
                            <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500 uppercase">CA Total</th>
                            <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500 uppercase">Encaissé</th>
                            <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500 uppercase">Reste à Payer</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse(($amevoData['vendorCards'] ?? []) as $v)
                            <tr>
                                <td class="px-4 py-2.5 font-medium">{{ $v['nom'] }}</td>
                                <td class="px-4 py-2.5 text-right">{{ number_format($v['ca_total'] ?? 0, 0, ',', ' ') }}</td>
                                <td class="px-4 py-2.5 text-right text-emerald-600">{{ number_format($v['ca_encaisse'] ?? 0, 0, ',', ' ') }}</td>
                                <td class="px-4 py-2.5 text-right font-bold {{ ($v['reste'] ?? 0) > 5000 ? 'text-red-600' : 'text-gray-700' }}">
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
    <div class="bg-white rounded-2xl card-shadow overflow-hidden">
        <div class="p-6 pb-0">
            <h2 class="text-lg font-bold text-gray-900">Détail par Client</h2>
        </div>
        <div class="overflow-x-auto p-6">
            @if($clients->isEmpty())
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl">✅</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucune créance en cours</h3>
                    <p class="text-gray-500">Tous vos clients sont à jour.</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase rounded-l-xl">Client</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Nb Factures</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">0-30j</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">31-60j</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">60j+</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Impayé</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase rounded-r-xl">Risque</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($clients as $data)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $data['client']->nom }}</td>
                            <td class="px-4 py-3 text-center text-gray-600">{{ $data['nb_factures'] }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ number_format($data['j30'], 2, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ number_format($data['j60'], 2, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ number_format($data['j90_plus'], 2, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right font-bold text-gray-900">{{ number_format($data['total_impaye'], 2, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($data['risque'] === 'critique')
                                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">🔴 Critique</span>
                                @elseif($data['risque'] === 'attention')
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">🟡 Attention</span>
                                @else
                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">🟢 Normal</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
