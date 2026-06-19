<?php

namespace App\Http\Controllers;

use App\Models\Vendeur;
use Illuminate\Http\Request;

class VendeurController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendeur::query();

        if ($request->filled('q')) {
            $query->where('nom_complet', 'like', '%' . $request->q . '%');
        }

        $vendeurs = $query->orderBy('nom_complet')->paginate(20)->withQueryString();

        return view('vendeurs.index', compact('vendeurs'));
    }

    public function create()
    {
        return view('vendeurs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_complet' => 'required|string|max:150|unique:vendeurs,nom_complet',
            'amevo_id'    => 'nullable|string|max:100|unique:vendeurs,amevo_id',
        ], [
            'nom_complet.required' => 'Le nom complet est obligatoire.',
            'nom_complet.unique'   => 'Ce vendeur existe déjà.',
            'amevo_id.unique'      => 'Cet ID AMEVO est déjà utilisé.',
        ]);

        Vendeur::create($validated);

        return redirect()->route('vendeurs.index')
            ->with('success', '✅ Vendeur ajouté avec succès !');
    }

    public function edit(Vendeur $vendeur)
    {
        return view('vendeurs.edit', compact('vendeur'));
    }

    public function update(Request $request, Vendeur $vendeur)
    {
        $validated = $request->validate([
            'nom_complet' => 'required|string|max:150|unique:vendeurs,nom_complet,' . $vendeur->id,
            'amevo_id'    => 'nullable|string|max:100|unique:vendeurs,amevo_id,' . $vendeur->id,
        ], [
            'nom_complet.required' => 'Le nom complet est obligatoire.',
            'nom_complet.unique'   => 'Ce nom est déjà utilisé par un autre vendeur.',
            'amevo_id.unique'      => 'Cet ID AMEVO est déjà utilisé.',
        ]);

        $vendeur->update($validated);

        return redirect()->route('vendeurs.index')
            ->with('success', '✅ Vendeur mis à jour !');
    }

    public function destroy(Vendeur $vendeur)
    {
        $vendeur->delete();

        return redirect()->route('vendeurs.index')
            ->with('success', '🗑️ Vendeur supprimé !');
    }
}