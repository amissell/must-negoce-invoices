<?php

namespace App\Http\Controllers;

use App\Models\SuiviVendeur;
use App\Models\SuiviCategorie;
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

        $blGlobal = $toutesLignes->sum('bl');
        $caGlobal = $toutesLignes->sum('chiffre');

        $statsGlobales = [
            'ca_total'     => $caGlobal,
            'encaisse'     => $toutesLignes->sum('total_paye'),
            'reste'        => $caGlobal - $toutesLignes->sum('total_paye'),
            'bl_total'     => $blGlobal,
            'taux_global'  => $caGlobal > 0
                ? round(($toutesLignes->sum('total_paye') / $caGlobal) * 100, 1)
                : 0,
            'drops_global' => $blGlobal > 0
                ? round($caGlobal / $blGlobal, 2)
                : 0,
        ];

        $suiviParVendeur = $toutesLignes
            ->groupBy('vendeur_nom')
            ->map(function ($entries) {
                $caTotal  = $entries->sum('chiffre');
                $encaisse = $entries->sum('total_paye');
                $blTotal  = $entries->sum('bl');
                $sorted   = $entries->sortByDesc('chiffre');

                return [
                    'ca_total'      => $caTotal,
                    'encaisse'      => $encaisse,
                    'reste'         => $caTotal - $encaisse,
                    'bl'            => $blTotal,
                    'taux'          => $caTotal > 0 ? round(($encaisse / $caTotal) * 100, 1) : 0,
                    'drops'         => $blTotal > 0 ? round($caTotal / $blTotal, 2) : 0,
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
        $CIBLE_JOURS = 26;
        
        $isCurrentMonth = (Carbon::now()->year == $annee && Carbon::now()->month == $mois);
        $finCalcul      = $isCurrentMonth
        ? Carbon::now()
        : Carbon::create($annee, $mois, 1)->endOfMonth();
        $joursTravaillables = collect();
        $cursor = Carbon::create($annee, $mois, 1)->startOfMonth();
        while ($cursor->lte($finCalcul)) {
            if ($cursor->dayOfWeek !== Carbon::SUNDAY) {
                $joursTravaillables->push($cursor->toDateString());
                }
                $cursor->addDay();
                }
                $totalJoursOuvres = $joursTravaillables->count();
                $joursAttendus    = min($totalJoursOuvres, $CIBLE_JOURS);
                
                $inactiviteParVendeur = [];
                $tousVendeurs = SuiviVendeur::whereBetween('date', [$debut, $fin])
                ->distinct()
                ->orderBy('vendeur_nom')
                ->pluck('vendeur_nom');
                
                foreach ($tousVendeurs as $nom) {
                    $entrees = SuiviVendeur::whereBetween('date', [$debut, $fin])
                    ->where('vendeur_nom', $nom)
                    ->get();
                    
                    $joursActifs    = $entrees->where('jour_repos', false)->count();
                    $joursRepos     = $entrees->where('jour_repos', true)->count();
                    $joursPresences = $joursActifs + $joursRepos;
                    $joursAbsents   = max(0, $joursAttendus - $joursPresences);
                    $joursAuDela    = max(0, $joursAttendus - $joursActifs);
                    $joursRestants  = max(0, $CIBLE_JOURS - $joursActifs);
                    
                    $inactiviteParVendeur[$nom] = [
                        'jours_actifs'   => $joursActifs,
                        'jours_repos'    => $joursRepos,
                        'jours_absents'  => $joursAbsents,
                        'jours_au_dela'  => $joursAuDela,
                        'jours_restants' => $joursRestants,
                        'statut'         => $joursAuDela >= 10 ? 'critique'
                          : ($joursAuDela >= 4  ? 'attention' : 'ok'),
                          ];
                          }
                          
                          $inactiviteParVendeur = collect($inactiviteParVendeur)
                          ->sortByDesc('jours_au_dela');

                          return view('suivi_vendeurs.index', compact(
                            'lignes', 'suiviParVendeur', 'statsGlobales',
                            'vendeurs', 'mois', 'annee',
                            'chartDates', 'chartDataCA', 'toutesLignes',
                            'inactiviteParVendeur', 'totalJoursOuvres',
                            'joursAttendus', 'CIBLE_JOURS'
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

        if ($validated['jour_repos']) {
            $validated['chiffre']     = 0;
            $validated['total_paye']  = 0;
            $validated['bl']          = 0;
            $validated['heure_debut'] = null;
            $validated['heure_fin']   = null;
        }

        $validated['created_by'] = Auth::id();
        $suiviVendeur = SuiviVendeur::create($validated);

        // ── Enregistrer les catégories ──────────────────────────────────
        if ($request->has('categories')) {
            foreach ($request->categories as $cat) {
                if (!empty($cat['categorie'])) {
                    $suiviVendeur->categories()->create([
                        'categorie' => $cat['categorie'],
                        'quantite'  => $cat['quantite'] ?? 0,
                        'ca'        => $cat['ca'] ?? 0,
                    ]);
                }
            }
        }

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

        if ($validated['jour_repos']) {
            $validated['chiffre']     = 0;
            $validated['total_paye']  = 0;
            $validated['bl']          = 0;
            $validated['heure_debut'] = null;
            $validated['heure_fin']   = null;
        }

        $suiviVendeur->update($validated);

        // ── Supprimer les anciennes catégories et les remplacer ──────────
        $suiviVendeur->categories()->delete();

        if ($request->has('categories')) {
            foreach ($request->categories as $cat) {
                if (!empty($cat['categorie'])) {
                    $suiviVendeur->categories()->create([
                        'categorie' => $cat['categorie'],
                        'quantite'  => $cat['quantite'] ?? 0,
                        'ca'        => $cat['ca'] ?? 0,
                    ]);
                }
            }
        }

        return redirect()->route('suivi-vendeurs.index')
            ->with('success', '✅ Entrée mise à jour !');
    }

    public function destroy(SuiviVendeur $suiviVendeur)
    {
        $suiviVendeur->delete();
        return redirect()->route('suivi-vendeurs.index')
            ->with('success', '🗑️ Entrée supprimée !');
    }

    public function showVendeur(Request $request, string $nom)
    {
        $mois  = $request->get('mois', Carbon::now()->month);
        $annee = $request->get('annee', Carbon::now()->year);

        $debut = Carbon::create($annee, $mois, 1)->startOfMonth()->toDateString();
        $fin   = Carbon::create($annee, $mois, 1)->endOfMonth()->toDateString();

        $lignes = SuiviVendeur::where('vendeur_nom', $nom)
            ->whereBetween('date', [$debut, $fin])
            ->where('jour_repos', 0)
            ->orderBy('date')
            ->get();

        $caTotal      = $lignes->sum('chiffre');
        $encaisse     = $lignes->sum('total_paye');
        $blTotal      = $lignes->sum('bl');
        $taux         = $caTotal > 0 ? round(($encaisse / $caTotal) * 100, 1) : 0;
        $drops        = $blTotal > 0 ? round($caTotal / $blTotal, 2) : 0;
        $meilleurJour = $lignes->sortByDesc('chiffre')->first();
        $pireJour     = $lignes->sortBy('chiffre')->first();

        $chartDates = $lignes->map(fn($l) => Carbon::parse($l->date)->format('d/m'))->values();
        $chartCA    = $lignes->pluck('chiffre')->values();

        // ── Statistiques par catégorie ──────────────────────────────────
        $categoriesStats = SuiviCategorie::whereHas('suiviVendeur', function ($q) use ($nom, $debut, $fin) {
            $q->where('vendeur_nom', $nom)
              ->whereBetween('date', [$debut, $fin]);
        })
        ->selectRaw('categorie, SUM(quantite) as total_quantite, SUM(ca) as total_ca')
        ->groupBy('categorie')
        ->orderByDesc('total_ca')
        ->get();

        return view('suivi_vendeurs.show', compact(
            'nom', 'lignes', 'mois', 'annee',
            'caTotal', 'encaisse', 'blTotal', 'taux', 'drops',
            'meilleurJour', 'pireJour', 'chartDates', 'chartCA',
            'categoriesStats'
        ));
    }
    public function dailyStats(Request $request)
    {
        $date = $request->get('date', now()->toDateString());
        $stats = \App\Models\SuiviCategorie::whereHas('suiviVendeur', function($q) use ($date) {
            $q->whereDate('date', $date);
        })
        ->selectRaw('categorie, SUM(quantite) as total_quantite, SUM(ca) as total_ca')
        ->groupBy('categorie')
        ->get();

    return response()->json([
        'date' => $date,
        'stats' => $stats
    ]);
}
}