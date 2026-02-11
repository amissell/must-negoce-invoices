@extends('layouts.app')

@section('title', 'Modifier la Facture')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Modifier la Facture</h1>
                <p class="text-gray-600 mt-1">Facture N° {{ $invoice->invoice_number }}</p>
            </div>
            <a href="{{ route('invoices.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form -->
    <form id="editInvoiceForm" method="POST" action="{{ route('invoices.update', $invoice->id) }}" class="bg-white shadow-lg rounded-lg overflow-hidden">
        @csrf
        @method('PUT')
        
        <!-- Invoice Information Section -->
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Informations de la Facture</h2>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Invoice Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">N° Facture <span class="text-red-500">*</span></label>
                    <input type="text" name="invoice_number" value="{{ old('invoice_number', $invoice->invoice_number) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono">
                    <p class="text-xs text-gray-500 mt-1">Format: YY/XXX (ex: 26/001)</p>
                </div>

                <!-- Invoice Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date <span class="text-red-500">*</span></label>
                    <input type="date" name="invoice_date" value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Payment Mode -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mode de Paiement <span class="text-red-500">*</span></label>
                    <select name="payment_mode" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="CH" {{ old('payment_mode', $invoice->payment_mode) == 'CH' ? 'selected' : '' }}>Chèque (CH)</option>
                        <option value="ESP" {{ old('payment_mode', $invoice->payment_mode) == 'ESP' ? 'selected' : '' }}>Espèces (ESP)</option>
                        <option value="LCN" {{ old('payment_mode', $invoice->payment_mode) == 'LCN' ? 'selected' : '' }}>(LCN)</option>
                    </select>
                </div>

                <!-- Invoice Type (Read-only) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type de Facture</label>
                    <input type="text" value="{{ $invoice->type === 'manual' ? 'Manuelle' : 'Automatique' }}" readonly
                           class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                </div>
            </div>
        </div>

        <!-- Client Information Section -->
        @if($invoice->type === 'manual' && $invoice->client_name && $invoice->client_name !== 'divers')
        <div class="bg-gray-50 px-6 py-4 border-t border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Informations Client</h2>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Client Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom du Client</label>
                    <input type="text" name="client_name" value="{{ old('client_name', $invoice->client_name) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Client Phone -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                    <input type="tel" name="client_phone" value="{{ old('client_phone', $invoice->client_phone) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Client ICE -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">ICE</label>
                    <input type="text" name="client_ice" value="{{ old('client_ice', $invoice->client_ice) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           pattern="[0-9]{15}" maxlength="15">
                </div>

                <!-- Client Address -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Adresse</label>
                    <input type="text" name="client_address" value="{{ old('client_address', $invoice->client_address) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
        </div>
        @endif

        <!-- Products Section -->
        <div class="bg-gray-50 px-6 py-4 border-t border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Articles de la Facture</h2>
                <button type="button" id="addItemBtn" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Ajouter un Article
                </button>
            </div>
        </div>

        <div class="p-6">
            <!-- Items Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="itemsTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantité</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix Unit.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody" class="bg-white divide-y divide-gray-200">
                        @foreach($invoice->items as $index => $item)
                        <tr class="item-row">
                            <td class="px-6 py-4">
                                <select name="items[{{ $index }}][product_id]" required class="product-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">Sélectionner un produit</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" 
                                                data-price="{{ $product->prix_ttc }}" 
                                                data-name="{{ $product->name }}"
                                                {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-6 py-4">
                                <input type="number" name="items[{{ $index }}][quantity]" min="1" value="{{ $item->quantity }}" required
                                       class="quantity-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-right font-mono">
                            </td>
                            <td class="px-6 py-4">
                                <input type="number" name="items[{{ $index }}][unit_price]" step="0.01" min="0" value="{{ $item->unit_price }}" required
                                       class="price-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-right font-mono">
                            </td>
                            <td class="px-6 py-4">
                                <span class="item-total font-mono font-semibold text-gray-900">{{ number_format($item->total, 2) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <button type="button" class="remove-item text-red-600 hover:text-red-800 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- No items message -->
            <div id="noItemsMessage" class="text-center py-8 text-gray-500" style="display: none;">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-lg font-medium">Aucun article</p>
                <p class="text-sm mt-1">Cliquez sur "Ajouter un Article" pour commencer</p>
            </div>

            <!-- Totals - SAME AS CREATE PAGE -->
            <div class="mt-8 flex justify-end">
                <div class="w-full md:w-96 bg-gray-50 rounded-lg p-6 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="font-medium text-gray-700">Montant HT:</span>
                        <span id="montantHT" class="font-mono font-semibold text-gray-900">0.00 MAD</span>
                    </div>
                    <div class="flex justify-between items-center text-sm border-t border-gray-300 pt-3">
                        <span class="font-medium text-gray-700">Remise (%):</span>
                        <div class="flex items-center">
                            <input type="number" id="remiseInput" name="remise_percentage" min="0" max="100" step="0.01" 
                                   value="{{ old('remise_percentage', $invoice->remise_percentage ?? 0) }}" 
                                   class="w-20 px-2 py-1 border border-gray-300 rounded text-right font-mono text-sm focus:ring-2 focus:ring-blue-500">
                            <span class="ml-1 text-gray-600">%</span>
                        </div>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="font-medium text-gray-700">Montant Remise:</span>
                        <span id="montantRemise" class="font-mono font-semibold text-red-600">0.00 MAD</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="font-medium text-gray-700">TVA (20%):</span>
                        <span id="tvaAmount" class="font-mono font-semibold text-gray-900">0.00 MAD</span>
                    </div>
                    <div class="border-t border-gray-300 pt-3">
                        <div class="flex justify-between text-lg">
                            <span class="font-bold text-gray-900">MONTANT TTC:</span>
                            <span id="totalTTC" class="font-mono font-bold text-blue-600">0.00 MAD</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            <div class="flex justify-end space-x-3">
                <a href="{{ route('invoices.index') }}" 
                   class="inline-flex items-center px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition">
                    Annuler
                </a>
                <button type="submit" id="submitBtn"
                        class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer les Modifications
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Product Selection Modal Template (hidden) -->
<template id="itemRowTemplate">
    <tr class="item-row">
        <td class="px-6 py-4">
            <select name="items[INDEX][product_id]" required class="product-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">Sélectionner un produit</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" data-price="{{ $product->prix_ttc }}" data-name="{{ $product->name }}">
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>
        </td>
        <td class="px-6 py-4">
            <input type="number" name="items[INDEX][quantity]" min="1" value="1" required
                   class="quantity-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-right font-mono">
        </td>
        <td class="px-6 py-4">
            <input type="number" name="items[INDEX][unit_price]" step="0.01" min="0" required
                   class="price-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-right font-mono">
        </td>
        <td class="px-6 py-4">
            <span class="item-total font-mono font-semibold text-gray-900">0.00</span>
        </td>
        <td class="px-6 py-4">
            <button type="button" class="remove-item text-red-600 hover:text-red-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = {{ $invoice->items->count() }};
    const itemsBody = document.getElementById('itemsBody');
    const noItemsMessage = document.getElementById('noItemsMessage');
    const addItemBtn = document.getElementById('addItemBtn');
    const submitBtn = document.getElementById('submitBtn');
    const remiseInput = document.getElementById('remiseInput');

    // Attach listeners to existing rows
    document.querySelectorAll('.item-row').forEach(row => {
        attachRowEventListeners(row);
    });

    // Initial calculation
    calculateGrandTotal();
    updateSubmitButton();

    // Add item button click
    addItemBtn.addEventListener('click', addItem);
    
    // Remise input change
    remiseInput.addEventListener('input', calculateGrandTotal);

    // Add item function
    function addItem() {
        const template = document.getElementById('itemRowTemplate');
        const clone = template.content.cloneNode(true);
        
        // Replace INDEX with actual index
        const row = clone.querySelector('tr');
        row.innerHTML = row.innerHTML.replaceAll('INDEX', itemIndex);
        
        itemsBody.appendChild(clone);
        
        // Hide no items message
        noItemsMessage.style.display = 'none';
        
        // Get the actual appended row
        const addedRow = itemsBody.lastElementChild;
        
        // Attach event listeners to the newly added row
        attachRowEventListeners(addedRow);
        
        itemIndex++;
        updateSubmitButton();
    }

    // Attach event listeners to a row
    function attachRowEventListeners(row) {
        const productSelect = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        const priceInput = row.querySelector('.price-input');
        const removeBtn = row.querySelector('.remove-item');

        // Product selection
        productSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            
            if (price && price !== '') {
                priceInput.value = parseFloat(price).toFixed(2);
                calculateRowTotal(row);
            }
        });

        // Quantity or price change
        quantityInput.addEventListener('input', () => calculateRowTotal(row));
        priceInput.addEventListener('input', () => calculateRowTotal(row));

        // Remove button
        removeBtn.addEventListener('click', function() {
            row.remove();
            if (itemsBody.children.length === 0) {
                noItemsMessage.style.display = 'block';
            }
            calculateGrandTotal();
            updateSubmitButton();
        });
    }

    // Calculate row total
    function calculateRowTotal(row) {
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const total = quantity * price;
        
        row.querySelector('.item-total').textContent = total.toFixed(2);
        calculateGrandTotal();
    }

    // Calculate grand total - SAME LOGIC AS CREATE PAGE
    function calculateGrandTotal() {
        let montantHT = 0;
        
        // Sum all item totals (HT prices)
        document.querySelectorAll('.item-total').forEach(span => {
            montantHT += parseFloat(span.textContent) || 0;
        });

        // Get remise percentage
        const remisePercentage = parseFloat(remiseInput.value) || 0;
        const montantRemise = montantHT * (remisePercentage / 100);
        
        // Calculate HT after remise
        const montantHTAfterRemise = montantHT - montantRemise;
        
        // Calculate TVA on HT after remise
        const tvaRate = 0.20; // 20%
        const tva = montantHTAfterRemise * tvaRate;
        
        // Calculate TTC
        const totalTTC = montantHTAfterRemise + tva;

        // Update display
        document.getElementById('montantHT').textContent = montantHT.toFixed(2) + ' MAD';
        document.getElementById('montantRemise').textContent = montantRemise.toFixed(2) + ' MAD';
        document.getElementById('tvaAmount').textContent = tva.toFixed(2) + ' MAD';
        document.getElementById('totalTTC').textContent = totalTTC.toFixed(2) + ' MAD';
    }

    // Update submit button state
    function updateSubmitButton() {
        submitBtn.disabled = itemsBody.children.length === 0;
    }
});
</script>
@endpush
@endsection