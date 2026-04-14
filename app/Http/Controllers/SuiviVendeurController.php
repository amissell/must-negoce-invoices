<?php

namespace App\Http\Controllers;

use App\Models\SuiviVendeur;
use App\Models\SuiviCategorie;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\ObjectifVendeur;


class SuiviVendeurController extends Controller
{


public function index(Request $request)
{
    $mois = (int) $request->get('mois', now()->month);
    $annee = (int) $request->get('annee', now()->year);

    $vendeurs = [
        'NOUREDINE',
        'ABDILILAH',
        'HASSAN',
        'ABDELLAH',
        'SOUFAINE',
    ];

    $suivis = SuiviVendeur::whereMonth('date', $mois)
        ->whereYear('date', $annee)
        ->orderByDesc('date')
        ->get();

    $objectifs = ObjectifVendeur::where('mois', $mois)
        ->where('annee', $annee)
        ->get()
        ->keyBy('vendeur_nom');

    $suivisParVendeur = $suivis->groupBy('vendeur_nom');

    $joursDansMois = Carbon::create($annee, $mois, 1)->daysInMonth;
    $jourActuel = ($mois == now()->month && $annee == now()->year) ? now()->day : $joursDansMois;

    /*
    |---------------------------------------------------------
    | Affichage informatif seulement
    |---------------------------------------------------------
    */
    $totalJoursOuvres = 0;
    for ($day = 1; $day <= $joursDansMois; $day++) {
        $date = Carbon::create($annee, $mois, $day);
        if (!$date->isWeekend()) {
            $totalJoursOuvres++;
        }
    }

    /*
    |---------------------------------------------------------
    | Société = base fixe de 26 jours / mois
    |---------------------------------------------------------
    */
    $joursAttendus = 26;

    /*
    |---------------------------------------------------------
    | Inactivité par vendeur
    |---------------------------------------------------------
    */
    $inactiviteParVendeur = collect();

    foreach ($vendeurs as $nom) {
        $suiviVendeur = $suivisParVendeur->get($nom, collect());

        $joursActifs = $suiviVendeur
            ->where('jour_repos', 0)
            ->pluck('date')
            ->filter()
            ->map(function ($date) {
                return Carbon::parse($date)->format('Y-m-d');
            })
            ->unique()
            ->count();

        // Absences sur base fixe de 26 jours
        $joursAbsents = max($joursAttendus - $joursActifs, 0);

        // Retard au-delà d'une tolérance de 3 jours
        $joursAuDela = max($joursAbsents - 3, 0);

        if ($joursAbsents > 6) {
            $statut = 'critique';
        } elseif ($joursAbsents > 3) {
            $statut = 'attention';
        } else {
            $statut = 'normal';
        }

        $inactiviteParVendeur->push([
            'vendeur_nom'   => $nom,
            'jours_actifs'  => $joursActifs,
            'jours_absents' => $joursAbsents,
            'jours_au_dela' => $joursAuDela,
            'statut'        => $statut,
        ]);
    }

    $inactiviteParVendeur = $inactiviteParVendeur
        ->sortByDesc('jours_absents')
        ->values();

    /*
    |---------------------------------------------------------
    | Objectifs mensuels
    |---------------------------------------------------------
    */
    $objectifsVendeurs = [];

    foreach ($vendeurs as $nom) {
        $objectif = $objectifs->get($nom);
        $suiviVendeur = $suivisParVendeur->get($nom, collect());

        $caActuel = (float) $suiviVendeur->sum('chiffre');
        $blActuel = (int) $suiviVendeur->sum('bl');

        $objectifCa = (float) ($objectif->objectif_ca ?? 0);
        $objectifBl = (int) ($objectif->objectif_bl ?? 0);
        $prime = (float) ($objectif->prime ?? 0);

        $progression = $objectifCa > 0
            ? round(($caActuel / $objectifCa) * 100, 1)
            : 0;

        $prediction = $jourActuel > 0
            ? round(($caActuel / $jourActuel) * $joursDansMois, 0)
            : 0;

        $atteindra = $objectifCa > 0 ? $prediction >= $objectifCa : false;

        $objectifsVendeurs[$nom] = [
            'ca_actuel'   => $caActuel,
            'bl_actuel'   => $blActuel,
            'objectif_ca' => $objectifCa,
            'objectif_bl' => $objectifBl,
            'prime'       => $prime,
            'progression' => $progression,
            'prediction'  => $prediction,
            'atteindra'   => $atteindra,
        ];
    }

    return view('suivi_vendeurs.index', compact(
        'suivis',
        'vendeurs',
        'mois',
        'annee',
        'objectifsVendeurs',
        'totalJoursOuvres',
        'joursAttendus',
        'inactiviteParVendeur'
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
    public function dailyStatsPage(Request $request)
{
    $dateDebut = $request->get('date_debut', now()->startOfMonth()->toDateString());
    $dateFin   = $request->get('date_fin', now()->toDateString());

    $jours = SuiviVendeur::whereBetween('date', [$dateDebut, $dateFin])
        ->where('jour_repos', 0)
        ->distinct()
        ->orderBy('date')
        ->pluck('date');

    $categories = SuiviCategorie::whereHas('suiviVendeur', function($q) use ($dateDebut, $dateFin) {
            $q->whereBetween('date', [$dateDebut, $dateFin])->where('jour_repos', 0);
        })
        ->distinct()
        ->pluck('categorie')
        ->sort()
        ->values();

    $dataParCategorie = [];
    foreach ($categories as $cat) {
        $dataParCategorie[$cat] = [];
        foreach ($jours as $jour) {
            $total = SuiviCategorie::whereHas('suiviVendeur', function($q) use ($jour) {
                    $q->whereDate('date', $jour)->where('jour_repos', 0);
                })
                ->where('categorie', $cat)
                ->sum('ca');
            $dataParCategorie[$cat][] = round($total, 2);
        }
    }

    $categoriesStats = SuiviCategorie::whereHas('suiviVendeur', function($q) use ($dateDebut, $dateFin) {
            $q->whereBetween('date', [$dateDebut, $dateFin])->where('jour_repos', 0);
        })
        ->selectRaw('categorie, SUM(quantite) as total_quantite, SUM(ca) as total_ca')
        ->groupBy('categorie')
        ->orderByDesc('total_ca')
        ->get();

    $tableData = [];
    foreach ($jours as $jour) {
        $row = ['date' => $jour, 'categories' => []];
        foreach ($categories as $cat) {
            $row['categories'][$cat] = SuiviCategorie::whereHas('suiviVendeur', function($q) use ($jour) {
                    $q->whereDate('date', $jour)->where('jour_repos', 0);
                })
                ->where('categorie', $cat)
                ->sum('ca');
        }
        $row['total'] = array_sum($row['categories']);
        $tableData[] = $row;
    }

    $chartDates = $jours->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))->values();

    return view('suivi_vendeurs.daily_stats', compact(
        'dateDebut', 'dateFin', 'categories', 'dataParCategorie',
        'categoriesStats', 'tableData', 'chartDates'
    ));
}


public function comparaison(Request $request)
{
    $mois1 = $request->get('mois1', Carbon::now()->month);
    $annee1 = $request->get('annee1', Carbon::now()->year);
    $mois2 = $request->get('mois2', Carbon::now()->subMonth()->month);
    $annee2 = $request->get('annee2', Carbon::now()->subMonth()->year);

    $getData = function ($annee, $mois) {
        $lignes = SuiviVendeur::parMois($annee, $mois)->where('jour_repos', 0)->get();
        return [
            'ca' => $lignes->sum('chiffre'),
            'encaisse' => $lignes->sum('total_paye'),
            'reste' => $lignes->sum('chiffre') - $lignes->sum('total_paye'),
            'bl' => $lignes->sum('bl'),
            'drops' => $lignes->sum('bl') > 0 ? round($lignes->sum('chiffre') / $lignes->sum('bl'), 2) : 0,
            'par_vendeur' => $lignes->groupBy('vendeur_nom')->map(fn($e) => [
                'ca' => $e->sum('chiffre'),
                'encaisse' => $e->sum('total_paye'),
                'bl' => $e->sum('bl'),
            ]),
        ];
    };

    $periode1 = $getData($annee1, $mois1);
    $periode2 = $getData($annee2, $mois2);

    $evolution = [
        'ca' => $periode2['ca'] > 0 ? round((($periode1['ca'] - $periode2['ca']) / $periode2['ca']) * 100, 1) : 0,
        'encaisse' => $periode2['encaisse'] > 0 ? round((($periode1['encaisse'] - $periode2['encaisse']) / $periode2['encaisse']) * 100, 1) : 0,
        'drops' => $periode2['drops'] > 0 ? round((($periode1['drops'] - $periode2['drops']) / $periode2['drops']) * 100, 1) : 0,
    ];

    return view('suivi_vendeurs.comparaison', compact('periode1', 'periode2', 'evolution', 'mois1', 'annee1', 'mois2', 'annee2'));
}

}