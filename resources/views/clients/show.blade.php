@extends('layouts.app')

@section('title', $client->nom)

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('clients.index') }}"
               class="p-2 rounded-xl bg-white card-shadow text-gray-500 hover:text-gray-700 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white text-lg font-bold"
                     style="background: linear-gradient(135deg, #1e3a5f, #3b7cb8)">
                    {{ strtoupper(substr($client->nom, 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $client->nom }}</h1>
                    <p class="text-sm text-gray-500">
                        @if($client->ice) ICE: {{ $client->ice }} @endif
                        @if($client->ville) · {{ $client->ville }} @endif
                    </p>
                </div>
            </div>
        </div>
        <a href="{{ route('clients.edit', $client) }}"
           class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 text-amber-600 border border-amber-200 rounded-xl text-sm font-semibold hover:bg-amber-100 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Modifier
        </a>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl card-shadow p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Facturé</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($totalFacture, 2) }} DH</p>
        </div>
        <div class="bg-white rounded-2xl card-shadow p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Nb. Factures</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $nombreFactures }}</p>
        </div>
        <div class="bg-white rounded-2xl card-shadow p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Téléphone</p>
            <p class="text-lg font-semibold text-gray-700 mt-1">{{ $client->telephone ?? '—' }}</p>
        </div>
        <div class="bg-white rounded-2xl card-shadow p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Email</p>
            <p class="text-sm font-semibold text-gray-700 mt-1 truncate">{{ $client->email ?? '—' }}</p>
        </div>
    </div>

    {{-- Infos Légales --}}
    <div class="bg-white rounded-2xl card-shadow p-6">
        <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">📋 Informations Légales</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            <div>
                <p class="text-xs text-gray-400 mb-1">ICE</p>
                <p class="font-semibold text-gray-700">{{ $client->ice ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">Identifiant Fiscal</p>
                <p class="font-semibold text-gray-700">{{ $client->if_number ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">RC</p>
                <p class="font-semibold text-gray-700">{{ $client->rc ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">CNSS</p>
                <p class="font-semibold text-gray-700">{{ $client->cnss ?? '—' }}</p>
            </div>
        </div>
        @if($client->adresse || $client->ville)
        <div class="pt-4 border-t border-gray-50">
            <p class="text-xs text-gray-400 mb-1">Adresse</p>
            <p class="font-semibold text-gray-700">{{ $client->adresse ?? '' }}{{ $client->ville ? ' — '.$client->ville : '' }}</p>
        </div>
        @endif
    </div>

    {{-- Historique Factures --}}
    <div class="bg-white rounded-2xl card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider">🧾 Historique des Factures</h2>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50">
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">N° Facture</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Date</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Montant TTC</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($invoices as $inv)
                <tr class="hover:bg-blue-50/30 transition-colors">
                    <td class="px-6 py-3">
                        <code class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-lg">
                            {{ $inv->invoice_number ?? '#'.$inv->id }}
                        </code>
                    </td>
                    <td class="px-6 py-3 text-gray-500">{{ $inv->created_at->format('d/m/Y') }}</td>
                    <td class="px-6 py-3 text-right font-semibold text-gray-800">
                        {{ number_format($inv->total_ttc, 2) }} DH
                    </td>
                    <td class="px-6 py-3 text-right">
                        <a href="{{ route('invoices.download', $inv->id) }}"
                           class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-medium">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            PDF
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-12 text-gray-400">
                        <svg class="w-10 h-10 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-sm">Aucune facture pour ce client</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection