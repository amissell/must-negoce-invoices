@extends('layouts.app')

@section('title', 'Business Analytics')
@section('page-title', 'Business Analytics')

@section('content')
@php
    $summary = $dataQuality['summary'];
    $period = $dataQuality['period'];
    $hasData = ($summary['checks'] ?? 0) > 0 || ($summary['issues'] ?? 0) > 0 || ($summary['critical'] ?? 0) > 0 || ($summary['warnings'] ?? 0) > 0;
@endphp

<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Business Analytics Foundation</h1>
            <p class="mt-1 text-sm text-gray-500">Qualité des données et limites de calcul — aucune donnée n’est estimée ou inventée.</p>
            <a href="{{ route('business-analytics.commercial.index') }}" class="mt-2 inline-block text-sm font-medium text-teal-700 hover:text-teal-900">Voir l’analyse clients, ventes et paiements →</a>
        </div>
        <form method="GET" class="flex flex-wrap items-end gap-3 rounded-xl bg-white p-3 shadow-sm">
            <label class="text-sm text-gray-600">Du <input type="date" name="from" value="{{ $period['from'] }}" class="ml-1 rounded border-gray-300"></label>
            <label class="text-sm text-gray-600">Au <input type="date" name="to" value="{{ $period['to'] }}" class="ml-1 rounded border-gray-300"></label>
            <button class="rounded bg-teal-600 px-3 py-2 text-sm font-medium text-white">Actualiser</button>
        </form>
    </div>

    @if (! $hasData)
        <section class="rounded-xl border border-slate-300 bg-slate-100 p-4 text-sm text-slate-700">
            <strong>Données indisponibles :</strong> aucune vérification analytique n’a été déclenchée pour cette période. Le module signale explicitement l’absence de données au lieu de conclure à un résultat parfait.
        </section>
    @endif
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
        <div class="rounded-xl bg-white p-5 shadow-sm"><p class="text-xs uppercase text-gray-500">Contrôles</p><p class="mt-2 text-2xl font-bold">{{ $summary['checks'] }}</p></div>
        <div class="rounded-xl bg-white p-5 shadow-sm"><p class="text-xs uppercase text-gray-500">Anomalies</p><p class="mt-2 text-2xl font-bold">{{ $summary['issues'] }}</p></div>
        <div class="rounded-xl bg-red-50 p-5 shadow-sm"><p class="text-xs uppercase text-red-700">Critiques</p><p class="mt-2 text-2xl font-bold text-red-700">{{ $summary['critical'] }}</p></div>
        <div class="rounded-xl bg-amber-50 p-5 shadow-sm"><p class="text-xs uppercase text-amber-700">Avertissements</p><p class="mt-2 text-2xl font-bold text-amber-700">{{ $summary['warnings'] }}</p></div>
    </div>

    <section class="rounded-xl bg-white p-6 shadow-sm overflow-x-auto">
        <h2 class="text-lg font-semibold">Data Quality</h2>
        <p class="mb-4 text-sm text-gray-500">Contrôles appliqués aux factures de la période et aux données maîtres.</p>
        <table class="w-full text-sm">
            <thead><tr class="border-b text-left text-gray-500"><th class="py-2">Contrôle</th><th class="py-2">Résultat</th><th class="py-2">Impact analytique</th></tr></thead>
            <tbody>
                @foreach($dataQuality['checks'] as $check)
                    <tr class="border-b last:border-0"><td class="py-3 font-medium">{{ $check['label'] }}</td><td class="py-3"><span class="rounded px-2 py-1 text-xs {{ $check['count'] === 0 ? 'bg-green-100 text-green-700' : ($check['severity'] === 'critical' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ $check['count'] }} {{ $check['count'] === 0 ? '— conforme' : 'à vérifier' }}</span></td><td class="py-3 text-gray-600">{{ $check['explanation'] }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </section>

    <section class="rounded-xl bg-white p-6 shadow-sm overflow-x-auto">
        <h2 class="text-lg font-semibold">Data Gap Report</h2>
        <p class="mb-4 text-sm text-gray-500">Les indicateurs indisponibles restent explicitement signalés jusqu’à ce que les données requises soient collectées.</p>
        <table class="w-full text-sm">
            <thead><tr class="border-b text-left text-gray-500"><th class="py-2">Question</th><th class="py-2">Statut</th><th class="py-2">Explication</th><th class="py-2">Données requises</th></tr></thead>
            <tbody>
                @foreach($dataGaps as $gap)
                    <tr class="border-b last:border-0"><td class="py-3 font-medium">{{ $gap['question'] }}</td><td class="py-3"><span class="rounded px-2 py-1 text-xs {{ $gap['status'] === 'available' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-700' }}">{{ $gap['status'] === 'available' ? 'Disponible' : 'Données indisponibles' }}</span></td><td class="py-3 text-gray-600">{{ $gap['explanation'] }}</td><td class="py-3 text-gray-600">{{ implode(', ', $gap['required_fields']) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </section>
</div>
@endsection
