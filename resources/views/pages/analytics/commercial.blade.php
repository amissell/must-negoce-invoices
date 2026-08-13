@extends('layouts.app')
@section('title', 'Analyse commerciale')
@section('page-title', 'Analyse commerciale')

@section('content')
@php
    $customers = $analytics['customers'];
    $payments = $analytics['payments'];
    $sales = $analytics['sales'];
    $period = $analytics['period'];
    $fmt = fn ($value) => number_format((float) $value, 2, ',', ' ');
    $hasCommercialData = (($customers['total_revenue'] ?? 0) > 0) || (($payments['recorded_amount'] ?? 0) > 0) || (($sales['top_products'] ?? []) !== []);
@endphp
<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Clients, ventes et paiements</h1>
            <p class="mt-1 text-sm text-gray-500">Analyse fondée sur les factures et paiements réellement enregistrés.</p>
            <a href="{{ route('business-analytics.profitability.index') }}" class="mt-2 inline-block text-sm font-medium text-teal-700 hover:text-teal-900">Voir l’analyse de rentabilité →</a>
        </div>
        <form method="GET" class="flex flex-wrap items-end gap-3 rounded-xl bg-white p-3 shadow-sm">
            <label class="text-sm text-gray-600">Du <input type="date" name="from" value="{{ $period['from'] }}" class="ml-1 rounded border-gray-300"></label>
            <label class="text-sm text-gray-600">Au <input type="date" name="to" value="{{ $period['to'] }}" class="ml-1 rounded border-gray-300"></label>
            <button class="rounded bg-teal-600 px-3 py-2 text-sm font-medium text-white">Actualiser</button>
        </form>
    </div>
    @if (! $hasCommercialData)
        <section class="rounded-xl border border-slate-300 bg-slate-100 p-4 text-sm text-slate-700">
            <strong>Données indisponibles :</strong> aucun chiffre d’affaires, paiement enregistré ou ligne de vente n’a été observé pour cette période. Le module indique explicitement l’absence d’observation au lieu de suggérer une performance nulle.
        </section>
    @endif
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
        <div class="rounded-xl bg-white p-5 shadow-sm"><p class="text-xs uppercase text-gray-500">CA facturé</p><p class="mt-2 text-2xl font-bold">{{ $fmt($customers['total_revenue']) }}</p></div>
        <div class="rounded-xl bg-white p-5 shadow-sm"><p class="text-xs uppercase text-gray-500">Top 5 — concentration CA</p><p class="mt-2 text-2xl font-bold">{{ is_null($customers['top_5_concentration_percent']) ? '—' : $customers['top_5_concentration_percent'].'%' }}</p></div>
        <div class="rounded-xl bg-white p-5 shadow-sm"><p class="text-xs uppercase text-gray-500">Paiements enregistrés</p><p class="mt-2 text-2xl font-bold">{{ $fmt($payments['recorded_amount']) }}</p></div>
        <div class="rounded-xl bg-white p-5 shadow-sm"><p class="text-xs uppercase text-gray-500">Délai moyen enregistré</p><p class="mt-2 text-2xl font-bold">{{ number_format($payments['average_recorded_payment_days'], 1, ',', ' ') }} j</p></div>
    </div>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <section class="rounded-xl bg-white p-6 shadow-sm overflow-x-auto"><h2 class="mb-4 text-lg font-semibold">Clients les plus importants</h2><table class="w-full text-sm"><thead><tr class="border-b text-left text-gray-500"><th class="py-2">Client</th><th class="py-2 text-right">Commandes</th><th class="py-2 text-right">CA</th><th class="py-2 text-right">Exposition impayée</th></tr></thead><tbody>@forelse($customers['top_by_revenue'] as $row)<tr class="border-b last:border-0"><td class="py-2 font-medium">{{ $row->client }}</td><td class="py-2 text-right">{{ $row->orders }}</td><td class="py-2 text-right">{{ $fmt($row->revenue) }}</td><td class="py-2 text-right text-red-700">{{ $fmt($row->outstanding) }}</td></tr>@empty<tr><td colspan="4" class="py-4 text-center text-gray-400">Aucune facture dans la période.</td></tr>@endforelse</tbody></table></section>
        <section class="rounded-xl bg-white p-6 shadow-sm overflow-x-auto"><h2 class="mb-4 text-lg font-semibold">Produits les plus vendus</h2><table class="w-full text-sm"><thead><tr class="border-b text-left text-gray-500"><th class="py-2">Produit</th><th class="py-2 text-right">Quantité</th><th class="py-2 text-right">CA lignes</th></tr></thead><tbody>@forelse($sales['top_products'] as $row)<tr class="border-b last:border-0"><td class="py-2 font-medium">{{ $row->product }}</td><td class="py-2 text-right">{{ $row->quantity }}</td><td class="py-2 text-right">{{ $fmt($row->revenue) }}</td></tr>@empty<tr><td colspan="3" class="py-4 text-center text-gray-400">Aucune ligne vendue dans la période.</td></tr>@endforelse</tbody></table></section>
    </div>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <section class="rounded-xl bg-white p-6 shadow-sm overflow-x-auto"><h2 class="mb-4 text-lg font-semibold">Clients inactifs depuis 90 jours</h2><table class="w-full text-sm"><thead><tr class="border-b text-left text-gray-500"><th class="py-2">Client</th><th class="py-2">Dernier achat</th></tr></thead><tbody>@forelse($customers['inactive_customers'] as $row)<tr class="border-b last:border-0"><td class="py-2 font-medium">{{ $row->client }}</td><td class="py-2">{{ $row->last_purchase_date ?? 'Aucun achat enregistré' }}</td></tr>@empty<tr><td colspan="2" class="py-4 text-center text-gray-400">Aucun client inactif détecté.</td></tr>@endforelse</tbody></table></section>
        <section class="rounded-xl bg-white p-6 shadow-sm overflow-x-auto"><h2 class="mb-4 text-lg font-semibold">Villes par chiffre d’affaires</h2><table class="w-full text-sm"><thead><tr class="border-b text-left text-gray-500"><th class="py-2">Ville</th><th class="py-2 text-right">CA</th></tr></thead><tbody>@forelse($sales['top_cities'] as $row)<tr class="border-b last:border-0"><td class="py-2 font-medium">{{ $row->city }}</td><td class="py-2 text-right">{{ $fmt($row->revenue) }}</td></tr>@empty<tr><td colspan="2" class="py-4 text-center text-gray-400">Aucune ville client renseignée dans la période.</td></tr>@endforelse</tbody></table></section>
    </div>
    <section class="rounded-xl bg-white p-6 shadow-sm"><h2 class="mb-2 text-lg font-semibold">Prix et remises</h2><div class="grid grid-cols-1 gap-4 sm:grid-cols-3"><div><p class="text-xs uppercase text-gray-500">Factures avec remise</p><p class="mt-1 text-xl font-bold">{{ $sales['discounts']['discounted_invoices'] }}</p></div><div><p class="text-xs uppercase text-gray-500">Remise moyenne</p><p class="mt-1 text-xl font-bold">{{ number_format($sales['discounts']['average_discount_percent'], 2, ',', ' ') }}%</p></div><div><p class="text-xs uppercase text-gray-500">Montant total des remises</p><p class="mt-1 text-xl font-bold">{{ $fmt($sales['discounts']['total_discount_amount']) }}</p></div></div><p class="mt-4 text-sm text-amber-700">Les remises observées ne prouvent pas qu’elles ont causé une hausse ou une baisse des ventes.</p></section>
    <section class="rounded-xl bg-slate-50 p-6"><h2 class="mb-2 text-lg font-semibold">Limites d’interprétation</h2><ul class="list-disc space-y-1 pl-5 text-sm text-slate-600">@foreach($analytics['limitations'] as $limitation)<li>{{ $limitation }}</li>@endforeach</ul></section>
</div>
@endsection
