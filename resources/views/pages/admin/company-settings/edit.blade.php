@extends('layouts.app')

@section('title', 'Paramètres Société')
@section('page-title', 'Paramètres Société')

@push('styles')
<style>
    .settings-card { background: var(--white); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: var(--shadow-sm); padding: 24px; margin-bottom: 20px; }
    .settings-card h3 { font-size: 15px; font-weight: 700; color: var(--primary-color); margin-bottom: 4px; }
    .settings-card p.hint { font-size: 12px; color: var(--text-secondary); margin-bottom: 16px; }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
    .form-field { display: flex; flex-direction: column; gap: 6px; }
    .form-field label { font-size: 12px; font-weight: 600; color: var(--text-primary); }
    .form-field input[type=text], .form-field input[type=email], .form-field input[type=number], .form-field input[type=url], .form-field select, .form-field textarea {
        border: 1px solid var(--border-color); background: var(--white); padding: 9px 11px; border-radius: 6px; font-size: 14px; color: var(--text-primary); width: 100%;
    }
    .form-field textarea { min-height: 80px; resize: vertical; font-family: inherit; }
    .form-field input:focus, .form-field select:focus, .form-field textarea:focus { outline: none; border-color: var(--accent-color); box-shadow: 0 0 0 3px rgba(20,184,166,0.12); }
    .logo-preview { display: flex; align-items: center; gap: 16px; padding: 12px; background: var(--bg-secondary); border-radius: 8px; margin-bottom: 12px; }
    .logo-preview img { max-width: 80px; max-height: 80px; background: white; padding: 6px; border-radius: 6px; border: 1px solid var(--border-color); }
    .btn-primary { background: var(--accent-color); color: var(--primary-color); border: none; padding: 11px 22px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s ease; }
    .btn-primary:hover { background: var(--accent-hover); color: white; }
    .btn-danger-link { color: #dc2626; font-size: 12px; text-decoration: none; }
    .form-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px; }
    .template-picker { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .template-option { border: 2px solid var(--border-color); border-radius: 10px; padding: 14px; cursor: pointer; transition: all 0.2s; background: var(--white); }
    .template-option input { display: none; }
    .template-option-title { font-weight: 700; color: var(--primary-color); font-size: 14px; margin-bottom: 4px; }
    .template-option-desc { font-size: 12px; color: var(--text-secondary); }
    .template-option.selected { border-color: var(--accent-color); background: #ecfeff; box-shadow: 0 0 0 3px rgba(20,184,166,0.12); }
</style>
@endpush
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Paramètres Société</h1>
        <p class="page-subtitle">Ces informations sont utilisées sur toutes les factures PDF générées.</p>
    </div>
</div>
<form action="{{ route('admin.company-settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="settings-card">
        <h3>Identité</h3>
        <p class="hint">Nom et logo affichés en en-tête des factures.</p>
        @if($company->logo_url)
            <div class="logo-preview">
                <img src="{{ $company->logo_url }}" alt="Logo actuel">
                <div>
                    <div style="font-weight:600;">Logo actuel</div>
                    <label style="font-size:12px; color: var(--text-secondary);">
                        <input type="checkbox" name="remove_logo" value="1"> Supprimer ce logo
                    </label>
                </div>
            </div>
        @endif
        <div class="grid-2">
            <div class="form-field">
                <label for="name">Nom de la société *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $company->name) }}" required>
            </div>
            <div class="form-field">
                <label for="logo">Logo (PNG, JPG, SVG - max 2 MB)</label>
                <input type="file" name="logo" id="logo" accept="image/*">
            </div>
        </div>
    </div>
    <div class="settings-card">
        <h3>Coordonnées</h3>
        <div class="grid-2">
            <div class="form-field">
                <label for="address">Adresse</label>
                <input type="text" name="address" id="address" value="{{ old('address', $company->address) }}">
            </div>
            <div class="form-field">
                <label for="phone">Téléphone</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $company->phone) }}">
            </div>
            <div class="form-field">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $company->email) }}">
            </div>
            <div class="form-field">
                <label for="website">Site web</label>
                <input type="url" name="website" id="website" value="{{ old('website', $company->website) }}">
            </div>
        </div>
    </div>
    <div class="settings-card">
        <h3>Informations légales</h3>
        <p class="hint">Ces identifiants apparaissent dans le pied de page des factures.</p>
        <div class="grid-3">
            <div class="form-field"><label for="ice">ICE</label><input type="text" name="ice" id="ice" value="{{ old('ice', $company->ice) }}"></div>
            <div class="form-field"><label for="if_number">IF</label><input type="text" name="if_number" id="if_number" value="{{ old('if_number', $company->if_number) }}"></div>
            <div class="form-field"><label for="rc">RC</label><input type="text" name="rc" id="rc" value="{{ old('rc', $company->rc) }}"></div>
            <div class="form-field"><label for="patente">Patente</label><input type="text" name="patente" id="patente" value="{{ old('patente', $company->patente) }}"></div>
            <div class="form-field"><label for="cnss">CNSS</label><input type="text" name="cnss" id="cnss" value="{{ old('cnss', $company->cnss) }}"></div>
        </div>
    </div>
    <div class="settings-card">
        <h3>Facturation</h3>
        <div class="grid-3">
            <div class="form-field">
                <label for="tva_rate">Taux de TVA (%) *</label>
                <input type="number" step="0.01" min="0" max="100" name="tva_rate" id="tva_rate" value="{{ old('tva_rate', $company->tva_rate) }}" required>
            </div>
            <div class="form-field">
                <label for="currency">Devise *</label>
                <input type="text" name="currency" id="currency" value="{{ old('currency', $company->currency) }}" required>
            </div>
            <div class="form-field">
                <label for="invoice_prefix">Préfixe factures *</label>
                <input type="text" name="invoice_prefix" id="invoice_prefix" value="{{ old('invoice_prefix', $company->invoice_prefix) }}" required>
            </div>
        </div>
    </div>
    <div class="settings-card">
        <h3>Textes des factures</h3>
        <div class="grid-2">
            <div class="form-field">
                <label for="footer_text">Sous-titre / Slogan</label>
                <textarea name="footer_text" id="footer_text">{{ old('footer_text', $company->footer_text) }}</textarea>
            </div>
            <div class="form-field">
                <label for="legal_mentions">Mentions légales</label>
                <textarea name="legal_mentions" id="legal_mentions">{{ old('legal_mentions', $company->legal_mentions) }}</textarea>
            </div>
        </div>
    </div>
    <div class="settings-card">
        <h3>Modèle de facture PDF par défaut</h3>
        <p class="hint">Choisissez le style utilisé lors du téléchargement des factures.</p>
        <div class="template-picker">
            @php $currentTpl = old('default_pdf_template', $company->default_pdf_template); @endphp
            <label class="template-option {{ $currentTpl === 'classic' ? 'selected' : '' }}" data-tpl="classic">
                <input type="radio" name="default_pdf_template" value="classic" {{ $currentTpl === 'classic' ? 'checked' : '' }}>
                <div class="template-option-title">Classique</div>
                <div class="template-option-desc">Style traditionnel, tableau noir sur fond blanc, formel.</div>
            </label>

            <label class="template-option {{ $currentTpl === 'modern' ? 'selected' : '' }}" data-tpl="modern">
                <input type="radio" name="default_pdf_template" value="modern" {{ $currentTpl === 'modern' ? 'checked' : '' }}>
                <div class="template-option-title">Moderne</div>
                <div class="template-option-desc">En-tête coloré, coins arrondis, mise en page contemporaine.</div>
            </label>
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn-primary">💾 Enregistrer</button>
    </div>
</form>
@endsection
@push('scripts')
<script>
    document.querySelectorAll('.template-option').forEach(opt => {
        opt.addEventListener('click', () => {
            document.querySelectorAll('.template-option').forEach(o => o.classList.remove('selected'));
            opt.classList.add('selected');
            opt.querySelector('input').checked = true;
        });
    });
</script>
@endpush
