@extends('layouts.app')
@section('title', 'Modifier Saisie')

@push('styles')
{{-- same styles as create.blade.php --}}
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    * { font-family: 'Plus Jakarta Sans', sans-serif; }
    .create-page { min-height:100vh; background:linear-gradient(to bottom,#F8FAFC 0%,#EFF6FF 100%); }
    .page-hero { background:linear-gradient(135deg,#4F46E5 0%,#7C3AED 100%); border-radius:24px; position:relative; overflow:hidden; }
    .form-card { background:white; border-radius:24px; box-shadow:0 4px 32px rgba(0,0,0,0.10); border:1px solid rgba(0,0,0,0.05); overflow:hidden; }
    .section-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:#94A3B8; margin-bottom:16px; display:flex; align-items:center; }
    .section-label::after { content:''; flex:1; height:1px; background:#E2E8F0; margin-left:12px; }
    .field-group { display:flex; flex-direction:column; gap:6px; }
    .field-label { font-size:12px; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:0.4px; }
    .field-input { width:100%; padding:12px 16px; border:2px solid #E2E8F0; border-radius:14px; font-size:14px; font-weight:500; color:#1E293B; background:#F8FAFC; transition:all 0.2s ease; outline:none; font-family:'Plus Jakarta Sans',sans-serif; }
    .field-input:focus { border-color:#4F46E5; background:white; box-shadow:0 0 0 4px rgba(79,70,229,0.10); }
    .field-input.is-invalid { border-color:#EF4444; background:#FFF5F5; }
    .field-input[readonly] { opacity:0.4; cursor:not-allowed; background:#F1F5F9; }
    .invalid-feedback { font-size:12px; color:#EF4444; font-weight:600; margin-top:4px; }
    .btn-cancel { display:inline-flex; align-items:center; gap:8px; padding:12px 24px; background:#F1F5F9; color:#64748B; border-radius:14px; font-weight:700; font-size:14px; text-decoration:none; border:none; cursor:pointer; transition:all 0.2s ease; }
    .btn-cancel:hover { background:#E2E8F0; }
    .btn-submit { display:inline-flex; align-items:center; gap:8px; padding:12px 32px; background:linear-gradient(135deg,#4F46E5 0%,#7C3AED 100%); color:white; border-radius:14px; font-weight:700; font-size:14px; border:none; cursor:pointer; transition:all 0.2s ease; box-shadow:0 4px 16px rgba(79,70,229,0.35); }
    .btn-submit:hover { transform:translateY(-2px); }
    .alert-error { background:#FEF2F2; border:1px solid #FECACA; border-radius:14px; padding:16px 20px; margin-bottom:24px; }
    .alert-error ul { margin:4px 0 0; padding-left:18px; color:#DC2626; font-size:13px; font-weight:600; }
    @keyframes fadeInUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
    .fade-in-up { animation:fadeInUp 0.45s ease-out backwards; }
    .repos-banner { display:flex; align-items:center; gap:14px; padding:16px 20px; background:#FFF7ED; border:1.5px solid #FED7AA; border-radius:16px; cursor:pointer; transition:all 0.2s ease; user-select:none; }
    .repos-banner.is-active { background:#FFF1F2; border-color:#FECACA; }
</style>
@endpush

@section('content')
<div class="create-page">
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

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
                    <h1 class="text-2xl font-extrabold text-white">Modifier Saisie</h1>
                    <p class="text-indigo-200 text-sm font-medium">
                        {{ $suiviVendeur->vendeur_nom }} —
                        {{ \Carbon\Carbon::parse($suiviVendeur->date)->format('d/m/Y') }}
                    </p>
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

    <div class="form-card fade-in-up" style="animation-delay:0.1s">
        <div style="height:5px;background:linear-gradient(90deg,#10B981,#4F46E5,#7C3AED)"></div>

        <div class="p-8">

            @if($errors->any())
            <div class="alert-error">
                <span class="text-red-600 font-bold text-sm">⚠️ Erreurs :</span>
                <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <form method="POST" action="{{ route('suivi-vendeurs.update', $suiviVendeur->id) }}">
                @csrf
                @method('PUT')

                {{-- Section 1 --}}
                <div class="mb-6">
                    <div class="section-label"><span>👤 Identification</span></div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div class="field-group">
                            <label class="field-label">Vendeur <span class="text-red-500">*</span></label>
                            <select name="vendeur_nom" class="field-input @error('vendeur_nom') is-invalid @enderror" required>
                                @foreach($vendeurs as $v)
                                    <option value="{{ $v }}" {{ $suiviVendeur->vendeur_nom == $v ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                            @error('vendeur_nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="field-group">
                            <label class="field-label">Date <span class="text-red-500">*</span></label>
                            <input type="date" name="date"
                                   value="{{ old('date', \Carbon\Carbon::parse($suiviVendeur->date)->format('Y-m-d')) }}"
                                   class="field-input @error('date') is-invalid @enderror" required>
                            @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>
                </div>

                {{-- Section 2 --}}
                <div class="mb-6">
                    <div class="section-label"><span>💰 Données Financières</span></div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div class="field-group">
                            <label class="field-label">Chiffre d'affaires (DH) <span class="text-red-500">*</span></label>
                            <input type="number" name="chiffre" id="chiffre"
                                   step="0.01" min="0"
                                   value="{{ old('chiffre', $suiviVendeur->chiffre) }}"
                                   class="field-input @error('chiffre') is-invalid @enderror" required>
                            @error('chiffre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="field-group">
                            <label class="field-label">Total Payé (DH) <span class="text-red-500">*</span></label>
                            <input type="number" name="total_paye" id="totalPaye"
                                   step="0.01" min="0"
                                   value="{{ old('total_paye', $suiviVendeur->total_paye) }}"
                                   class="field-input @error('total_paye') is-invalid @enderror" required>
                            @error('total_paye')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>
                </div>

                {{-- Section 3 --}}
                <div class="mb-6">
                    <div class="section-label"><span>📦 Activité</span></div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                        <div class="field-group">
                            <label class="field-label">Heure début</label>
                            <input type="time" name="heure_debut" id="heureDebut"
                                   value="{{ old('heure_debut', $suiviVendeur->heure_debut ? \Carbon\Carbon::parse($suiviVendeur->heure_debut)->format('H:i') : '') }}"
                                   class="field-input">
                        </div>

                        <div class="field-group">
                            <label class="field-label">Heure fin</label>
                            <input type="time" name="heure_fin" id="heureFin"
                                   value="{{ old('heure_fin', $suiviVendeur->heure_fin ? \Carbon\Carbon::parse($suiviVendeur->heure_fin)->format('H:i') : '') }}"
                                   class="field-input">
                        </div>

                        <div class="field-group">
                            <label class="field-label">Nombre de BL</label>
                            <input type="number" name="bl" id="bl" min="0"
                                   value="{{ old('bl', $suiviVendeur->bl) }}"
                                   class="field-input">
                        </div>

                        <div class="field-group sm:col-span-3">
                            <label class="field-label">Notes</label>
                            <input type="text" name="notes"
                                   value="{{ old('notes', $suiviVendeur->notes) }}"
                                   placeholder="Observations, remarques..."
                                   class="field-input">
                        </div>

                    </div>
                </div>

                {{-- Jour Repos --}}
                <div class="mb-8">
                    <label class="repos-banner {{ $suiviVendeur->jour_repos ? 'is-active' : '' }}"
                           id="reposBanner" for="jourRepos">
                        <input type="checkbox" name="jour_repos" value="1" id="jourRepos"
                               class="w-5 h-5 cursor-pointer accent-orange-500 flex-shrink-0"
                               {{ old('jour_repos', $suiviVendeur->jour_repos) ? 'checked' : '' }}>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:#92400E">
                                🏖️ Ce vendeur n'a pas travaillé ce jour
                            </div>
                            <div style="font-size:11px;color:#B45309;margin-top:3px">
                                Congé / Repos / Absence
                            </div>
                        </div>
                    </label>
                </div>

                <div class="flex items-center justify-between pt-6" style="border-top:1px solid #F1F5F9">
                    <a href="{{ route('suivi-vendeurs.index') }}" class="btn-cancel">✕ Annuler</a>
                    <button type="submit" class="btn-submit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Mettre à jour
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
const reposInputs = [
    document.getElementById('chiffre'),
    document.getElementById('totalPaye'),
    document.getElementById('bl'),
    document.getElementById('heureDebut'),
    document.getElementById('heureFin'),
];

function setReposMode(active) {
    const banner = document.getElementById('reposBanner');
    if (active) {
        document.getElementById('chiffre').value    = 0;
        document.getElementById('totalPaye').value  = 0;
        document.getElementById('bl').value         = 0;
        document.getElementById('heureDebut').value = '';
        document.getElementById('heureFin').value   = '';
        reposInputs.forEach(f => f.setAttribute('readonly', true));
        banner.classList.add('is-active');
    } else {
        reposInputs.forEach(f => f.removeAttribute('readonly'));
        banner.classList.remove('is-active');
    }
}

document.getElementById('jourRepos').addEventListener('change', function () {
    setReposMode(this.checked);
});

// Apply readonly on load if repos is already checked
if (document.getElementById('jourRepos').checked) {
    reposInputs.forEach(f => f.setAttribute('readonly', true));
}
</script>
@endpush