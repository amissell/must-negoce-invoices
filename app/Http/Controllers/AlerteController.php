<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\SuiviVendeur;
use Carbon\Carbon;

class AlerteController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        // Factures impayées > 30j
        $facturesImpayees = Invoice::where('statut_paiement', '!=', 'payée')
            ->where('invoice_date', '<', $now->copy()->subDays(30))
            ->orderBy('invoice_date')
            ->get()
            ->map(function ($i) use ($now) {
                $jours = $now->diffInDays(Carbon::parse($i->invoice_date));
                $clientNom = $i->client ? $i->client->nom : 'Client inconnu';
                return [
                    'type' => 'facture',
                    'niveau' => $jours > 60 ? 'critique' : 'attention',
                    'message' => "Facture #{$i->invoice_number} - {$clientNom} - " . number_format($i->total_ttc - $i->montant_paye, 2) . " MAD impayé depuis {$jours}j",
                    'date' => $i->invoice_date,
                ];
            });

        // Vendeurs inactifs (pas d'entrée depuis 3+ jours)
        $vendeurs = SuiviVendeur::distinct()->pluck('vendeur_nom');
        $vendeursInactifs = $vendeurs->map(function ($nom) use ($now) {
            $derniere = SuiviVendeur::where('vendeur_nom', $nom)
                ->where('jour_repos', 0)
                ->orderByDesc('date')
                ->first();
            if (!$derniere) return null;
            $jours = $now->diffInDays(Carbon::parse($derniere->date));
            if ($jours < 3) return null;
            return [
                'type' => 'vendeur',
                'niveau' => $jours >= 7 ? 'critique' : 'attention',
                'message' => "{$nom} inactif depuis {$jours} jours (dernier: {$derniere->date})",
                'date' => $derniere->date,
            ];
        })->filter();

        $alertes = collect()
            ->merge($facturesImpayees)
            ->merge($vendeursInactifs)
            ->sortBy('niveau');

        return view('pages.alertes.index', compact('alertes'));
    }
}
