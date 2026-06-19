<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\ObjectifVendeur;
use App\Models\SuiviCategorie;
use App\Models\SuiviVendeur;
use App\Models\Vendeur;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;


class SuiviVendeurController extends Controller
{
    public function index(Request $request)
{
    $mois  = (int) $request->get('mois', now()->month);
    $annee = (int) $request->get('annee', now()->year);
    $aliasMap = [
    // Abdelilah variations → one final name
    'ABDEILLAH' => 'ABDELILAH 0777939547',
    'ABDELILAH' => 'ABDELILAH 0777939547',
    'ABDILILAH' => 'ABDELILAH 0777939547',

    // Hassan → full name
    'HASSAN' => 'OUISSADEN HASSAN',
    'OUISSADEN HASSAN' => 'OUISSADEN HASSAN',

    // Soufiane variations
    'SOUFAINE' => 'SOUFIANE',
    'SOUFIANE' => 'SOUFIANE',

    // Others
    'ABDELLAH' => 'ABDELLAH',
    'NOUREDINE' => 'NOUREDINE',
];

    $normalizeName = function ($nom) use ($aliasMap) {
        $nom = trim(mb_strtoupper($nom));
        return $aliasMap[$nom] ?? $nom;
    };

    $vendeurs = collect(
    array_merge(
        SuiviVendeur::pluck('vendeur_nom')->toArray(),
        
        ObjectifVendeur::pluck('vendeur_nom')->toArray()
        )
        )
        ->map($normalizeName)
        ->unique()
        ->sort()
        ->values()
        ->toArray();

    // Query de base
    $query = SuiviVendeur::whereMonth('date', $mois)
        ->whereYear('date', $annee);

    if ($request->filled('vendeur')) {
        $query->where('vendeur_nom', $request->vendeur);
    }

    if ($request->get('statut') === 'actif') {
        $query->where('jour_repos', 0);
    } elseif ($request->get('statut') === 'repos') {
        $query->where('jour_repos', 1);
    }

    if ($request->filled('q')) {
        $query->where('vendeur_nom', 'like', '%' . $request->q . '%');
    }

    $lignes = (clone $query)->orderByDesc('date')->paginate(30);

    $suivis = SuiviVendeur::whereMonth('date', $mois)
        ->whereYear('date', $annee)
        ->get()
        ->map(function ($item) use ($normalizeName) {
            $item->vendeur_nom = $normalizeName($item->vendeur_nom);
            return $item;
        });

    $objectifs = ObjectifVendeur::where('mois', $mois)
        ->where('annee', $annee)
        ->get()
        ->map(function ($item) use ($normalizeName) {
            $item->vendeur_nom = $normalizeName($item->vendeur_nom);
            return $item;
        })
        ->keyBy('vendeur_nom');

    $suivisParVendeur = $suivis->groupBy('vendeur_nom');

    $joursDansMois = Carbon::create($annee, $mois, 1)->daysInMonth;
    $jourActuel    = ($mois == now()->month && $annee == now()->year)
        ? now()->day
        : $joursDansMois;

    $totalJoursOuvres = 0;
    for ($day = 1; $day <= $joursDansMois; $day++) {
        if (!Carbon::create($annee, $mois, $day)->isWeekend()) {
            $totalJoursOuvres++;
        }
    }

    $joursAttendus = 26;

    // Inactivité
    $inactiviteParVendeur = collect();

    foreach ($vendeurs as $nom) {
        $suiviVendeur = $suivisParVendeur->get($nom, collect());

        $joursActifs = $suiviVendeur
            ->where('jour_repos', 0)
            ->pluck('date')
            ->filter()
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->unique()
            ->count();

        $joursAbsents = max($joursAttendus - $joursActifs, 0);
        $joursAuDela  = max($joursAbsents - 3, 0);

        $statut = $joursAbsents > 6 ? 'critique'
                : ($joursAbsents > 3 ? 'attention' : 'normal');

        $inactiviteParVendeur->put($nom, [
            'vendeur_nom'   => $nom,
            'jours_actifs'  => $joursActifs,
            'jours_absents' => $joursAbsents,
            'jours_au_dela' => $joursAuDela,
            'statut'        => $statut,
        ]);
    }

    $inactiviteParVendeur = $inactiviteParVendeur->sortByDesc('jours_absents');

    // Stats par vendeur
    $suiviParVendeur = collect();

    foreach ($vendeurs as $nom) {
        $lignesVendeur = $suivisParVendeur->get($nom, collect())
            ->where('jour_repos', 0);

        $caTotal   = (float) $lignesVendeur->sum('chiffre');
        $encaisse  = (float) $lignesVendeur->sum('total_paye');
        $reste     = $caTotal - $encaisse;
        $bl        = (int) $lignesVendeur->sum('bl');
        $taux      = $caTotal > 0 ? round(($encaisse / $caTotal) * 100, 1) : 0;
        $drops     = $bl > 0 ? round($caTotal / $bl, 0) : 0;

        $meilleurJour = $lignesVendeur->sortByDesc('chiffre')->first();
        $pireJour     = $lignesVendeur->where('chiffre', '>', 0)->sortBy('chiffre')->first();

        $suiviParVendeur->put($nom, [
            'ca_total'      => $caTotal,
            'encaisse'      => $encaisse,
            'reste'         => $reste,
            'bl'            => $bl,
            'taux'          => $taux,
            'drops'         => $drops,
            'meilleur_jour' => $meilleurJour,
            'pire_jour'     => $pireJour,
        ]);
    }

    // Stats globales
    $actifs = $suivis->where('jour_repos', 0);

    $caTotal    = (float) $actifs->sum('chiffre');
    $encGlobal  = (float) $actifs->sum('total_paye');
    $blTotal    = (int) $actifs->sum('bl');
    $tauxGlobal = $caTotal > 0 ? round(($encGlobal / $caTotal) * 100, 1) : 0;
    $drops      = $blTotal > 0 ? round($caTotal / $blTotal, 0) : 0;

    $statsGlobales = [
        'ca_total'     => $caTotal,
        'encaisse'     => $encGlobal,
        'reste'        => $caTotal - $encGlobal,
        'bl_total'     => $blTotal,
        'taux_global'  => $tauxGlobal,
        'drops_global' => $drops,
    ];

    // Objectifs vendeurs
    $objectifsVendeurs = [];

    foreach ($vendeurs as $nom) {
        $objectif      = $objectifs->get($nom);
        $lignesVendeur = $suivisParVendeur->get($nom, collect())->where('jour_repos', 0);

        $caActuel   = (float) $lignesVendeur->sum('chiffre');
        $blActuel   = (int) $lignesVendeur->sum('bl');
        $objectifCa = (float) ($objectif->objectif_ca ?? 0);
        $objectifBl = (int) ($objectif->objectif_bl ?? 0);
        $prime      = (float) ($objectif->prime ?? 600);

        $progressionCa = $objectifCa > 0 ? round(($caActuel / $objectifCa) * 100, 1) : 0;
        $progressionBl = $objectifBl > 0 ? round(($blActuel / $objectifBl) * 100, 1) : 0;

        // moyenne dyal CA + BL
        $progression = round(($progressionCa + $progressionBl) / 2, 1);

        $atteintCa = $objectifCa > 0 && $caActuel >= $objectifCa;
        $atteintBl = $objectifBl > 0 && $blActuel >= $objectifBl;
        $atteindra = $atteintCa && $atteintBl;

        $predictionCa = $jourActuel > 0 ? round(($caActuel / $jourActuel) * $joursDansMois, 0) : 0;
        $predictionBl = $jourActuel > 0 ? round(($blActuel / $jourActuel) * $joursDansMois, 0) : 0;

        $objectifsVendeurs[$nom] = [
            'ca_actuel'        => $caActuel,
            'bl_actuel'        => $blActuel,
            'objectif_ca'      => $objectifCa,
            'objectif_bl'      => $objectifBl,
            'prime'            => $prime,
            'progression_ca'   => $progressionCa,
            'progression_bl'   => $progressionBl,
            'progression'      => $progression,
            'prediction_ca'    => $predictionCa,
            'prediction_bl'    => $predictionBl,
            'atteint_ca'       => $atteintCa,
            'atteint_bl'       => $atteintBl,
            'atteindra'        => $atteindra,
            'prime_gagnee'     => $atteindra ? $prime : 0,
        ];
    }

    // Chart line
    $datesUniques = $actifs
        ->pluck('date')
        ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
        ->unique()
        ->sort()
        ->values();

    $chartDates = $datesUniques
        ->map(fn($d) => Carbon::parse($d)->format('d/m'))
        ->values()
        ->toArray();

    $chartDataCA = [];
    foreach ($vendeurs as $nom) {
        $chartDataCA[$nom] = $datesUniques->map(function ($date) use ($suivisParVendeur, $nom) {
            return (float) $suivisParVendeur
                ->get($nom, collect())
                ->where('jour_repos', 0)
                ->filter(fn($l) => Carbon::parse($l->date)->format('Y-m-d') === $date)
                ->sum('chiffre');
        })->values()->toArray();
    }

    return view('suivi_vendeurs.index', compact(
        'suivis',
        'lignes',
        'vendeurs',
        'mois',
        'annee',
        'objectifsVendeurs',
        'statsGlobales',
        'totalJoursOuvres',
        'joursAttendus',
        'inactiviteParVendeur',
        'suiviParVendeur',
        'chartDates',
        'chartDataCA'
    ));
}


public function create()
{
    $vendeurs = SuiviVendeur::distinct()
        ->orderBy('vendeur_nom')
        ->pluck('vendeur_nom');

    $categories = Categorie::all();

    return view(
        'suivi_vendeurs.create',
        compact(
            'vendeurs',
            'categories'
        )
    );
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


    public function importdata(Request $request)
    {
        $url = config('services.amevo.synchronisation_url', 'https://app.amevo.ma/must-negoce/public/synchronisation');

        try {
            $curlData = Http::timeout(30)->acceptJson()->get($url)->throw()->json();
            $rows = $this->normalizeImportRows($curlData);

            if ($rows->isEmpty()) {
                return redirect()->route('suivi-vendeurs.index')
                    ->with('error', "Aucune donnée reçue depuis AMEVO.");
            }

            $stats = DB::transaction(function () use ($rows) {
                $stats = [
                    'vendeurs'   => 0,
                    'lignes'     => 0,
                    'categories' => 0,
                ];

                foreach ($rows as $row) {
                    // ── 1. Identifier le bloc "user" ──────────────────────────
                    $user = $row['User'] ?? $row['user'] ?? $row;
                    $nom  = trim((string) data_get($user, 'nom_complet', data_get($user, 'name', '')));

                    if ($nom === '') {
                        continue;
                    }

                    // ── 2. Lier au vendeur local ──────────────────────────────
                    $amevoVendeurId = $this->stringOrNull(
                        data_get($user, 'id')
                        ?? data_get($user, 'user_id')
                        ?? data_get($user, 'amevo_id')
                    );

                    if ($amevoVendeurId !== null) {
                        Vendeur::updateOrCreate(
                            ['amevo_id' => $amevoVendeurId],
                            ['nom_complet' => $nom]
                        );
                        $stats['vendeurs']++;
                    }

                    // ── 3. Déterminer la date de la ligne ─────────────────────
                    // BUG FIX: utiliser la date envoyée par AMEVO si elle existe,
                    // sinon today.  Ça permet d'importer des données historiques.
                    $rawDate = data_get($user, 'date')
                        ?? data_get($user, 'created_at')
                        ?? data_get($row,  'date')
                        ?? now()->toDateString();

                    try {
                        $dateString = Carbon::parse($rawDate)->toDateString();
                    } catch (\Throwable $e) {
                        $dateString = now()->toDateString();
                    }

                    // ── 4. Upsert de la ligne suivi ───────────────────────────
                    // On identifie une ligne par (vendeur + date) pour éviter
                    // les doublons, mais on accepte n'importe quelle date.
                    $suivi = SuiviVendeur::updateOrCreate(
                        [
                            'vendeur_nom' => $nom,
                            'date'        => $dateString,
                        ],
                        [
                            'vendeur_id'  => $amevoVendeurId,
                            'chiffre'     => (float) data_get($user, 'chiffre', 0),
                            'total_paye'  => (float) data_get($user, 'total_paye', 0),
                            'bl'          => (int)   data_get($user, 'total_bl', data_get($user, 'bl', 0)),
                            'jour_repos'  => false,
                            'heure_debut' => null,
                            'heure_fin'   => null,
                            'notes'       => "Synchronisation automatique depuis l'application AMEVO",
                            'created_by'  => Auth::id(),
                        ]
                    );
                    $stats['lignes']++;

                    // ── 5. Catégories ─────────────────────────────────────────
                    $remoteCategories = $this->extractRemoteCategories($row);
                    if ($remoteCategories->isNotEmpty()) {
                        $suivi->categories()->delete();
                    }

                    foreach ($remoteCategories as $remoteCategory) {
                        $categoryName = trim((string) (
                            data_get($remoteCategory, 'nom')
                            ?? data_get($remoteCategory, 'categorie')
                            ?? data_get($remoteCategory, 'name')
                            ?? ''
                        ));

                        if ($categoryName === '') {
                            continue;
                        }

                        $amevoCategorieId = $this->stringOrNull(
                            data_get($remoteCategory, 'id')
                            ?? data_get($remoteCategory, 'categorie_id')
                            ?? data_get($remoteCategory, 'amevo_id')
                        );

                        // BUG FIX: on enregistre la catégorie même sans amevo_id,
                        // on essaie juste d'upsert si on a l'id, sinon firstOrCreate par nom.
                        if ($amevoCategorieId !== null) {
                            Categorie::updateOrCreate(
                                ['amevo_id' => $amevoCategorieId],
                                ['nom'      => $categoryName]
                            );
                        } else {
                            Categorie::firstOrCreate(
                                ['nom'      => $categoryName],
                                ['amevo_id' => null]
                            );
                        }

                        $suivi->categories()->create([
                            'categorie'    => $categoryName,
                            'categorie_id' => $amevoCategorieId,
                            'vendeur_id'   => $amevoVendeurId,
                            'quantite'     => (int)   data_get($remoteCategory, 'quantite', data_get($remoteCategory, 'qte', 0)),
                            'ca'           => (float) data_get($remoteCategory, 'ca', data_get($remoteCategory, 'chiffre', 0)),
                        ]);
                        $stats['categories']++;
                    }
                }

                return $stats;
            });

            return redirect()->route('suivi-vendeurs.index')
                ->with('success', "✅ Import terminé : {$stats['lignes']} ligne(s), {$stats['vendeurs']} vendeur(s), {$stats['categories']} catégorie(s) synchronisés.");

        } catch (Exception $e) {
            return redirect()->route('suivi-vendeurs.index')
                ->with('error', '❌ Erreur importation AMEVO : ' . $e->getMessage());
        }
    }

    private function normalizeImportRows(mixed $payload)
    {
        if (!is_array($payload)) {
            return collect();
        }
        $rows = $payload['data'] ?? $payload['users'] ?? $payload['Users'] ?? $payload;

        if (!is_array($rows)) {
            return collect();
        }

        if (array_key_exists('User', $rows) || array_key_exists('user', $rows) || array_key_exists('nom_complet', $rows)) {
            return collect([$rows]);
        }

        return collect($rows)->filter(fn($row) => is_array($row))->values();
    }

    private function extractRemoteCategories(array $row)
    {
        foreach (['Categories', 'categories', 'SuiviCategorie', 'suivi_categories', 'details'] as $key) {
            $categories = data_get($row, $key);

            if (is_array($categories)) {
                return collect($categories)->filter(fn($category) => is_array($category))->values();
            }
        }

        return collect();
    }

    private function stringOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

}