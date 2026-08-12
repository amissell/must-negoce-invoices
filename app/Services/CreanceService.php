<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class CreanceService
{
    public function getAmevoData(?int $mois = null, ?int $annee = null, ?int $userId = null): array
    {
        if (app()->environment('testing')) {
            return ['erreur' => null, 'data' => null];
        }
        $mois  = $mois ?? (int) now()->format('m');
        $annee = $annee ?? now()->year;

        $base = "https://app.amevo.ma/must-negoce/public/synchronisation/{$mois}/{$annee}";
        $url = $userId ? "{$base}/{$userId}" : $base;

        $cacheKey = sprintf('amevo:%s:%02d-%d', $userId ? 'user' . $userId : 'global', $mois, $annee);

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($url) {
            try {
                $response = Http::timeout(15)->get($url);

                if ($response->failed()) {
                    return ['erreur' => "Impossible de contacter l'API AMEVO.", 'data' => null];
                }
                $data = $response->json();

                if (isset($data['error']) && $data['error'] === true) {
                    return ['erreur' => $data['message'] ?? "Erreur inconnue de l'API AMEVO.", 'data' => null];
                }
                return ['erreur' => null, 'data' => $data];

            } catch (\Exception $e) {
                return ['erreur' => "Erreur réseau AMEVO : " . $e->getMessage(), 'data' => null];
            }
        });
    }

    public function getCreancesData(): array
    {
        $now = Carbon::now();
        $clients = Client::with(['invoices' => function ($q) {
            $q->where('statut_paiement', '!=', 'payée');
        }])->get()->map(function ($client) use ($now) {
            $invoices = $client->invoices;

            $j30 = $invoices->filter(fn($i) => $now->diffInDays(Carbon::parse($i->invoice_date)) <= 30)
                ->sum(fn($i) => $i->total_ttc - $i->montant_paye);

            $j60 = $invoices->filter(fn($i) => $now->diffInDays(Carbon::parse($i->invoice_date)) > 30 && $now->diffInDays(Carbon::parse($i->invoice_date)) <= 60)
                ->sum(fn($i) => $i->total_ttc - $i->montant_paye);

            $j90 = $invoices->filter(fn($i) => $now->diffInDays(Carbon::parse($i->invoice_date)) > 60)
                ->sum(fn($i) => $i->total_ttc - $i->montant_paye);

            $total = $j30 + $j60 + $j90;

            return [
                'client' => $client,
                'j30' => $j30,
                'j60' => $j60,
                'j90_plus' => $j90,
                'total_impaye' => $total,
                'nb_factures' => $invoices->count(),
                'risque' => $j90 > 0 ? 'critique' : ($j60 > 0 ? 'attention' : 'normal'),
            ];
        })->filter(fn($c) => $c['total_impaye'] > 0)->sortByDesc('total_impaye');
        $totalCreances = $clients->sum('total_impaye');
        $total30 = $clients->sum('j30');
        $total60 = $clients->sum('j60');
        $total90 = $clients->sum('j90_plus');
        return compact('clients', 'totalCreances', 'total30', 'total60', 'total90');
    }
}
