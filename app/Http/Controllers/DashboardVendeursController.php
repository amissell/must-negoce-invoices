<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardVendeursController extends Controller
{
    public function index(Request $request)
    {
        // Récupérer les paramètres depuis l'URL (?mois=06&annee=2026&user_id=2)
        // S'ils ne sont pas fournis, on utilise le mois et l'année en cours
        $mois   = $request->get('mois',    now()->format('m'));
        $annee  = $request->get('annee',   now()->year);
        $userId = $request->get('user_id', null); // null = tous les vendeurs

        // Construire l'URL de l'API
        $url = "https://app.amevo.ma/must-negoce/public/synchronisation/{$mois}/{$annee}";
        if ($userId) {
            $url .= "/{$userId}";
        }

        // Appeler l'API (Laravel Http Client)
        try {
            $response = Http::timeout(15)->get($url);

            // Vérifier que la requête a réussi
            if ($response->failed()) {
                return view('dashboard-vendeurs', [
                    'erreur' => "Impossible de contacter l'API.",
                    'data'   => null,
                ]);
            }

            $data = $response->json();

            // Vérifier si l'API a renvoyé une erreur applicative
            if (isset($data['error']) && $data['error'] === true) {
                return view('dashboard-vendeurs', [
                    'erreur' => $data['message'] ?? 'Erreur inconnue de l\'API.',
                    'data'   => null,
                ]);
            }

        } catch (\Exception $e) {
            return view('dashboard-vendeurs', [
                'erreur' => "Erreur réseau : " . $e->getMessage(),
                'data'   => null,
            ]);
        }

        // Tout est OK → on envoie les données à la vue
        return view('dashboard-vendeurs', [
            'erreur'  => null,
            'data'    => $data,
            'mois'    => $mois,
            'annee'   => $annee,
            'userId'  => $userId,
        ]);
    }
}