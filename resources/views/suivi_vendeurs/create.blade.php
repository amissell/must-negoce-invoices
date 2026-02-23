@extends('layouts.app')
@section('title', 'Saisie Journalière')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    * { font-family: 'Plus Jakarta Sans', sans-serif; }

    .create-page {
        min-height: 100vh;
        background: linear-gradient(to bottom, #F8FAFC 0%, #EFF6FF 100%);
    }
    .page-hero {
        background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
        border-radius: 24px;
        position: relative;
        overflow: hidden;
    }
    .page-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
        animation: float 20s ease-in-out infinite;
    }
    @keyframes float {
        0%, 100% { transform: translate(0, 0) rotate(0deg); }
        50%       { transform: translate(-30px, 30px) rotate(180deg); }
    }
    .form-card {
        background: white;
        border-radius: 24px;
        box-shadow: 0 4px 32px rgba(0,0,0,0.10);
        border: 1px solid rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .section-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #94A3B8;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
    }
    .section-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #E2E8F0;
        margin-left: 12px;
    }
    .field-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .field-label {
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .field-input {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #E2E8F0;
        border-radius: 14px;
        font-size: 14px;
        font-weight: 500;
        color: #1E293B;
        background: #F8FAFC;
        transition: all 0.2s ease;
        outline: none;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .field-input:focus {
        border-color: #4F46E5;
        background: white;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.10);
    }
    .field-input.is-invalid {
        border-color: #EF4444;
        background: #FFF5F5;
    }
    /* KEY FIX: readonly styling — field still submits to server */
    .field-input[readonly] {
        opacity: 0.4;
        cursor: not-allowed;
        background: #F1F5F9;
        border-color: #E2E8F0;
    }
    .field-input[readonly]:focus {
        border-color: #E2E8F0;
        box-shadow: none;
    }
    .invalid-feedback {
        font-size: 12px;
        color: #EF4444;
        font-weight: 600;
        margin-top: 4px;
    }
    .preview-strip {
        background: linear-gradient(135deg, #F0F4FF 0%, #F5F3FF 100%);
        border: 1px solid #E0E7FF;
        border-radius: 16px;
        padding: 20px 24px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        text-align: center;
        transition: opacity 0.3s ease;
    }
    .preview-strip.dimmed { opacity: 0.4; }
    .preview-item-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #94A3B8;
        margin-bottom: 6px;
    }
    .preview-item-value {
        font-size: 20px;
        font-weight: 800;
        line-height: 1;
    }
    .btn-cancel {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: #F1F5F9;
        color: #64748B;
        border-radius: 14px;
        font-weight: 700;
        font-size: 14px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-cancel:hover { background: #E2E8F0; color: #475569; }
    .btn-submit {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 32px;
        background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
        color: white;
        border-radius: 14px;
        font-weight: 700;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 16px rgba(79, 70, 229, 0.35);
    }
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(79, 70, 229, 0.40);
    }
    .btn-submit:active { transform: translateY(0); }
    .alert-error {
        background: #FEF2F2;
        border: 1px solid #FECACA;
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 24px;
    }
    .alert-error ul {
        margin: 4px 0 0;
        padding-left: 18px;
        color: #DC2626;
        font-size: 13px;
        font-weight: 600;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .fade-in-up { animation: fadeInUp 0.45s ease-out backwards; }
    .taux-bar {
        height: 6px;
        background: #E2E8F0;
        border-radius: 999px;
        overflow: hidden;
        margin-top: 8px;
    }
    .taux-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #4F46E5, #7C3AED);
        transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    /* Repos banner */
    .repos-banner {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 20px;
        background: #FFF7ED;
        border: 1.5px solid #FED7AA;
        border-radius: 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        user-select: none;
    }
    .repos-banner:hover { background: #FFFBEB; border-color: #FCA5A5; }
    .repos-banner.is-active {
        background: #FFF1F2;
        border-color: #FECACA;
    }
</style>
@endpush

@section('content')
<div class="create-page">
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- ── Hero ── --}}
    <div class="page-hero p-8 fade-in-up">
        <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-white leading-tight">Saisie Journalière</h1>
                    <p class="text-indigo-200 text-sm font-medium">Enregistrer les données d'un vendeur</p>
                </div>
            </div>
            <a href="{{ route('suivi-vendeurs.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/20 hover:bg-white/30
                      text-white font-semibold rounded-xl border border-white/30
                      transition-all duration-200 text-sm backdrop-blur-sm">
                ← Retour au Dashboard
            </a>
        </div>
    </div>

    {{-- ── Form card ── --}}
    <div class="form-card fade-in-up" style="animation-delay:0.1s">
        <div style="height:5px;background:linear-gradient(90deg,#4F46E5,#7C3AED,#EC4899)"></div>

        <div class="p-8">

            @if($errors->any())
            <div class="alert-error">
                <span class="text-red-600 font-bold text-sm">⚠️ Veuillez corriger les erreurs suivantes :</span>
                <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <form method="POST" action="{{ route('suivi-vendeurs.store') }}">
                @csrf

                {{-- ─── Section 1 : Identification ─── --}}
                <div class="mb-6">
                    <div class="section-label"><span>👤 Identification</span></div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div class="field-group">
                            <label class="field-label">Vendeur <span class="text-red-500">*</span></label>
                            <select name="vendeur_nom" id="selectVendeur"
                                    class="field-input @error('vendeur_nom') is-invalid @enderror" required>
                                <option value="">— Choisir un vendeur —</option>
                                @foreach($vendeurs as $v)
                                    <option value="{{ $v }}" {{ old('vendeur_nom') == $v ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                                <option value="__nouveau__">➕ Nouveau vendeur...</option>
                            </select>
                            @error('vendeur_nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="field-group" id="nouveauVendeurWrap" style="display:none">
                            <label class="field-label">Nom du nouveau vendeur <span class="text-red-500">*</span></label>
                            <input type="text" id="nouveauVendeurInput" class="field-input"
                                   placeholder="Ex: Mohammed Alami">
                        </div>

                        <div class="field-group">
                            <label class="field-label">Date <span class="text-red-500">*</span></label>
                            <input type="date" name="date"
                                   value="{{ old('date', date('Y-m-d')) }}"
                                   class="field-input @error('date') is-invalid @enderror" required>
                            @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>
                </div>

                {{-- ─── Section 2 : Financier ─── --}}
                <div class="mb-6">
                    <div class="section-label"><span>💰 Données Financières</span></div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">

                        <div class="field-group">
                            <label class="field-label">Chiffre d'affaires (DH) <span class="text-red-500">*</span></label>
                            <input type="number" name="chiffre" id="chiffre"
                                   step="0.01" min="0" value="{{ old('chiffre', 0) }}"
                                   class="field-input @error('chiffre') is-invalid @enderror" required>
                            @error('chiffre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="field-group">
                            <label class="field-label">Total Payé (DH) <span class="text-red-500">*</span></label>
                            <input type="number" name="total_paye" id="totalPaye"
                                   step="0.01" min="0" value="{{ old('total_paye', 0) }}"
                                   class="field-input @error('total_paye') is-invalid @enderror" required>
                            @error('total_paye')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>

                    <div class="preview-strip" id="previewStrip">
                        <div>
                            <div class="preview-item-label">CA Total</div>
                            <div class="preview-item-value text-indigo-600" id="prevCA">0 DH</div>
                        </div>
                        <div>
                            <div class="preview-item-label">Reste à Payer</div>
                            <div class="preview-item-value text-red-500" id="prevReste">0 DH</div>
                        </div>
                        <div>
                            <div class="preview-item-label">Taux Encaissement</div>
                            <div class="preview-item-value text-emerald-600" id="prevTaux">0%</div>
                            <div class="taux-bar"><div class="taux-fill" id="tauxFill" style="width:0%"></div></div>
                        </div>
                    </div>
                </div>

                {{-- ─── Section 3 : Activité ─── --}}
                <div class="mb-6">
                    <div class="section-label"><span>📦 Activité</span></div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                        <div class="field-group">
                            <label class="field-label">Heure début</label>
                            <input type="time" name="heure_debut" id="heureDebut"
                                   value="{{ old('heure_debut') }}" class="field-input">
                        </div>

                        <div class="field-group">
                            <label class="field-label">Heure fin</label>
                            <input type="time" name="heure_fin" id="heureFin"
                                   value="{{ old('heure_fin') }}" class="field-input">
                        </div>

                        <div class="field-group">
                            <label class="field-label">Nombre de BL</label>
                            <input type="number" name="bl" id="bl" min="0"
                                   value="{{ old('bl', 0) }}" class="field-input">
                        </div>

                        <div class="field-group sm:col-span-3">
                            <label class="field-label">Notes</label>
                            <input type="text" name="notes" value="{{ old('notes') }}"
                                   placeholder="Observations, remarques..." class="field-input">
                        </div>

                    </div>
                </div>

                {{-- ─── Jour Repos ─── --}}
                <div class="mb-8">
                    <label class="repos-banner {{ old('jour_repos') ? 'is-active' : '' }}"
                           id="reposBanner" for="jourRepos">
                        <input type="checkbox" name="jour_repos" value="1" id="jourRepos"
                               class="w-5 h-5 cursor-pointer accent-orange-500 flex-shrink-0"
                               {{ old('jour_repos') ? 'checked' : '' }}>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:#92400E">
                                🏖️ Ce vendeur n'a pas travaillé ce jour
                            </div>
                            <div style="font-size:11px;color:#B45309;margin-top:3px">
                                Congé / Repos / Absence — Tous les chiffres seront mis à zéro automatiquement
                            </div>
                        </div>
                    </label>
                </div>
                {{-- ─── Section 4 : Catégories ─── --}}
                <div class="mb-8">
                    <div class="section-label"><span>📦 Catégories vendues ce jour</span></div>
                    <div id="categories-container">
                        <div class="categorie-row" style="display:flex;gap:10px;margin-bottom:10px;align-items:center">
                            <select name="categories[0][categorie]" class="field-input" style="flex:2">
                                <option value="">— Choisir catégorie —</option>
                                <option value="COMPACT">COMPACT</option>
                                <option value="SPARK">SPARK</option>
                                <option value="SILLA">SILLA</option>
                                <option value="NETTOYAGE">NETTOYAGE</option>
                                <option value="ECO-SOLUTIONS">ECO-SOLUTIONS</option>
                                <option value="EL FEDDAN GROS">EL FEDDAN GROS</option>
                                <option value="COUCHES">COUCHES</option>
                                <option value="FEDDANE DETAIL">FEDDANE DETAIL</option>
                                <option value="FOOD">FOOD</option>
                                <option value="TCOSMÉTIQUE">TCOSMÉTIQUE</option>
                            </select>
                            <input type="number" name="categories[0][quantite]" placeholder="Qté"
                            class="field-input" style="flex:1" min="0">
                            <input type="number" name="categories[0][ca]" placeholder="CA (DH)"
                            class="field-input" style="flex:1" step="0.01" min="0">
                        </div>
                    </div>
                    <button type="button" onclick="addCategorie()"
                    style="margin-top:8px;padding:8px 18px;background:#EEF2FF;color:#4338CA;border:1.5px solid #C7D2FE;border-radius:10px;font-weight:700;font-size:13px;cursor:pointer;font-family:inherit">
                    ➕ Ajouter une catégorie
                </button>
            </div>

                {{-- ─── Actions ─── --}}
                <div class="flex items-center justify-between pt-6" style="border-top:1px solid #F1F5F9">
                    <a href="{{ route('suivi-vendeurs.index') }}" class="btn-cancel">✕ Annuler</a>
                    <button type="submit" class="btn-submit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M5 13l4 4L19 7"/>
                        </svg>
                        Enregistrer
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>
</div>
@endsection

@push('scripts')
<script>
// ── Live CA preview ──────────────────────────────────
function updateCalc() {
    const ca    = parseFloat(document.getElementById('chiffre').value)   || 0;
    const paye  = parseFloat(document.getElementById('totalPaye').value) || 0;
    const reste = ca - paye;
    const taux  = ca > 0 ? Math.min((paye / ca) * 100, 100) : 0;

    document.getElementById('prevCA').textContent    = ca.toLocaleString('fr-MA')    + ' DH';
    document.getElementById('prevReste').textContent = reste.toLocaleString('fr-MA') + ' DH';
    document.getElementById('prevTaux').textContent  = taux.toFixed(1) + '%';
    document.getElementById('tauxFill').style.width  = taux + '%';

    document.getElementById('prevReste').className =
        'preview-item-value ' + (reste > 0 ? 'text-red-500' : 'text-emerald-600');

    const tauxFill = document.getElementById('tauxFill');
    tauxFill.style.background = taux >= 80
        ? 'linear-gradient(90deg,#10B981,#34D399)'
        : taux >= 50
            ? 'linear-gradient(90deg,#F59E0B,#FCD34D)'
            : 'linear-gradient(90deg,#EF4444,#F87171)';
}

document.getElementById('chiffre').addEventListener('input', updateCalc);
document.getElementById('totalPaye').addEventListener('input', updateCalc);

// ── Jour repos — use readonly NOT disabled ───────────
// readonly = field is still included in form submission ✅
// disabled = field is excluded from form submission ❌
const reposInputs = [
    document.getElementById('chiffre'),
    document.getElementById('totalPaye'),
    document.getElementById('bl'),
    document.getElementById('heureDebut'),
    document.getElementById('heureFin'),
];

function setReposMode(active) {
    const banner = document.getElementById('reposBanner');
    const preview = document.getElementById('previewStrip');

    if (active) {
        document.getElementById('chiffre').value    = 0;
        document.getElementById('totalPaye').value  = 0;
        document.getElementById('bl').value         = 0;
        document.getElementById('heureDebut').value = '';
        document.getElementById('heureFin').value   = '';

        reposInputs.forEach(f => f.setAttribute('readonly', true));
        banner.classList.add('is-active');
        preview.classList.add('dimmed');
        updateCalc();
    } else {
        reposInputs.forEach(f => f.removeAttribute('readonly'));
        banner.classList.remove('is-active');
        preview.classList.remove('dimmed');
    }
}

document.getElementById('jourRepos').addEventListener('change', function () {
    setReposMode(this.checked);
});

// Restore state if form was re-shown after validation error
@if(old('jour_repos'))
    setReposMode(true);
@endif

// ── Nouveau vendeur ──────────────────────────────────
document.getElementById('selectVendeur').addEventListener('change', function () {
    const wrap  = document.getElementById('nouveauVendeurWrap');
    const input = document.getElementById('nouveauVendeurInput');

    if (this.value === '__nouveau__') {
        wrap.style.display = 'flex';
        input.setAttribute('name', 'vendeur_nom');
        this.removeAttribute('name');
        input.focus();
    } else {
        wrap.style.display = 'none';
        input.removeAttribute('name');
        this.setAttribute('name', 'vendeur_nom');
    }
});


// ── Catégories dynamiques ──
let catIndex = 1;
function addCategorie() {
    const container = document.getElementById('categories-container');
    const row = document.createElement('div');
    row.className = 'categorie-row';
    row.style = 'display:flex;gap:10px;margin-bottom:10px;align-items:center';
    row.innerHTML = `
        <select name="categories[${catIndex}][categorie]" class="field-input" style="flex:2">
            <option value="">— Choisir catégorie —</option>
            <option value="COMPACT">COMPACT</option>
            <option value="SPARK">SPARK</option>
            <option value="SILLA">SILLA</option>
            <option value="NETTOYAGE">NETTOYAGE</option>
            <option value="ECO-SOLUTIONS">ECO-SOLUTIONS</option>
            <option value="EL FEDDAN GROS">EL FEDDAN GROS</option>
            <option value="COUCHES">COUCHES</option>
            <option value="FEDDANE DETAIL">FEDDANE DETAIL</option>
            <option value="FOOD">FOOD</option>
            <option value="TCOSMÉTIQUE">TCOSMÉTIQUE</option>
        </select>
        <input type="number" name="categories[${catIndex}][quantite]" placeholder="Qté"
               class="field-input" style="flex:1" min="0">
        <input type="number" name="categories[${catIndex}][ca]" placeholder="CA (DH)"
               class="field-input" style="flex:1" step="0.01" min="0">
        <button type="button" onclick="this.parentElement.remove()"
                style="padding:8px 12px;background:#FEF2F2;color:#B91C1C;border:1.5px solid #FCA5A5;border-radius:10px;font-weight:700;cursor:pointer">
            🗑️
        </button>
    `;
    container.appendChild(row);
    catIndex++;
}
</script>
@endpush