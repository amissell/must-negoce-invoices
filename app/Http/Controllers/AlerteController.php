<?php
namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\SuiviVendeur;
use Carbon\Carbon;

class AlerteController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        // Factures impayées > 30j
        $facturesImpayees = Invoice::where('statut_paiement', '!=', 'payée')
            ->where('date', '<', $now->copy()->subDays(30))
            ->orderBy('date')
            ->get()
            ->map(fn($i) => [
                'type' => 'facture',
                'niveau' => $now->diffInDays($i->date) > 60 ? 'critique' : 'attention',
                'message' => "Facture #{$i->invoice_number} - {$i->client_name} - " . number_format($i->total_ttc - $i->montant_paye, 2) . " MAD impayé depuis " . $now->diffInDays($i->date) . "j",
                'date' => $i->date,
            ]);

        // Stock critique (si tu as un champ stock/stock_min dans products)
        $stockCritique = Product::whereColumn('stock', '<', \DB::raw('stock_minimum * 0.3'))
            ->get()
            ->map(fn($p) => [
                'type' => 'stock',
                'niveau' => $p->stock <= 0 ? 'critique' : 'attention',
                'message' => "{$p->name} — Stock: {$p->stock} (Min: {$p->stock_minimum})",
                'date' => now(),
            ]);

        // Vendeurs inactifs (pas d'entrée depuis 3+ jours)
        $vendeurs = SuiviVendeur::distinct()->pluck('vendeur_nom');
        $vendeursInactifs = $vendeurs->map(function ($nom) use ($now) {
            $derniere = SuiviVendeur::where('vendeur_nom', $nom)
                ->where('jour_repos', 0)
                ->orderByDesc('date')
                ->first();
            if (!$derniere) return null;
            $jours = $now->diffInDays($derniere->date);
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
            ->merge($stockCritique)
            ->merge($vendeursInactifs)
            ->sortBy('niveau');

        return view('alertes.index', compact('alertes'));
    }
}
