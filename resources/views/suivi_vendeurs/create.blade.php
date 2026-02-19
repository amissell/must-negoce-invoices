@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Nouvelle entrée - Suivi Vendeur</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('suivi-vendeurs.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Vendeur</label>
            <input type="text" name="vendeur_nom" class="form-control" 
                   list="vendeurs-list" value="{{ old('vendeur_nom') }}" required>
            <datalist id="vendeurs-list">
                @foreach($vendeurs as $vendeur)
                    <option value="{{ $vendeur }}">
                @endforeach
            </datalist>
        </div>

        <div class="mb-3">
            <label>Date</label>
            <input type="date" name="date" class="form-control" value="{{ old('date') }}" required>
        </div>

        <div class="mb-3">
            <label>Chiffre</label>
            <input type="number" step="0.01" name="chiffre" class="form-control" value="{{ old('chiffre') }}" required>
        </div>

        <div class="mb-3">
            <label>Total Payé</label>
            <input type="number" step="0.01" name="total_paye" class="form-control" value="{{ old('total_paye') }}" required>
        </div>

        <div class="mb-3">
            <label>Heure Début</label>
            <input type="time" name="heure_debut" class="form-control" value="{{ old('heure_debut') }}">
        </div>

        <div class="mb-3">
            <label>Heure Fin</label>
            <input type="time" name="heure_fin" class="form-control" value="{{ old('heure_fin') }}">
        </div>

        <div class="mb-3">
            <label>BL</label>
            <input type="number" name="bl" class="form-control" value="{{ old('bl') }}" required>
        </div>

        <div class="mb-3">
            <label>Notes</label>
            <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="{{ route('suivi-vendeurs.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection