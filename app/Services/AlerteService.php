<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\SuiviVendeur;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AlerteService
{
    /**
     * Factures dont le paiement est en retard de plus de $seuilJours jours.
     *
     * @return Collection<int, array{type:string,niveau:string,message:string,date:mixed}>
     */
    public function getUnpaidInvoices(int $seuilJours = 30): Collection
    {
        $now = Carbon::now();

        return Invoice::where('statut_paiement', '!=', 'payée')
            ->where('invoice_date', '<', $now->copy()->subDays($seuilJours))
            ->orderBy('invoice_date')
            ->get()
            ->map(function ($i) use ($now) {
                $jours = $now->diffInDays(Carbon::parse($i->invoice_date));
                $clientNom = $i->client ? $i->client->nom : 'Client inconnu';
                $reste = $i->total_ttc - $i->montant_paye;

                return [
                    'type' => 'facture',
                    'niveau' => $jours > 60 ? 'critique' : 'attention',
                    'message' => "Facture #{$i->invoice_number} - {$clientNom} - "
                        . number_format($reste, 2) . " MAD impayé depuis {$jours}j",
                    'date' => $i->invoice_date,
                ];
            });
    }

    /**
     * Vendeurs sans entrée de suivi (hors jour de repos) depuis $seuilJours jours ou plus.
     *
     * @return Collection<int, array{type:string,niveau:string,message:string,date:mixed}>
     */
    public function getInactiveVendors(int $seuilJours = 3): Collection
    {
        $now = Carbon::now();
        $vendeurs = SuiviVendeur::distinct()->pluck('vendeur_nom');

        return $vendeurs->map(function ($nom) use ($now, $seuilJours) {
            $derniere = SuiviVendeur::where('vendeur_nom', $nom)
                ->where('jour_repos', 0)
                ->orderByDesc('date')
                ->first();

            if (!$derniere) {
                return null;
            }

            $jours = $now->diffInDays(Carbon::parse($derniere->date));
            if ($jours < $seuilJours) {
                return null;
            }

            return [
                'type' => 'vendeur',
                'niveau' => $jours >= 7 ? 'critique' : 'attention',
                'message' => "{$nom} inactif depuis {$jours} jours (dernier: {$derniere->date})",
                'date' => $derniere->date,
            ];
        })->filter()->values();
    }
    public function getAllAlertes(): Collection
    {
        $ordreNiveau = ['critique' => 0, 'attention' => 1];

        return collect()
            ->merge($this->getUnpaidInvoices())
            ->merge($this->getInactiveVendors())
            ->sortBy(fn ($a) => $ordreNiveau[$a['niveau']] ?? 2)
            ->values();
    }
}
