<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardVendeurCategorieController extends Controller
{
    public function index(Request $request)
    {
        $mois = $request->get('mois', now()->month);
        $annee = $request->get('annee', now()->year);

        $url = "https://app.amevo.ma/must-negoce/public/synchronisation/{$mois}/{$annee}";

        $response = Http::timeout(30)->get($url);

        if (!$response->successful()) {
            return back()->with('error', 'Impossible de récupérer les données.');
        }
        $data = $response->json();

        $vendorCards = collect($data['vendorCards'] ?? []);
        $categoriesData = collect($data['categoriesData'] ?? []);

        return view('pages.dashboards.dashboard-vendeur-categorie', [
            'mois' => $mois,
            'annee' => $annee,

            'vendorCards' => $vendorCards,
            'categoriesData' => $categoriesData,

            'totalGeneral' => $data['ca_total'] ?? 0,
            'catTotal' => $data['cat_ca_total'] ?? 0,
        ]);
    }
}
