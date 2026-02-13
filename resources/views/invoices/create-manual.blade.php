@extends('layouts.app')

@section('title', 'Créer une Facture Manuelle')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Créer une Facture Manuelle</h1>
                <p class="text-gray-600 mt-1">Sté. MUST-NEGOCE</p>
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
    <form id="manualInvoiceForm" method="POST" action="{{ route('invoices.manual.store') }}" class="bg-white shadow-lg rounded-lg overflow-hidden">
        @csrf

        {{-- ✅ Hidden inputs — valeurs calculées côté JS pour le serveur --}}
        <input type="hidden" name="montant_ht"        id="hiddenMontantHT">
        <input type="hidden" name="tva_amount"        id="hiddenTva">
        <input type="hidden" name="total_ttc"         id="hiddenTotalTTC">
        <input type="hidden" name="remise_amount"     id="hiddenRemiseAmount">

        <!-- Invoice Information Section -->
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Informations de la Facture</h2>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Invoice Number (Read-only) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">N° Facture</label>
                    <input type="text" value="{{ $nextInvoiceNumber }}" readonly
                           class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 font-mono">
                </div>
                <!-- Invoice Date - CORRIGÉ ✅ -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date <span class="text-red-500">*</span></label>
                    <input type="date"
                    name="invoice_date"
                    value="{{ old('invoice_date', \Carbon\Carbon::now()->format('Y-m-d')) }}"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Payment Mode -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mode de Paiement <span class="text-red-500">*</span></label>
                    <select name="payment_mode" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="CH"  {{ old('payment_mode') == 'CH'  ? 'selected' : '' }}>Chèque (CH)</option>
                        <option value="ESP" {{ old('payment_mode') == 'ESP' ? 'selected' : '' }}>Espèces (ESP)</option>
                        <option value="LCN" {{ old('payment_mode') == 'LCN' ? 'selected' : '' }}>(LCN)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Client Information Section -->
        <div class="bg-gray-50 px-6 py-4 border-t border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Informations Client</h2>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Client Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom du Client <span class="text-red-500">*</span></label>
                    <input type="text" name="client_name" value="{{ old('client_name') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Nom complet du client">
                </div>

                <!-- Client Phone -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                    <input type="tel" name="client_phone" value="{{ old('client_phone') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="06 12 34 56 78">
                </div>

                <!-- ICE -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">ICE</label>
                    <input type="text" name="client_ice" value="{{ old('client_ice') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Ex: 001234567890123"
                           pattern="[0-9]{15}" maxlength="15">
                </div>

                <!-- Client Address -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Adresse</label>
                    <input type="text" name="client_address" value="{{ old('client_address') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Adresse complète">
                </div>
            </div>
        </div>

        <!-- Products Section -->
        <div class="bg-gray-50 px-6 py-4 border-t border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Articles de la Facture</h2>
                    {{-- ✅ Badge info: les prix sont TTC --}}
                    <p class="text-xs text-blue-600 mt-1 font-medium">
                        ℹ️ Les prix unitaires sont en <strong>TTC</strong> — la TVA 20% sera extraite automatiquement
                    </p>
                </div>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Prix Unit. <span class="text-blue-500 font-bold">(TTC)</span>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total <span class="text-blue-500 font-bold">(TTC)</span>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody" class="bg-white divide-y divide-gray-200">
                        <!-- Items will be added here dynamically -->
                    </tbody>
                </table>
            </div>

            <!-- No items message -->
            <div id="noItemsMessage" class="text-center py-8 text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-lg font-medium">Aucun article ajouté</p>
                <p class="text-sm mt-1">Cliquez sur "Ajouter un Article" pour commencer</p>
            </div>

            <!-- Totals -->
            <div class="mt-8 flex justify-end">
                <div class="w-full md:w-96 bg-gray-50 rounded-lg p-6 space-y-3">

                    {{-- ✅ Total TTC brut (somme des lignes) --}}
                    <div class="flex justify-between text-sm">
                        <span class="font-medium text-gray-500">Total TTC brut:</span>
                        <span id="totalBrut" class="font-mono font-semibold text-gray-500">0.00 MAD</span>
                    </div>

                    {{-- Remise --}}
                    <div class="flex justify-between items-center text-sm border-t border-gray-300 pt-3">
                        <span class="font-medium text-gray-700">Remise (%):</span>
                        <div class="flex items-center">
                            <input type="number" id="remiseInput" name="remise_percentage"
                                   min="0" max="100" step="0.01" value="0"
                                   class="w-20 px-2 py-1 border border-gray-300 rounded text-right font-mono text-sm focus:ring-2 focus:ring-blue-500">
                            <span class="ml-1 text-gray-600">%</span>
                        </div>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="font-medium text-gray-700">Montant Remise:</span>
                        <span id="montantRemise" class="font-mono font-semibold text-red-600">- 0.00 MAD</span>
                    </div>

                    {{-- ✅ Montant TTC après remise --}}
                    <div class="flex justify-between text-sm border-t border-gray-300 pt-3">
                        <span class="font-bold text-gray-800">Montant TTC:</span>
                        <span id="totalTTC" class="font-mono font-bold text-blue-600">0.00 MAD</span>
                    </div>

                    {{-- ✅ TVA extraite du TTC (÷ 1.20) --}}
                    <div class="flex justify-between text-sm bg-yellow-50 rounded px-2 py-1">
                        <span class="font-medium text-gray-700">
                            dont TVA 20%
                            <span class="text-xs text-gray-400">(TTC ÷ 1.20 × 0.20)</span>:
                        </span>
                        <span id="tvaAmount" class="font-mono font-semibold text-orange-600">0.00 MAD</span>
                    </div>

                    {{-- ✅ Montant HT = TTC ÷ 1.20 --}}
                    <div class="border-t-2 border-gray-800 pt-3">
                        <div class="flex justify-between text-lg">
                            <span class="font-bold text-gray-900">
                                MONTANT HT:
                                <span class="text-xs font-normal text-gray-400">(TTC ÷ 1.20)</span>
                            </span>
                            <span id="montantHT" class="font-mono font-bold text-green-600">0.00 MAD</span>
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
                <button type="submit" id="submitBtn" disabled
                        class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Créer la Facture
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Item Row Template -->
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
                   class="price-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-right font-mono"
                   placeholder="Prix TTC">
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
document.addEventListener('DOMContentLoaded', function () {

    const TVA_RATE = 0.20; // ✅ Taux TVA

    let itemIndex = 0;
    const itemsBody    = document.getElementById('itemsBody');
    const noItemsMsg   = document.getElementById('noItemsMessage');
    const addItemBtn   = document.getElementById('addItemBtn');
    const submitBtn    = document.getElementById('submitBtn');
    const remiseInput  = document.getElementById('remiseInput');

    // ─── Event Listeners (une seule fois, hors de addItem) ─────────────────
    addItemBtn.addEventListener('click', addItem);
    remiseInput.addEventListener('input', calculateGrandTotal);

    // ─── Ajouter une ligne ──────────────────────────────────────────────────
    function addItem() {
        const template = document.getElementById('itemRowTemplate');
        const clone    = template.content.cloneNode(true);

        // Remplacer INDEX par l'index réel
        const row = clone.querySelector('tr');
        row.innerHTML = row.innerHTML.replaceAll('INDEX', itemIndex);

        itemsBody.appendChild(clone);
        noItemsMsg.style.display = 'none';

        const addedRow = itemsBody.lastElementChild;
        attachRowEventListeners(addedRow);

        itemIndex++;
        updateSubmitButton();
    }

    // ─── Listeners sur chaque ligne ─────────────────────────────────────────
    function attachRowEventListeners(row) {
        const productSelect = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        const priceInput    = row.querySelector('.price-input');
        const removeBtn     = row.querySelector('.remove-item');

        // Auto-remplir le prix TTC depuis le produit sélectionné
        productSelect.addEventListener('change', function () {
            const price = this.options[this.selectedIndex].getAttribute('data-price');
            if (price && price !== '') {
                priceInput.value = parseFloat(price).toFixed(2);
                calculateRowTotal(row);
            }
        });

        quantityInput.addEventListener('input', () => calculateRowTotal(row));
        priceInput.addEventListener('input',    () => calculateRowTotal(row));

        removeBtn.addEventListener('click', function () {
            row.remove();
            if (itemsBody.children.length === 0) {
                noItemsMsg.style.display = 'block';
            }
            calculateGrandTotal();
            updateSubmitButton();
        });
    }

    // ─── Total d'une ligne (TTC) ─────────────────────────────────────────────
    function calculateRowTotal(row) {
        const qty   = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value)    || 0;
        const total = qty * price; // TTC

        row.querySelector('.item-total').textContent = total.toFixed(2);
        calculateGrandTotal();
    }

    // ─── ✅ CALCUL PRINCIPAL ──────────────────────────────────────────────────
    // Les prix sont TTC → on extrait HT et TVA
    //   Montant TTC brut  = Σ (qté × prix_ttc)
    //   Remise            = TTC_brut × remise%
    //   TTC net           = TTC_brut − Remise
    //   Montant HT        = TTC_net ÷ 1.20
    //   TVA               = TTC_net − HT  (= HT × 20%)
    // ─────────────────────────────────────────────────────────────────────────
    function calculateGrandTotal() {

        // 1. Somme des totaux TTC des lignes
        let totalBrut = 0;
        document.querySelectorAll('.item-total').forEach(span => {
            totalBrut += parseFloat(span.textContent) || 0;
        });

        // 2. Remise sur TTC brut
        const remisePct    = parseFloat(remiseInput.value) || 0;
        const remiseAmount = totalBrut * (remisePct / 100);

        // 3. TTC net après remise
        const totalTTC = totalBrut - remiseAmount;

        // 4. Extraction HT et TVA depuis TTC
        const montantHT = totalTTC / (1 + TVA_RATE);   // TTC ÷ 1.20
        const tva       = totalTTC - montantHT;          // TTC − HT

        // ─── Affichage ────────────────────────────────────────────────────
        document.getElementById('totalBrut').textContent    = totalBrut.toFixed(2)    + ' MAD';
        document.getElementById('montantRemise').textContent = '- ' + remiseAmount.toFixed(2) + ' MAD';
        document.getElementById('totalTTC').textContent     = totalTTC.toFixed(2)     + ' MAD';
        document.getElementById('tvaAmount').textContent    = tva.toFixed(2)          + ' MAD';
        document.getElementById('montantHT').textContent    = montantHT.toFixed(2)    + ' MAD';

        // ─── ✅ Remplir les hidden inputs pour le serveur ─────────────────
        document.getElementById('hiddenMontantHT').value    = montantHT.toFixed(2);
        document.getElementById('hiddenTva').value          = tva.toFixed(2);
        document.getElementById('hiddenTotalTTC').value     = totalTTC.toFixed(2);
        document.getElementById('hiddenRemiseAmount').value = remiseAmount.toFixed(2);
    }

    // ─── Activer/désactiver le bouton Créer ─────────────────────────────────
    function updateSubmitButton() {
        submitBtn.disabled = itemsBody.children.length === 0;
    }

    // Ajouter une première ligne au chargement
    addItem();
});
</script>
@endpush
@endsection