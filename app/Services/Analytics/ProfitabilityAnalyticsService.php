<?php
namespace App\Services\Analytics;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProfitabilityAnalyticsService
{
    /** @return array<string, mixed> */
    public function report(Carbon $from, Carbon $to): array
    {
        $baseQuery = $this->baseQuery($from, $to);
        $summary = (clone $baseQuery)
            ->selectRaw('COUNT(*) as line_count')
            ->selectRaw('COUNT(CASE WHEN COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) IS NOT NULL AND COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) >= 0 THEN 1 END) as known_cost_line_count')
            ->selectRaw('COUNT(CASE WHEN invoice_items.unit_cost_at_sale IS NOT NULL THEN 1 END) as snapshot_cost_line_count')
            ->selectRaw('SUM(CASE WHEN COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) IS NOT NULL AND COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) >= 0 THEN (invoice_items.total - COALESCE(invoice_items.discount_amount, invoice_items.total * COALESCE(invoices.remise_percentage, 0) / 100)) ELSE 0 END) as known_cost_revenue')
            ->selectRaw('SUM(CASE WHEN COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) IS NOT NULL AND COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) >= 0 THEN COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) * invoice_items.quantity ELSE 0 END) as known_cost_purchase_cost')
            ->first();
        $lineCount = (int) ($summary->line_count ?? 0);
        $knownLineCount = (int) ($summary->known_cost_line_count ?? 0);
        $snapshotLineCount = (int) ($summary->snapshot_cost_line_count ?? 0);
        $revenue = (float) ($summary->known_cost_revenue ?? 0);
        $purchaseCost = (float) ($summary->known_cost_purchase_cost ?? 0);
        $profit = $revenue - $purchaseCost;

        return [
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'summary' => [
                'known_cost_revenue' => round($revenue, 2),
                'known_cost_purchase_cost' => round($purchaseCost, 2),
                'gross_profit' => round($profit, 2),
                'gross_margin_percent' => $revenue > 0 ? round(($profit / $revenue) * 100, 2) : null,
                'line_count' => $lineCount,
                'known_cost_line_count' => $knownLineCount,
                'missing_cost_line_count' => max(0, $lineCount - $knownLineCount),
                'snapshot_cost_line_count' => $snapshotLineCount,
                'estimated_cost_line_count' => max(0, $knownLineCount - $snapshotLineCount),
                'confidence' => $this->confidence($lineCount, $knownLineCount, $snapshotLineCount),
            ],
            'products' => $this->grouped($baseQuery, 'product'),
            'customers' => $this->grouped($baseQuery, 'customer'),
            'salespeople' => $this->grouped($baseQuery, 'salesperson'),
            'notice' => 'Gross profit includes only invoice lines with a known cost. Lines without a cost are excluded rather than assumed to have zero cost.',
        ];
    }

    private function baseQuery(Carbon $from, Carbon $to)
    {
        return DB::table('invoice_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->leftJoin('products', 'products.id', '=', 'invoice_items.product_id')
            ->leftJoin('clients', 'clients.id', '=', 'invoices.client_id')
            ->leftJoin('users', 'users.id', '=', 'invoices.created_by')
            ->whereDate('invoices.invoice_date', '>=', $from->toDateString())
            ->whereDate('invoices.invoice_date', '<=', $to->toDateString());
    }

    /** @return array<int, object> */
    private function grouped($query, string $dimension): array
    {
        $selectLabel = match ($dimension) {
            'product' => "COALESCE(products.name, 'Produit supprimé') as label",
            'customer' => "COALESCE(clients.nom, invoices.client_name, 'Client non attribué') as label",
            'salesperson' => "COALESCE(users.name, 'Non attribué') as label",
            default => "COALESCE(products.name, 'Produit supprimé') as label",
        };

        $rows = (clone $query)
            ->where(function ($query) {
                $query->whereNotNull('invoice_items.unit_cost_at_sale')
                    ->orWhereNotNull('products.cost_price');
            })
            ->selectRaw($selectLabel)
            ->selectRaw('SUM(CASE WHEN COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) IS NOT NULL AND COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) >= 0 THEN (invoice_items.total - COALESCE(invoice_items.discount_amount, invoice_items.total * COALESCE(invoices.remise_percentage, 0) / 100)) ELSE 0 END) as revenue')
            ->selectRaw('SUM(CASE WHEN COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) IS NOT NULL AND COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) >= 0 THEN COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) * invoice_items.quantity ELSE 0 END) as purchase_cost')
            ->selectRaw('SUM(CASE WHEN COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) IS NOT NULL AND COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) >= 0 THEN (invoice_items.total - COALESCE(invoice_items.discount_amount, invoice_items.total * COALESCE(invoices.remise_percentage, 0) / 100)) - (COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) * invoice_items.quantity) ELSE 0 END) as gross_profit')
            ->selectRaw('SUM(CASE WHEN COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) IS NOT NULL AND COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) >= 0 THEN invoice_items.quantity ELSE 0 END) as quantity')
            ->groupBy('label')
            ->orderByDesc('gross_profit')
            ->limit(10)
            ->get();

        return $rows->map(function ($row) {
            $revenue = (float) ($row->revenue ?? 0);
            $grossProfit = (float) ($row->gross_profit ?? 0);

            return (object) [
                'label' => $row->label,
                'revenue' => round($revenue, 2),
                'purchase_cost' => round((float) ($row->purchase_cost ?? 0), 2),
                'gross_profit' => round($grossProfit, 2),
                'quantity' => (int) ($row->quantity ?? 0),
                'gross_margin_percent' => $revenue > 0 ? round(($grossProfit / $revenue) * 100, 2) : null,
            ];
        })->all();
    }

    private function confidence(int $lineCount, int $knownLineCount, int $snapshotLineCount): string
    {
        if ($lineCount === 0 || $knownLineCount === 0) {
            return 'Data unavailable';
        }

        if ($snapshotLineCount === $lineCount) {
            return 'High';
        }

        if ($knownLineCount === $lineCount) {
            return 'Medium';
        }
        return 'Low';
    }
}
