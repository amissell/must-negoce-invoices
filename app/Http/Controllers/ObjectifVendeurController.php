<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ObjectifVendeur;

class ObjectifVendeurController extends Controller
{
    /**
     * Liste tous les objectifs (page index)
     */
    public function index(Request $request)
    {
        $mois = $request->get('mois', now()->month);
        $annee = $request->get('annee', now()->year);

        $objectifs = ObjectifVendeur::where('mois', $mois)
            ->where('annee', $annee)
            ->orderBy('vendeur_nom')
            ->get();

        $vendeurs = [
            'NOUREDINE',
            'ABDILILAH',
            'HASSAN',
            'ABDELLAH',
            'SOUFAINE'
        ];

        return view('objectifs.index', compact('objectifs', 'vendeurs', 'mois', 'annee'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $vendeurs = [
            'NOUREDINE',
            'ABDILILAH',
            'HASSAN',
            'ABDELLAH',
            'SOUFAINE'
        ];

        return view('objectifs.form', [
            'vendeurs' => $vendeurs,
            'objectif' => null
        ]);
    }

    /**
     * Enregistrer (updateOrCreate)
     */
    public function store(Request $request)
    {
        $request->validate([
            'vendeur_nom' => 'required|string',
            'mois' => 'required|integer|min:1|max:12',
            'annee' => 'required|integer',
            'objectif_spark' => 'required|numeric|min:0',
            'objectif_ecosolutions' => 'required|numeric|min:0',
            'objectif_total' => 'required|numeric|min:0',
            'prime' => 'required|numeric|min:0',
        ]);

        ObjectifVendeur::updateOrCreate(
            [
                'vendeur_nom' => $request->vendeur_nom,
                'mois' => $request->mois,
                'annee' => $request->annee,
            ],
            $request->only([
                'objectif_spark',
                'objectif_ecosolutions',
                'objectif_total',
                'prime'
            ])
        );

        return redirect()->route('objectifs.index', [
            'mois' => $request->mois,
            'annee' => $request->annee,
        ])->with('success', 'Objectif enregistré ✔️');
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $objectif = ObjectifVendeur::findOrFail($id);

        $vendeurs = [
            'NOUREDINE',
            'ABDILILAH',
            'HASSAN',
            'ABDELLAH',
            'SOUFAINE'
        ];

        return view('objectifs.form', compact('objectif', 'vendeurs'));
    }

    /**
     * Mettre à jour
     */
    public function update(Request $request, $id)
    {
        $objectif = ObjectifVendeur::findOrFail($id);

        $request->validate([
            'objectif_spark' => 'required|numeric|min:0',
            'objectif_ecosolutions' => 'required|numeric|min:0',
            'objectif_total' => 'required|numeric|min:0',
            'prime' => 'required|numeric|min:0',
        ]);

        $objectif->update($request->only([
            'objectif_spark',
            'objectif_ecosolutions',
            'objectif_total',
            'prime',
        ]));

        return redirect()->route('objectifs.index', [
            'mois' => $objectif->mois,
            'annee' => $objectif->annee,
        ])->with('success', 'Objectif mis à jour ✔️');
    }

    /**
     * Supprimer un objectif
     */
    public function destroy($id)
    {
        $objectif = ObjectifVendeur::findOrFail($id);
        $mois = $objectif->mois;
        $annee = $objectif->annee;
        $objectif->delete();

        return redirect()->route('objectifs.index', [
            'mois' => $mois,
            'annee' => $annee,
        ])->with('success', 'Objectif supprimé ✔️');
    }
}