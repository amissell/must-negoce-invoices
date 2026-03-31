<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ObjectifVendeur;

class ObjectifVendeurController extends Controller
{
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

    public function store(Request $request)
    {
        $request->validate([
            'vendeur_nom' => 'required|string',
            'mois' => 'required|integer|min:1|max:12',
            'annee' => 'required|integer',
            'objectif_spark' => 'required|numeric',
            'objectif_ecosolutions' => 'required|numeric',
            'objectif_total' => 'required|numeric',
            'prime' => 'required|numeric',
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

        return redirect()->back()->with('success', 'Objectif enregistré ✔️');
    }

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

    public function update(Request $request, $id)
    {
        $objectif = ObjectifVendeur::findOrFail($id);

        $request->validate([
            'objectif_spark' => 'required|numeric',
            'objectif_ecosolutions' => 'required|numeric',
            'objectif_total' => 'required|numeric',
            'prime' => 'required|numeric',
        ]);

        $objectif->update($request->all());

        return redirect()->back()->with('success', 'Objectif mis à jour ✔️');
    }
}