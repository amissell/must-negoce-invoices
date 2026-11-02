@extends('layouts.app')
@section('title', 'Rentabilité')
@section('page-title', 'Rentabilité')
@section('content')
@php
    $summary = $analytics['summary'];
    $period = $analytics['period'];
    $selectedDimension = request('dimension', 'product');
    $fmt = fn ($value) => number_format((float) $value, 2, ',', ' ');
    $hasProfitData = ($summary['line_count'] ?? 0) > 0 && ($summary['known_cost_line_count'] ?? 0) > 0;
@endphp
<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4"><div><h1 class="text-2xl font-bold text-gray-900">Rentabilité brute</h1><p class="mt-1 text-sm text-gray-500">Revenu net des remises − coût d’achat connu dans le système local. Les lignes sans coût sont exclues pour éviter toute surestimation.</p></div><div class="flex flex-wrap items-end gap-3"><form method="GET" class="flex flex-wrap items-end gap-3 rounded-xl bg-white p-3 shadow-sm"><label class="text-sm text-gray-600">Du <input type="date" name="from" value="{{ $period['from'] }}" class="ml-1 rounded border-gray-300"></label><label class="text-sm text-gray-600">Au <input type="date" name="to" value="{{ $period['to'] }}" class="ml-1 rounded border-gray-300"></label><label class="text-sm text-gray-600">Dimension<select name="dimension" class="ml-1 rounded border-gray-300"><option value="product" {{ $selectedDimension === 'product' ? 'selected' : '' }}>Produit</option><option value="customer" {{ $selectedDimension === 'customer' ? 'selected' : '' }}>Client</option><option value="salesperson" {{ $selectedDimension === 'salesperson' ? 'selected' : '' }}>Commercial</option></select></label><button class="rounded bg-teal-600 px-3 py-2 text-sm font-medium text-white">Actualiser</button></form><a href="{{ route('api.business-analytics.profitability.details.export', ['dimension' => $selectedDimension, 'from' => $period['from'], 'to' => $period['to']]) }}" class="rounded bg-violet-700 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-violet-800">Exporter détail CSV</a><a href="{{ route('api.business-analytics.profitability.export', ['from' => $period['from'], 'to' => $period['to']]) }}" class="rounded bg-slate-700 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-800">Exporter synthèse CSV</a></div></div>
    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        <strong>Fiabilité :</strong> cette rentabilité est calculée uniquement sur les lignes avec coût connu. Les coûts historiques, stocks, fournisseurs et vendeur Amevo restent indisponibles dans le schéma actuel.
    </div>
    @if (! $hasProfitData)
        <section class="rounded-xl border border-slate-300 bg-slate-100 p-4 text-sm text-slate-700">
            <strong>Données indisponibles :</strong> aucune ligne de facture n’a un coût exploitable pour cette période. Le calcul de rentabilité est volontairement exclu plutôt qu’estimé.
        </section>
    @endif
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4"><div class="rounded-xl bg-white p-5 shadow-sm"><p class="text-xs uppercase text-gray-500">Revenu avec coût connu</p><p class="mt-2 text-2xl font-bold">{{ $fmt($summary['known_cost_revenue']) }}</p></div><div class="rounded-xl bg-white p-5 shadow-sm"><p class="text-xs uppercase text-gray-500">Coût d’achat</p><p class="mt-2 text-2xl font-bold">{{ $fmt($summary['known_cost_purchase_cost']) }}</p></div><div class="rounded-xl bg-white p-5 shadow-sm"><p class="text-xs uppercase text-gray-500">Profit brut</p><p class="mt-2 text-2xl font-bold {{ $summary['gross_profit'] >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ $fmt($summary['gross_profit']) }}</p></div><div class="rounded-xl bg-white p-5 shadow-sm"><p class="text-xs uppercase text-gray-500">Marge brute</p><p class="mt-2 text-2xl font-bold">{{ is_null($summary['gross_margin_percent']) ? 'Données indisponibles' : $summary['gross_margin_percent'].'%' }}</p></div></div>
    <section class="rounded-xl border border-amber-200 bg-amber-50 p-5"><p class="font-semibold text-amber-900">Fiabilité : {{ $summary['confidence'] }}</p><p class="mt-1 text-sm text-amber-800">{{ $analytics['notice'] }} {{ $summary['missing_cost_line_count'] }} ligne(s) sans coût sont exclues. {{ $summary['snapshot_cost_line_count'] }} ligne(s) utilisent un coût figé à la vente; {{ $summary['estimated_cost_line_count'] }} utilisent le coût produit actuel et restent des estimations historiques.</p></section>
    <div class="grid grid-cols-1 gap-6">
        @php
            $dimensionTables = [
                'product' => ['title' => 'Produits les plus rentables', 'rows' => $analytics['products']],
                'customer' => ['title' => 'Clients les plus rentables', 'rows' => $analytics['customers']],
                'salesperson' => ['title' => 'Créateurs de facture les plus rentables', 'rows' => $analytics['salespeople']],
            ];
        @endphp

        @foreach($dimensionTables as $key => $table)
            <section class="rounded-xl bg-white p-6 shadow-sm overflow-x-auto {{ $selectedDimension === $key ? '' : 'hidden' }}">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold">{{ $table['title'] }}</h2>
                    @if($selectedDimension === $key)
                        <a href="{{ route('api.business-analytics.profitability.details.export', ['dimension' => $key, 'from' => $period['from'], 'to' => $period['to']]) }}" class="rounded bg-violet-600 px-2.5 py-1.5 text-xs font-medium text-white hover:bg-violet-700">Exporter {{ strtolower($table['title']) }}</a>
                    @endif
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-gray-500">
                            <th class="py-2">Libellé</th>
                            <th class="py-2 text-right">Profit</th>
                            <th class="py-2 text-right">Marge</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($table['rows'] as $row)
                            <tr class="border-b last:border-0">
                                <td class="py-2 font-medium">{{ $row->label }}</td>
                                <td class="py-2 text-right {{ $row->gross_profit >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ $fmt($row->gross_profit) }}</td>
                                <td class="py-2 text-right">{{ is_null($row->gross_margin_percent) ? '—' : $row->gross_margin_percent.'%' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-gray-400">Données indisponibles : coût d’achat requis.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        @endforeach
    </div>
</div>
@endsection
