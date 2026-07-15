<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardVendeursController extends Controller
{
    public function index(Request $request)
    {
        $mois   = $request->get('mois', now()->format('m'));
        $annee  = $request->get('annee', now()->year);
        $userId = $request->get('user_id');

        $url = "https://app.amevo.ma/must-negoce/public/synchronisation/{$mois}/{$annee}";

        if ($userId) {
            $url .= "/{$userId}";
        }

        try {
            $response = Http::timeout(15)->get($url);

            if ($response->failed()) {
                return view('pages.dashboards.dashboard-vendeurs', [
                    'erreur' => "Impossible de contacter l'API.",
                    'data' => null,
                    'mois' => $mois,
                    'annee' => $annee,
                    'userId' => $userId,
                ]);
            }

            $data = $response->json();

            if (isset($data['error']) && $data['error'] === true) {
                return view('pages.dashboards.dashboard-vendeurs', [
                    'erreur' => $data['message'] ?? 'Erreur inconnue de l\'API.',
                    'data' => null,
                    'mois' => $mois,
                    'annee' => $annee,
                    'userId' => $userId,
                ]);
            }

            $allowed = [
                'ABDELILAH 0777939547',
                'OUISSADEN HASSAN',
                'ABDELILAH',
                'NOUREDINE',
                'SOUFIANE',
            ];

            $filter = function ($item) use ($allowed) {
                $nom = strtoupper(trim($item['nom'] ?? ''));
                $tel = trim($item['tel'] ?? '');

                if ($nom === 'ABDELILAH') {
                    return $tel === '0777939547';
                }

                return in_array($nom, $allowed);
            };

            foreach ([
                'vendorCards',
                'inactivite',
                'objectifsData',
                'caJourData',
            ] as $key) {
                if (isset($data[$key]) && is_array($data[$key])) {
                    $data[$key] = collect($data[$key])
                        ->filter($filter)
                        ->values()
                        ->toArray();
                }
            }

        } catch (\Exception $e) {
            return view('pages.dashboards.dashboard-vendeurs', [
                'erreur' => "Erreur réseau : " . $e->getMessage(),
                'data' => null,
                'mois' => $mois,
                'annee' => $annee,
                'userId' => $userId,
            ]);
        }

        return view('pages.dashboards.dashboard-vendeurs', [
            'erreur' => null,
            'data' => $data,
            'mois' => $mois,
            'annee' => $annee,
            'userId' => $userId,
        ]);
    }
}
