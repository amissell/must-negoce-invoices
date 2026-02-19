<?php

namespace App\Http\Controllers;

use App\Models\SuiviVendeur;
use Illuminate\Http\Request;
use Carbon\Carbon;
Use Illuminate\Support\Facades\Auth;

class SuiviVendeurController extends Controller
{
    // Liste + tableau récapitulatif
    public function index(Request $request)
{
    $mois = $request->get('mois', Carbon::now()->month);
    $annee = $request->get('annee', Carbon::now()->year);
    $vendeur = $request->get('vendeur');

    $query = SuiviVendeur::parMois($annee, $mois);
    
    if ($vendeur) {
        $query->vendeur($vendeur);
    }

    $suiviParVendeur = SuiviVendeur::parMois($annee, $mois)
        ->get()
        ->groupBy('vendeur_nom')
        ->map(function ($entries) {
            return [
                'total_chiffre' => $entries->sum('chiffre'),
                'total_paye'    => $entries->sum('total_paye'),
                'total_reste'   => $entries->sum('chiffre') - $entries->sum('total_paye'),
                'total_bl'      => $entries->sum('bl'),
                'entries'       => $entries->sortBy('date'),
            ];
        });

    // ✅ NEW: Global statistics for the top cards
    $allEntries = SuiviVendeur::parMois($annee, $mois)->get();
    $stats = [
        'ca_total'    => $allEntries->sum('chiffre'),
        'encaisse'    => $allEntries->sum('total_paye'),
        'reste'       => $allEntries->sum('chiffre') - $allEntries->sum('total_paye'),
        'bl_total'    => $allEntries->sum('bl'),
        'taux_global' => $allEntries->sum('chiffre') > 0
                            ? round(($allEntries->sum('total_paye') / $allEntries->sum('chiffre')) * 100, 1)
                            : 0,
    ];

    $lignes = $query->orderBy('date', 'desc')->paginate(30);
    $vendeurs = SuiviVendeur::distinct()->pluck('vendeur_nom');

    return view('suivi_vendeurs.index', compact(
        'lignes', 'suiviParVendeur', 'vendeurs', 'mois', 'annee', 'stats'
    ));
}

    // Formulaire de création
    public function create()
    {
        $vendeurs = SuiviVendeur::distinct()->pluck('vendeur_nom');
        return view('suivi_vendeurs.create', compact('vendeurs'));
    }

    // Sauvegarde
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendeur_nom' => 'required|string|max:100',
            'date' => 'required|date',
            'chiffre' => 'required|numeric|min:0',
            'total_paye' => 'required|numeric|min:0',
            'heure_debut' => 'nullable|date_format:H:i',
            'heure_fin' => 'nullable|date_format:H:i',
            'bl' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['created_by'] = Auth::id();
        SuiviVendeur::create($validated);

        return redirect()->route('suivi-vendeurs.index')
            ->with('success', 'Entrée ajoutée avec succès !');
    }

    // Formulaire édition
    public function edit(SuiviVendeur $suiviVendeur)
    {
        return view('suivi_vendeurs.edit', compact('suiviVendeur'));
    }

    // Mise à jour
    public function update(Request $request, SuiviVendeur $suiviVendeur)
    {
        $validated = $request->validate([
            'vendeur_nom' => 'required|string|max:100',
            'date' => 'required|date',
            'chiffre' => 'required|numeric|min:0',
            'total_paye' => 'required|numeric|min:0',
            'heure_debut' => 'nullable|date_format:H:i',
            'heure_fin' => 'nullable|date_format:H:i',
            'bl' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $suiviVendeur->update($validated);

        return redirect()->route('suivi-vendeurs.index')
            ->with('success', 'Entrée mise à jour !');
    }

    // Suppression
    public function destroy(SuiviVendeur $suiviVendeur)
    {
        $suiviVendeur->delete();
        return redirect()->route('suivi-vendeurs.index')
            ->with('success', 'Entrée supprimée !');
    }
}
