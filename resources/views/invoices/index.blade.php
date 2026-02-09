@extends('layouts.app')

@section('title', 'Tableau de Bord')

@section('content')
<div class="space-y-6">
    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl card-shadow p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalInvoices }}</p>
                    <p class="text-sm text-gray-500">Total Factures</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl card-shadow p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-cyan-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $automaticCount }}</p>
                    <p class="text-sm text-gray-500">Automatiques</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl card-shadow p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $manualCount }}</p>
                    <p class="text-sm text-gray-500">Manuelles</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl card-shadow p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($invoices->sum('total_ttc'), 0, ',', ' ') }}</p>
                    <p class="text-sm text-gray-500">Chiffre d'affaires (MAD)</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Invoice Generator --}}
    <div class="bg-white rounded-2xl card-shadow overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <span class="text-xl">⚙️</span> Générer des Factures Automatiques
            </h2>
            <p class="text-sm text-gray-500 mt-1">Entrez un montant global TTC pour générer des factures réalistes (≤5000 MAD chacune)</p>
        </div>
        <form action="{{ route('invoices.generate') }}" method="POST" class="p-6">
            @csrf
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Montant Global TTC (MAD)</label>
                    <input type="number" name="montant_global_ttc" min="100" step="0.01" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Ex: 25000">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-primary text-white px-8 py-3 rounded-xl font-semibold flex items-center gap-2 shadow-lg hover:shadow-xl transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Générer
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Invoice List --}}
    <div class="bg-white rounded-2xl card-shadow overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">📋 Liste des Factures</h2>
                    <p class="text-sm text-gray-500">{{ $invoices->total() }} facture(s) au total</p>
                </div>

                {{-- Filter Tabs --}}
                <div class="flex items-center gap-2 bg-gray-100 p-1 rounded-xl">
                    <a href="{{ route('invoices.index') }}" 
                       class="px-4 py-2 text-sm font-medium rounded-lg transition-all {{ !request('type') ? 'bg-white shadow text-gray-900' : 'text-gray-600 hover:text-gray-900' }}">
                        Toutes
                    </a>
                    <a href="{{ route('invoices.index', ['type' => 'automatic']) }}" 
                       class="px-4 py-2 text-sm font-medium rounded-lg transition-all {{ request('type') === 'automatic' ? 'bg-white shadow text-gray-900' : 'text-gray-600 hover:text-gray-900' }}">
                        🤖 Auto
                    </a>
                    <a href="{{ route('invoices.index', ['type' => 'manual']) }}" 
                       class="px-4 py-2 text-sm font-medium rounded-lg transition-all {{ request('type') === 'manual' ? 'bg-white shadow text-gray-900' : 'text-gray-600 hover:text-gray-900' }}">
                        ✏️ Manuelles
                    </a>
                </div>
            </div>

            {{-- Search --}}
            <form method="GET" action="{{ route('invoices.index') }}" class="mt-4">
                @if(request('type'))
                    <input type="hidden" name="type" value="{{ request('type') }}">
                @endif
                <div class="relative">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Rechercher par n° facture, client..."
                           class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500">
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">N° Facture</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Montant HT</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">TVA</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Total TTC</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-900">{{ $invoice->invoice_number }}</span>
                                    @if($invoice->type === 'manual')
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">Manuel</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700">Auto</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $invoice->client_name ?? 'divers' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-right text-gray-600">{{ number_format($invoice->montant_ht, 2, ',', ' ') }} MAD</td>
                            <td class="px-6 py-4 text-right text-gray-500">{{ number_format($invoice->tva, 2, ',', ' ') }} MAD</td>
                            <td class="px-6 py-4 text-right font-bold text-blue-600">{{ number_format($invoice->total_ttc, 2, ',', ' ') }} MAD</td>
                            <!-- Actions Column - CODE CORRIGÉ -->
<td class="px-6 py-4">
    <div class="flex items-center justify-center gap-2">
        <!-- Bouton Télécharger PDF -->
        <a href="{{ route('invoices.download', $invoice->id) }}" 
           class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
           title="Télécharger PDF">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
        </a>
        
        <!-- Bouton Modifier -->
        <a href="{{ route('invoices.edit', $invoice->id) }}"
           class="p-2 text-gray-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all"
           title="Modifier">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
        </a>
        
        <!-- Bouton Supprimer -->
        <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST" 
              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette facture?')"
              class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all"
                    title="Supprimer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </form>
    </div>
</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <p class="text-gray-500 font-medium">Aucune facture</p>
                                    <p class="text-gray-400 text-sm">Générez votre première facture ci-dessus</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($invoices->hasPages())
            <div class="p-6 border-t border-gray-100">
                {{ $invoices->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
