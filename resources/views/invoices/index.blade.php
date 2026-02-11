@extends('layouts.app')
@section('title', 'Tableau de Bord')
@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- Welcome Header --}}
        <div class="animate-fade-in">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Bonjour, <span class="text-blue-600">{{ auth()->user()->name }}</span> 👋
                    </h1>
                    <p class="text-gray-600 mt-1 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ \Carbon\Carbon::now()->isoFormat('dddd D MMMM YYYY') }}
                    </p>
                </div>
                <a href="{{ route('invoices.manual.create') }}"
                   class="group inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle Facture
                </a>
            </div>
        </div>

        {{-- ✅ Stats Cards --}}
        @php
            $totalTTC = $invoices->sum('total_ttc');
            $totalHT  = $totalTTC / 1.20;   // extraction HT depuis TTC
            $totalTVA = $totalTTC - $totalHT;
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 animate-slide-up">

            {{-- Total Factures --}}
            <div class="group relative bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-full">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                            </svg>
                            +12%
                        </span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $totalInvoices }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Total Factures</p>
                </div>
            </div>

            {{-- Factures Automatiques --}}
            <div class="group relative bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-50 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $automaticCount }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Factures Automatiques</p>
                </div>
            </div>

            {{-- Factures Manuelles --}}
            <div class="group relative bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $manualCount }}</h3>
                    <p class="text-sm text-gray-500 font-medium">Factures Manuelles</p>
                </div>
            </div>

            {{-- ✅ Chiffre d'Affaires — TTC + HT breakdown --}}
            <div class="group relative bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>

                    {{-- TTC (principal) --}}
                    <h3 class="text-2xl font-bold text-white mb-0.5">
                        {{ number_format($totalTTC, 0, ',', ' ') }}
                        <span class="text-sm font-medium text-amber-100">MAD TTC</span>
                    </h3>

                    {{-- ✅ HT extrait --}}
                    <div class="mt-2 pt-2 border-t border-white/30">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-amber-100 font-medium">Montant HT</span>
                            <span class="text-sm font-bold text-white">
                                {{ number_format($totalHT, 0, ',', ' ') }} MAD
                            </span>
                        </div>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-xs text-amber-100 font-medium">dont TVA 20%</span>
                            <span class="text-xs font-semibold text-amber-200">
                                {{ number_format($totalTVA, 0, ',', ' ') }} MAD
                            </span>
                        </div>
                    </div>

                    <p class="text-xs text-amber-100 font-medium mt-2">Chiffre d'Affaires</p>
                </div>
            </div>
        </div>

        {{-- Invoice Generator --}}
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 animate-slide-up" style="animation-delay: 0.1s">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
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
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Montant Global TTC</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-gray-500 font-medium">MAD</span>
                            </div>
                            <input type="number" name="montant_global_ttc" min="100" step="0.01" required
                                   class="w-full pl-16 pr-4 py-4 text-lg font-semibold rounded-2xl border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all outline-none"
                                   placeholder="25000">
                        </div>
                        <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            Maximum 5000 MAD par facture
                        </p>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="group relative px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 flex items-center gap-3">
                            <svg class="w-5 h-5 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span>Générer</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Invoices Table --}}
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 animate-slide-up" style="animation-delay: 0.2s">
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Liste des Factures</h2>
                        <p class="text-sm text-gray-500 mt-1">{{ $invoices->total() }} factures au total</p>
                    </div>
                    <div class="flex gap-2">
                        <button class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-xl transition-colors duration-200 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            Filtrer
                        </button>
                        <button class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-xl transition-colors duration-200 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Exporter
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">N° Facture</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Date</th>
                            {{-- ✅ Colonnes TTC + HT --}}
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                Montant
                                <span class="block text-gray-400 font-normal normal-case text-xs">TTC / HT</span>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Créé par</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($invoices as $invoice)
                        @php
                            $htRow = ($invoice->montant_ht && $invoice->montant_ht > 0)
                                        ? $invoice->montant_ht
                                        : ($invoice->total_ttc / 1.20);
                        @endphp
                        <tr class="group hover:bg-gradient-to-r hover:from-blue-50/50 hover:to-transparent transition-all duration-200">
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform duration-200">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900">{{ $invoice->invoice_number }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ $invoice->client_name }}</div>
                                @if($invoice->client_email)
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $invoice->client_email }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <span class="text-sm text-gray-600 font-medium">
                                    {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
                                </span>
                            </td>
                            {{-- ✅ TTC + HT sur deux lignes --}}
                            <td class="px-6 py-5 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-gradient-to-r from-green-50 to-emerald-50 text-green-700 text-sm font-bold rounded-lg">
                                    {{ number_format($invoice->total_ttc, 2, ',', ' ') }}
                                    <span class="text-xs">MAD</span>
                                </span>
                                <div class="text-xs text-gray-400 mt-1 pl-1">
                                    HT: <span class="font-semibold text-gray-500">{{ number_format($htRow, 2, ',', ' ') }} MAD</span>
                                </div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl {{ $invoice->creator ? 'bg-gradient-to-br from-purple-500 to-blue-600' : 'bg-gray-400' }} flex items-center justify-center text-white text-sm font-bold shadow-md">
                                        @if($invoice->creator)
                                            {{ strtoupper(substr($invoice->creator->name, 0, 1)) }}
                                        @else
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">
                                            {{ $invoice->creator ? $invoice->creator->name : 'Système' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($invoice->created_at)->format('d/m H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('invoices.edit', $invoice->id) }}"
                                       class="p-2.5 text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all duration-200 group/btn"
                                       title="Modifier">
                                        <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('invoices.download', $invoice->id) }}"
                                       class="p-2.5 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all duration-200 group/btn"
                                       title="Télécharger" target="_blank">
                                        <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </a>
                                    <form action="{{ route('invoices.destroy', $invoice->id) }}"
                                          method="POST" class="inline"
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette facture ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-2.5 text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all duration-200 group/btn"
                                                title="Supprimer">
                                            <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-2">Aucune facture</h3>
                                    <p class="text-gray-500 mb-6">Créez votre première facture pour commencer</p>
                                    <a href="{{ route('invoices.manual.create') }}"
                                       class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-2xl shadow-lg hover:shadow-xl transition-all duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Créer une facture
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($invoices->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                {{ $invoices->links() }}
            </div>
            @endif
        </div>

    </div>
</div>

<style>
@keyframes fade-in  { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
@keyframes slide-up { from { opacity: 0; transform: translateY(30px);  } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in  { animation: fade-in  0.6s ease-out; }
.animate-slide-up { animation: slide-up 0.6s ease-out; animation-fill-mode: both; }
::-webkit-scrollbar       { width: 8px; height: 8px; }
::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
@endsection