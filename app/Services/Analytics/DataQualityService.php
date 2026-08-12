<?php

namespace App\Services\Analytics;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DataQualityService
{
    /** @return array<string, mixed> */
    public function report(Carbon $from, Carbon $to): array
    {
        $invoiceLines = DB::table('invoice_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->whereBetween('invoices.invoice_date', [$from->toDateString(), $to->toDateString()]);

        $checks = [
            $this->check('missing_invoice_client', 'Factures sans client lié', 'Un client est requis pour attribuer correctement le chiffre d’affaires.', DB::table('invoices')->whereBetween('invoice_date', [$from->toDateString(), $to->toDateString()])->whereNull('client_id')->count(), 'warning'),
            $this->check('missing_invoice_date', 'Factures sans date', 'Les analyses temporelles nécessitent une date de facture.', DB::table('invoices')->whereNull('invoice_date')->count(), 'critical'),
            $this->check('invalid_invoice_line_quantity', 'Lignes avec quantité nulle ou négative', 'Ces lignes faussent les volumes et le chiffre d’affaires.', (clone $invoiceLines)->where('invoice_items.quantity', '<=', 0)->count(), 'critical'),
            $this->check('invalid_invoice_line_price', 'Lignes avec prix ou total négatif', 'Ces valeurs doivent être vérifiées avant toute analyse.', (clone $invoiceLines)->where(function ($query) {
                $query->where('invoice_items.unit_price', '<', 0)->orWhere('invoice_items.total', '<', 0);
            })->count(), 'critical'),
            $this->check('missing_product_cost', 'Lignes vendues sans coût d’achat', 'Le profit et la marge ne sont pas calculables pour ces lignes.', (clone $invoiceLines)->leftJoin('products', 'products.id', '=', 'invoice_items.product_id')->whereNull('products.cost_price')->count(), 'warning'),
            $this->check('invalid_product_price', 'Produits avec prix négatif', 'Un prix de vente négatif est invalide.', DB::table('products')->where('prix_ttc', '<', 0)->count(), 'critical'),
            $this->check('invalid_product_cost', 'Produits avec coût négatif', 'Un coût négatif est invalide.', DB::table('products')->where('cost_price', '<', 0)->count(), 'critical'),
            $this->check('missing_client_city', 'Clients sans ville', 'Une ville normalisée est requise pour l’analyse géographique.', DB::table('clients')->whereNull('ville')->orWhere('ville', '')->count(), 'info'),
            $this->check('duplicate_client_ice', 'ICE client dupliqués', 'Un même ICE ne doit identifier qu’un seul client.', DB::table('clients')->whereNotNull('ice')->where('ice', '!=', '')->groupBy('ice')->havingRaw('COUNT(*) > 1')->count(), 'warning'),
        ];

        return [
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'summary' => [
                'checks' => count($checks),
                'issues' => collect($checks)->sum('count'),
                'critical' => collect($checks)->where('severity', 'critical')->sum('count'),
                'warnings' => collect($checks)->where('severity', 'warning')->sum('count'),
            ],
            'checks' => $checks,
            'schema_capabilities' => [
                'inventory' => Schema::hasTable('inventory_movements'),
                'suppliers' => Schema::hasTable('suppliers'),
                'product_categories' => Schema::hasColumn('products', 'category_id'),
                'invoice_cost_snapshot' => Schema::hasColumn('invoice_items', 'unit_cost_at_sale'),
                'invoice_salesperson' => Schema::hasColumn('invoices', 'vendeur_id'),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function check(string $key, string $label, string $explanation, int $count, string $severity): array
    {
        return compact('key', 'label', 'explanation', 'count', 'severity');
    }
}
