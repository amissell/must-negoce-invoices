<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    public function index(Request $request)
    {
        $query = Categorie::query();

        if ($request->filled('q')) {
            $query->where('nom', 'like', '%' . $request->q . '%');
        }

        $categories = $query->orderBy('nom')->paginate(20)->withQueryString();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'      => 'required|string|max:150|unique:categories,nom',
            'amevo_id' => 'nullable|string|max:100|unique:categories,amevo_id',
        ], [
            'nom.required' => 'Le nom de la catégorie est obligatoire.',
            'nom.unique'   => 'Cette catégorie existe déjà.',
            'amevo_id.unique' => 'Cet ID AMEVO est déjà utilisé.',
        ]);

        Categorie::create($validated);

        return redirect()->route('categories.index')
            ->with('success', '✅ Catégorie ajoutée avec succès !');
    }

    public function edit(Categorie $categorie)
    {
        return view('categories.edit', compact('categorie'));
    }

    public function update(Request $request, Categorie $categorie)
    {
        $validated = $request->validate([
            'nom'      => 'required|string|max:150|unique:categories,nom,' . $categorie->id,
            'amevo_id' => 'nullable|string|max:100|unique:categories,amevo_id,' . $categorie->id,
        ], [
            'nom.required' => 'Le nom de la catégorie est obligatoire.',
            'nom.unique'   => 'Ce nom est déjà utilisé par une autre catégorie.',
            'amevo_id.unique' => 'Cet ID AMEVO est déjà utilisé.',
        ]);

        $categorie->update($validated);

        return redirect()->route('categories.index')
            ->with('success', '✅ Catégorie mise à jour !');
    }

    public function destroy(Categorie $categorie)
    {
        $categorie->delete();

        return redirect()->route('categories.index')
            ->with('success', '🗑️ Catégorie supprimée !');
    }
}
