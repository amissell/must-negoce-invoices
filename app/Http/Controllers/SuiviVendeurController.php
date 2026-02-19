<?php

namespace App\Http\Controllers;

use App\Models\SuiviVendeur;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SuiviVendeurController extends Controller
{
    public function index(Request $request)
    {
        $mois  = $request->get('mois',  Carbon::now()->month);
        $annee = $request->get('annee', Carbon::now()->year);
        $vendeurFiltre = $request->get('vendeur');

        $debut = Carbon::create($annee, $mois, 1)->startOfMonth()->toDateString();
        $fin   = Carbon::create($annee, $mois, 1)->endOfMonth()->toDateString();

        $query = SuiviVendeur::parMois($annee, $mois);
        if ($vendeurFiltre) {
            $query->vendeur($vendeurFiltre);
        }

        // Exclude repos days from all stats
        $toutesLignes = SuiviVendeur::parMois($annee, $mois)
            ->where('jour_repos', 0)
            ->get();

        $statsGlobales = [
            'ca_total'    => $toutesLignes->sum('chiffre'),
            'encaisse'    => $toutesLignes->sum('total_paye'),
            'reste'       => $toutesLignes->sum('chiffre') - $toutesLignes->sum('total_paye'),
            'bl_total'    => $toutesLignes->sum('bl'),
            'taux_global' => $toutesLignes->sum('chiffre') > 0
                ? round(($toutesLignes->sum('total_paye') / $toutesLignes->sum('chiffre')) * 100, 1)
                : 0,
        ];

        $suiviParVendeur = $toutesLignes
            ->groupBy('vendeur_nom')
            ->map(function ($entries) {
                $caTotal  = $entries->sum('chiffre');
                $encaisse = $entries->sum('total_paye');
                $sorted   = $entries->sortByDesc('chiffre');

                return [
                    'ca_total'      => $caTotal,
                    'encaisse'      => $encaisse,
                    'reste'         => $caTotal - $encaisse,
                    'bl'            => $entries->sum('bl'),
                    'taux'          => $caTotal > 0 ? round(($encaisse / $caTotal) * 100, 1) : 0,
                    'meilleur_jour' => $sorted->first(),
                    'pire_jour'     => $sorted->last(),
                    'jours'         => $entries->sortBy('date'),
                ];
            });

        $allDates = SuiviVendeur::whereBetween('date', [$debut, $fin])
            ->where('jour_repos', 0)
            ->distinct()
            ->orderBy('date')
            ->pluck('date');

        $chartDates = $allDates->map(fn($d) => Carbon::parse($d)->format('d/m'))->values();

        $chartDataCA = [];
        foreach ($suiviParVendeur->keys() as $vendeur) {
            $rows = SuiviVendeur::whereBetween('date', [$debut, $fin])
                ->where('vendeur_nom', $vendeur)
                ->where('jour_repos', 0)
                ->orderBy('date')
                ->pluck('chiffre', 'date')
                ->mapWithKeys(fn($val, $key) => [(string) $key => $val]);

            $chartDataCA[$vendeur] = $allDates
                ->map(fn($d) => $rows->get((string) $d, 0))
                ->values()
                ->toArray();
        }

        $vendeurs = SuiviVendeur::distinct()->orderBy('vendeur_nom')->pluck('vendeur_nom');

        $lignes = $query->orderBy('date', 'desc')->paginate(30);

        return view('suivi_vendeurs.index', compact(
            'lignes', 'suiviParVendeur', 'statsGlobales',
            'vendeurs', 'mois', 'annee',
            'chartDates', 'chartDataCA', 'toutesLignes'
        ));
    }

    public function create()
    {
        $vendeurs = SuiviVendeur::distinct()->orderBy('vendeur_nom')->pluck('vendeur_nom');
        return view('suivi_vendeurs.create', compact('vendeurs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendeur_nom' => 'required|string|max:100',
            'date'        => 'required|date',
            'chiffre'     => 'required|numeric|min:0',
            'total_paye'  => 'required|numeric|min:0',
            'heure_debut' => 'nullable|date_format:H:i',
            'heure_fin'   => 'nullable|date_format:H:i',
            'bl'          => 'required|integer|min:0',
            'jour_repos'  => 'nullable|boolean',
            'notes'       => 'nullable|string|max:500',
        ]);

        $validated['jour_repos'] = $request->boolean('jour_repos');

        // Force zeros for repos days — server-side safety
        if ($validated['jour_repos']) {
            $validated['chiffre']     = 0;
            $validated['total_paye']  = 0;
            $validated['bl']          = 0;
            $validated['heure_debut'] = null;
            $validated['heure_fin']   = null;
        }

        $validated['created_by'] = Auth::id();
        SuiviVendeur::create($validated);

        return redirect()->route('suivi-vendeurs.index')
            ->with('success', '✅ Entrée ajoutée avec succès !');
    }

    public function edit(SuiviVendeur $suiviVendeur)
    {
        $vendeurs = SuiviVendeur::distinct()->orderBy('vendeur_nom')->pluck('vendeur_nom');
        return view('suivi_vendeurs.edit', compact('suiviVendeur', 'vendeurs'));
    }

    public function update(Request $request, SuiviVendeur $suiviVendeur)
    {
        $validated = $request->validate([
            'vendeur_nom' => 'required|string|max:100',
            'date'        => 'required|date',
            'chiffre'     => 'required|numeric|min:0',
            'total_paye'  => 'required|numeric|min:0',
            'heure_debut' => 'nullable|date_format:H:i',
            'heure_fin'   => 'nullable|date_format:H:i',
            'bl'          => 'required|integer|min:0',
            'jour_repos'  => 'nullable|boolean',
            'notes'       => 'nullable|string|max:500',
        ]);

        $validated['jour_repos'] = $request->boolean('jour_repos');

        // Force zeros for repos days — server-side safety
        if ($validated['jour_repos']) {
            $validated['chiffre']     = 0;
            $validated['total_paye']  = 0;
            $validated['bl']          = 0;
            $validated['heure_debut'] = null;
            $validated['heure_fin']   = null;
        }

        $suiviVendeur->update($validated);

        return redirect()->route('suivi-vendeurs.index')
            ->with('success', '✅ Entrée mise à jour !');
    }

    public function destroy(SuiviVendeur $suiviVendeur)
    {
        $suiviVendeur->delete();
        return redirect()->route('suivi-vendeurs.index')
            ->with('success', '🗑️ Entrée supprimée !');
    }
}