<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ObjectifVendeur;

class ObjectifVendeurController extends Controller
{
    public function index(Request $request)
    {
        $mois = (int) $request->get('mois', now()->month);
        $annee = (int) $request->get('annee', now()->year);

        $objectifs = ObjectifVendeur::where('mois', $mois)
            ->where('annee', $annee)
            ->orderBy('vendeur_nom')
            ->get();

        $vendeurs = [
            'NOUREDINE',
            'ABDILILAH',
            'HASSAN',
            'ABDELLAH',
            'SOUFAINE',
        ];

        return view('pages.objectifs.index', compact('objectifs', 'vendeurs', 'mois', 'annee'));
    }

    public function create()
    {
        $vendeurs = [
            'NOUREDINE',
            'ABDILILAH',
            'HASSAN',
            'ABDELLAH',
            'SOUFAINE',
        ];
        return view('pages.objectifs.form', [
            'vendeurs' => $vendeurs,
            'objectif' => null,
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'vendeur_nom' => 'required|string',
            'mois' => 'required|integer|min:1|max:12',
            'annee' => 'required|integer',
            'objectif_ca' => 'required|numeric|min:0',
            'objectif_bl' => 'required|integer|min:0',
            'prime' => 'required|numeric|min:0',
        ]);
        ObjectifVendeur::updateOrCreate(
            [
                'vendeur_nom' => $request->vendeur_nom,
                'mois' => $request->mois,
                'annee' => $request->annee,
            ],
            [
                'objectif_ca' => $request->objectif_ca,
                'objectif_bl' => $request->objectif_bl,
                'prime' => $request->prime,
            ]
        );
        return redirect()->route('objectifs.index', [
            'mois' => $request->mois,
            'annee' => $request->annee,
        ])->with('success', 'Objectif enregistré ✔️');
    }
    public function edit($id)
    {
        $objectif = ObjectifVendeur::findOrFail($id);
        $vendeurs = [
            'NOUREDINE',
            'ABDILILAH',
            'HASSAN',
            'ABDELLAH',
            'SOUFAINE',
        ];
        return view('pages.objectifs.form', compact('objectif', 'vendeurs'));
    }
    public function update(Request $request, $id)
    {
        $objectif = ObjectifVendeur::findOrFail($id);
        $request->validate([
            'vendeur_nom' => 'required|string',
            'mois' => 'required|integer|min:1|max:12',
            'annee' => 'required|integer',
            'objectif_ca' => 'required|numeric|min:0',
            'objectif_bl' => 'required|integer|min:0',
            'prime' => 'required|numeric|min:0',
        ]);
        $objectif->update([
            'vendeur_nom' => $request->vendeur_nom,
            'mois' => $request->mois,
            'annee' => $request->annee,
            'objectif_ca' => $request->objectif_ca,
            'objectif_bl' => $request->objectif_bl,
            'prime' => $request->prime,
        ]);

        return redirect()->route('objectifs.index', [
            'mois' => $request->mois,
            'annee' => $request->annee,
        ])->with('success', 'Objectif mis à jour ✔️');
    }
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
