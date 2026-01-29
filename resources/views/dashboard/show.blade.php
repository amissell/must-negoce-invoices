@extends('layouts.app')

@section('title', 'Facture ' . $invoice->invoice_number)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('dashboard') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour au tableau de bord
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Facture {{ $invoice->invoice_number }}</h1>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('invoices.pdf.view', $invoice) }}" 
               target="_blank"
               class="px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Aperçu PDF
            </a>
            <a href="{{ route('invoices.pdf.download', $invoice) }}" 
               class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Télécharger PDF
            </a>
        </div>
    </div>

    <!-- Invoice Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Informations Facture</h2>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-gray-600">Numéro:</dt>
                    <dd class="font-medium text-gray-900">{{ $invoice->invoice_number }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Date:</dt>
                    <dd class="font-medium text-gray-900">{{ $invoice->invoice_date->format('d/m/Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Statut:</dt>
                    <dd>
                        @if($invoice->is_locked)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Verrouillée
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Brouillon
                            </span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Client</h2>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-gray-600">Nom:</dt>
                    <dd class="font-medium text-gray-900">{{ $invoice->client->name }}</dd>
                </div>
                @if($invoice->client->ice)
                    <div class="flex justify-between">
                        <dt class="text-gray-600">ICE:</dt>
                        <dd class="font-medium text-gray-900">{{ $invoice->client->ice }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    <!-- Invoice Items -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-900">Détail des Articles</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Prix Unit. TTC</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantité</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total HT</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">TVA</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total TTC</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($invoice->items as $item)
                        <tr>
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-gray-900">{{ $item->product_name }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm text-gray-600">{{ number_format($item->unit_price_ttc, 2, ',', ' ') }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm text-gray-600">{{ $item->quantity }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm text-gray-600">{{ number_format($item->line_total_ht, 2, ',', ' ') }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm text-gray-600">{{ number_format($item->line_vat, 2, ',', ' ') }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm font-medium text-gray-900">{{ number_format($item->line_total_ttc, 2, ',', ' ') }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-6 py-4"></td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-medium text-gray-900">{{ number_format($invoice->total_ht, 2, ',', ' ') }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-medium text-gray-900">{{ number_format($invoice->total_vat, 2, ',', ' ') }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-lg font-bold text-gray-900">{{ number_format($invoice->total_ttc, 2, ',', ' ') }} MAD</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Amount in Words -->
    <div class="bg-blue-50 rounded-xl border border-blue-200 p-6">
        <h3 class="text-sm font-medium text-blue-800 mb-2">Montant en lettres:</h3>
        <p class="text-lg font-medium text-blue-900">{{ $invoice->total_in_words }}</p>
    </div>
</div>
@endsection
