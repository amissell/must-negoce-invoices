@extends('layouts.app')
@section('title', 'Tableau de Bord - Factures')

@section('content')
<div class="space-y-8">

    <!-- Welcome Header -->
    <div class="animate-fade-in">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-2">
            <div>
                <h1 class="text-4xl font-bold text-gray-900">
                    Bonjour, <span class="text-blue-600">{{ auth()->user()->name }}</span> 👋
                </h1>
                <p class="text-gray-600 mt-2 flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ \Carbon\Carbon::now()->isoFormat('dddd D MMMM YYYY') }}
                </p>
            </div>
            <a href="{{ route('invoices.manual.create') }}"
               class="group inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5">
                <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle Facture
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    @php
        $totalTTC = $invoices->sum('total_ttc');
        $totalHT  = $totalTTC / 1.20;
        $totalTVA = $totalTTC - $totalHT;
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Total Invoices -->
        <div class="group relative bg-white rounded-xl p-6 shadow-sm hover:shadow-lg transition-all border border-gray-100">
            <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-full -mr-12 -mt-12 group-hover:scale-125 transition-transform duration-500 opacity-50"></div>
            <div class="relative">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg mb-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $totalInvoices }}</h3>
                <p class="text-sm text-gray-500 font-medium">Total Factures</p>
            </div>
        </div>

        <!-- Automatic Invoices -->
        <div class="group relative bg-white rounded-xl p-6 shadow-sm hover:shadow-lg transition-all border border-gray-100">
            <div class="absolute top-0 right-0 w-24 h-24 bg-cyan-50 rounded-full -mr-12 -mt-12 group-hover:scale-125 transition-transform duration-500 opacity-50"></div>
            <div class="relative">
                <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-xl flex items-center justify-center shadow-lg mb-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $automaticCount }}</h3>
                <p class="text-sm text-gray-500 font-medium">Factures Automatiques</p>
            </div>
        </div>

        <!-- Manual Invoices -->
        <div class="group relative bg-white rounded-xl p-6 shadow-sm hover:shadow-lg transition-all border border-gray-100">
            <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 rounded-full -mr-12 -mt-12 group-hover:scale-125 transition-transform duration-500 opacity-50"></div>
            <div class="relative">
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg mb-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $manualCount }}</h3>
                <p class="text-sm text-gray-500 font-medium">Factures Manuelles</p>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="group relative bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl p-6 shadow-lg hover:shadow-2xl transition-all overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-white/20 rounded-full -mr-12 -mt-12 group-hover:scale-125 transition-transform duration-500"></div>
            <div class="relative">
                <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center shadow-lg mb-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-white mb-1">
                    {{ number_format($totalTTC, 0, ',', ' ') }}
                    <span class="text-sm font-medium text-amber-100">MAD TTC</span>
                </h3>
                <div class="mt-2 pt-2 border-t border-white/30 space-y-1">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-amber-100">HT</span>
                        <span class="text-sm font-bold text-white">{{ number_format($totalHT, 0, ',', ' ') }} MAD</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-amber-100">TVA (20%)</span>
                        <span class="text-xs font-semibold text-amber-200">{{ number_format($totalTVA, 0, ',', ' ') }} MAD</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Generator -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                    <span class="text-2xl">⚙️</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">Générateur Automatique</h2>
                    <p class="text-blue-100 text-sm mt-0.5">Créez des factures en quelques secondes</p>
                </div>
            </div>
        </div>
        <form action="{{ route('invoices.generate') }}" method="POST" class="p-6">
            @csrf
            <div class="flex flex-col sm:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Montant Global TTC (MAD)</label>
                    <input type="number" name="montant_global_ttc" min="100" step="0.01" required
                           class="w-full px-4 py-3 text-lg font-semibold rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all outline-none"
                           placeholder="Exemple: 25000">
                    <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        Maximum 5000 MAD par facture
                    </p>
                </div>
                <button type="submit" class="group relative px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 flex items-center gap-2 whitespace-nowrap">
                    <svg class="w-5 h-5 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Générer
                </button>
            </div>
        </form>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

        <!-- Table Header -->
        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Liste des Factures</h2>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $invoices->total() }} facture(s)
                            @if(request('search') || request('type') || request('client_id'))
                                <span class="text-blue-600 font-medium">— résultats filtrés</span>
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Search & Filters -->
                <form method="GET" action="{{ route('invoices.index') }}" class="flex flex-col sm:flex-row gap-3">
                    <!-- Search Input -->
                    <div class="relative flex-1">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Rechercher par N° facture ou client..."
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                    </div>

                    <!-- Client Filter -->
                    <div class="relative sm:w-56">
                        <select name="client_id" onchange="this.form.submit()"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white appearance-none cursor-pointer">
                            <option value="">Tous les clients</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->nom }}
                                </option>
                            @endforeach
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>

                    <!-- Type Filters -->
                    <div class="flex gap-2">
                        <a href="{{ route('invoices.index', array_merge(request()->except('type'), [])) }}"
                           class="px-4 py-2.5 rounded-lg text-sm font-semibold border-2 transition-all
                           {{ !request('type') ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300' }}">
                            Tous
                        </a>
                        <a href="{{ route('invoices.index', array_merge(request()->except('type'), ['type' => 'automatic'])) }}"
                           class="px-4 py-2.5 rounded-lg text-sm font-semibold border-2 transition-all
                           {{ request('type') === 'automatic' ? 'border-cyan-600 bg-cyan-600 text-white' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300' }}">
                            ⚡ Auto
                        </a>
                        <a href="{{ route('invoices.index', array_merge(request()->except('type'), ['type' => 'manual'])) }}"
                           class="px-4 py-2.5 rounded-lg text-sm font-semibold border-2 transition-all
                           {{ request('type') === 'manual' ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300' }}">
                            ✏️ Manuel
                        </a>

                        <!-- Search Button -->
                        <button type="submit"
                                class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-all">
                            Chercher
                        </button>

                        <!-- Clear Filters -->
                        @if(request('search') || request('type') || request('client_id'))
                        <a href="{{ route('invoices.index') }}"
                           class="px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold rounded-lg transition-all border border-red-100"
                           title="Effacer les filtres">
                            ✕ Réinitialiser
                        </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">N° Facture</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Montant TTC</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Créé par</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($invoices as $invoice)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-mono font-semibold text-blue-600">
                            #{{ $invoice->invoice_number }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($invoice->is_automatic)
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-cyan-100 text-cyan-700 text-xs font-semibold rounded-full">
                                    ⚡ Auto
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-full">
                                    ✏️ Manuel
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $invoice->client->nom ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-gray-900">
                            {{ number_format($invoice->total_ttc, 2, ',', ' ') }} MAD
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $invoice->user->name ?? 'Système' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-right">
                            <div class="flex justify-end gap-2">
                                @if(!$invoice->is_automatic)
                                <a href="{{ route('invoices.edit', $invoice->id) }}"
                                   class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                   title="Modifier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endif
                                <a href="{{ route('invoices.download', $invoice->id) }}"
                                   class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                                   title="Télécharger PDF">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </a>
                                @can('delete', $invoice)
                                <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST"
                                      onsubmit="return confirm('Supprimer la facture #{{ addslashes($invoice->invoice_number) }} ? Cette action est irréversible.')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                            title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="font-medium">Aucune facture trouvée</p>
                            <p class="text-sm">Créez une nouvelle facture pour commencer</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($invoices->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
