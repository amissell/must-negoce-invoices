<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Exports\CreancesExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class CreanceController extends Controller
{
    private function getAmevoData()
    {
        // Même logique que DashboardVendeursController : synchronisation du mois en cours, tous vendeurs
        $mois  = now()->format('m');
        $annee = now()->year;

        $url = "https://app.amevo.ma/must-negoce/public/synchronisation/{$mois}/{$annee}";

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
    }

    private function getCreancesData()
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

    public function index()
    {
        $data = $this->getCreancesData();
        $amevo = $this->getAmevoData();

        return view('creances.index', array_merge($data, [
            'amevoErreur' => $amevo['erreur'],
            'amevoData'   => $amevo['data'],
        ]));
    }

    public function exportExcel()
    {
        $data = $this->getCreancesData();
        return Excel::download(new CreancesExport($data['clients']), 'creances_' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportPdf()
    {
        $data = $this->getCreancesData();
        $pdf = Pdf::loadView('creances.pdf', $data);
        return $pdf->download('creances_' . now()->format('Y-m-d') . '.pdf');
    }
}
