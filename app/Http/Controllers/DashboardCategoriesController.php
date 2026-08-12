<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardCategoriesController extends Controller
{
    public function index(Request $request)
    {
        $mois   = $request->get('mois',    now()->format('m'));
        $annee  = $request->get('annee',   now()->year);
        $userId = $request->get('user_id', null);
        $url = "https://app.amevo.ma/must-negoce/public/synchronisation/{$mois}/{$annee}";
        if ($userId) {
            $url .= "/{$userId}";
        }
        try {
            $response = Http::timeout(15)->get($url);
            if ($response->failed()) {
                return view('pages.dashboards.dashboard-categories', [
                    'erreur' => "Impossible de contacter l'API.",
                    'data'   => null,
                ]);
            }
            $data = $response->json();
            if (isset($data['error']) && $data['error'] === true) {
                return view('pages.dashboards.dashboard-categories', [
                    'erreur' => $data['message'] ?? 'Erreur inconnue de l\'API.',
                    'data'   => null,
                ]);
            }
        } catch (\Exception $e) {
            return view('pages.dashboards.dashboard-categories', [
                'erreur' => "Erreur réseau : " . $e->getMessage(),
                'data'   => null,
            ]);
        }
        return view('pages.dashboards.dashboard-categories', [
            'erreur'  => null,
            'data'    => $data,
            'mois'    => $mois,
            'annee'   => $annee,
            'userId'  => $userId,
        ]);
    }
}
