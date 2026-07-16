@extends('layouts.app')
@section('title', 'Créer une Facture Manuelle')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-bold text-gray-900">Créer une Facture Manuelle</h1>
            <p class="text-gray-600 mt-2">Sté. MUST-NEGOCE</p>
        </div>
        <a href="{{ route('invoices.index') }}"
           class="inline-flex items-center px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-all gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour
        </a>
    </div>

    <!-- Error Messages -->
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl">
            <h3 class="font-semibold mb-2">Erreurs détectées:</h3>
            <ul class="list-disc list-inside space-y-1 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Form Card -->
    <form id="manualInvoiceForm" method="POST" action="{{ route('invoices.manual.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @csrf

        <input type="hidden" name="montant_ht"    id="hiddenMontantHT">
        <input type="hidden" name="tva_amount"    id="hiddenTva">
        <input type="hidden" name="total_ttc"     id="hiddenTotalTTC">
        <input type="hidden" name="remise_amount" id="hiddenRemiseAmount">

        <!-- Section 1: Invoice Information -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <span class="text-2xl">📋</span>
                Informations de la Facture
            </h2>
        </div>
        <div class="p-6 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">N° Facture</label>
                    <input type="text" value="{{ $nextInvoiceNumber }}" readonly
                           class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 font-mono font-semibold cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Date <span class="text-red-500">*</span></label>
                    <input type="date" name="invoice_date"
                           value="{{ old('invoice_date', \Carbon\Carbon::now()->format('Y-m-d')) }}" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Mode de Paiement <span class="text-red-500">*</span></label>
                    <select name="payment_mode" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">Sélectionner un mode...</option>
                        <option value="CH" {{ old('payment_mode') == 'CH' ? 'selected' : '' }}>Chèque (CH)</option>
                        <option value="ESP" {{ old('payment_mode') == 'ESP' ? 'selected' : '' }}>Espèces (ESP)</option>
                        <option value="VIR" {{ old('payment_mode') == 'VIR' ? 'selected' : '' }}>Virement (VIR)</option>
                        <option value="LCN" {{ old('payment_mode') == 'LCN' ? 'selected' : '' }}>Lettre de Change (LCN)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 2: Client Information -->
        <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 px-6 py-4">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <span class="text-2xl">👥</span>
                Informations Client
            </h2>
        </div>
        <div class="p-6 border-b border-gray-200">
            <!-- Toggle Buttons -->
            <div class="flex gap-3 mb-6">
                <button type="button" id="btnClientExistant" onclick="switchClientMode('existant')"
                        class="flex-1 py-3 rounded-lg text-sm font-semibold border-2 border-blue-600 bg-blue-600 text-white transition-all hover:from-blue-700">
                    👥 Client Existant
                </button>
                <button type="button" id="btnClientNouveau" onclick="switchClientMode('nouveau')"
                        class="flex-1 py-3 rounded-lg text-sm font-semibold border-2 border-gray-200 bg-white text-gray-600 hover:border-gray-300 transition-all">
                    ➕ Nouveau Client
                </button>
            </div>

            <!-- Mode 1: Existing Client -->
            <div id="modeExistant">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Sélectionner un client</label>
                <select id="clientSelect" name="client_id"
                        class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Sélectionner un client...</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->nom }} ({{ $c->ville ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Mode 2: New Client -->
            <div id="modeNouveau" class="hidden space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="text" name="new_client_nom" placeholder="Nom du client"
                           class="px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <input type="email" name="new_client_email" placeholder="Email"
                           class="px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <input type="tel" name="new_client_phone" placeholder="Téléphone"
                           class="px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <input type="text" name="new_client_ville" placeholder="Ville"
                           class="px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
            </div>
        </div>

        <!-- Section 3: Items -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <span class="text-2xl">📦</span>
                Articles
            </h2>
        </div>
        <div class="p-6 border-b border-gray-200">
            <div id="itemsContainer" class="space-y-4">
                <div class="item-row grid grid-cols-12 gap-3">
                    <select name="product_id[]" class="col-span-5 px-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Sélectionner un produit...</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" data-price="{{ $p->prix_ttc }}">
                                {{ $p->name }} ({{ number_format($p->prix_ttc, 2) }} MAD)
                            </option>
                        @endforeach
                    </select>
                    <input type="number" name="quantity[]" placeholder="Qté" min="1" value="1" class="col-span-2 px-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <input type="number" name="unit_price[]" placeholder="Prix" step="0.01" class="col-span-3 px-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <button type="button" onclick="removeItem(this)" class="col-span-2 px-3 py-3 bg-red-100 hover:bg-red-200 text-red-600 font-semibold rounded-lg transition-all">
                        ✕ Retirer
                    </button>
                </div>
            </div>
            <button type="button" onclick="addItem()" class="mt-4 px-4 py-3 bg-blue-50 hover:bg-blue-100 text-blue-600 font-semibold rounded-lg transition-all border border-blue-200">
                + Ajouter un article
            </button>
        </div>

        <!-- Section 4: Summary -->
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="text-sm text-gray-600 font-medium">Total HT</label>
                    <div class="text-3xl font-bold text-gray-900">
                        <span id="displayMontantHT">0.00</span> MAD
                    </div>
                </div>
                <div>
                    <label class="text-sm text-gray-600 font-medium">TVA (20%)</label>
                    <div class="text-3xl font-bold text-blue-600">
                        <span id="displayTva">0.00</span> MAD
                    </div>
                </div>
                <div>
                    <label class="text-sm text-gray-600 font-medium">Total TTC</label>
                    <div class="text-3xl font-bold text-green-600">
                        <span id="displayTotalTTC">0.00</span> MAD
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="p-6 flex gap-4">
            <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Enregistrer la Facture
            </button>
            <a href="{{ route('invoices.index') }}" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition-all">
                Annuler
            </a>
        </div>
    </form>
</div>

<script>
function switchClientMode(mode) {
    document.getElementById('modeExistant').classList.toggle('hidden', mode !== 'existant');
    document.getElementById('modeNouveau').classList.toggle('hidden', mode !== 'nouveau');

    document.getElementById('btnClientExistant').classList.toggle('bg-blue-600', mode === 'existant');
    document.getElementById('btnClientExistant').classList.toggle('bg-white', mode !== 'existant');

    document.getElementById('btnClientNouveau').classList.toggle('bg-blue-600', mode === 'nouveau');
    document.getElementById('btnClientNouveau').classList.toggle('bg-white', mode !== 'nouveau');
}

function addItem() {
    const container = document.getElementById('itemsContainer');
    const newItem = container.querySelector('.item-row').cloneNode(true);
    newItem.querySelectorAll('input, select').forEach(el => el.value = '');
    container.appendChild(newItem);
}

function removeItem(btn) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length > 1) {
        btn.closest('.item-row').remove();
        calculateTotals();
    }
}

function calculateTotals() {
    let totalHT = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('input[name="quantity[]"]').value) || 0;
        const price = parseFloat(row.querySelector('input[name="unit_price[]"]').value) || 0;
        totalHT += qty * price;
    });

    const tva = totalHT * 0.20;
    const totalTTC = totalHT + tva;

    document.getElementById('hiddenMontantHT').value = totalHT.toFixed(2);
    document.getElementById('hiddenTva').value = tva.toFixed(2);
    document.getElementById('hiddenTotalTTC').value = totalTTC.toFixed(2);

    document.getElementById('displayMontantHT').textContent = totalHT.toFixed(2);
    document.getElementById('displayTva').textContent = tva.toFixed(2);
    document.getElementById('displayTotalTTC').textContent = totalTTC.toFixed(2);
}

document.addEventListener('input', calculateTotals);
document.addEventListener('change', calculateTotals);
calculateTotals();
</script>
@endsection
