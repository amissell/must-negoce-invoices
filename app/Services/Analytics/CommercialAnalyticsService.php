<?php

namespace App\Services\Analytics;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CommercialAnalyticsService
{
    /** @return array<string, mixed> */
    public function report(Carbon $from, Carbon $to): array
    {
        $invoices = DB::table('invoices')->whereBetween('invoice_date', [$from->toDateString(), $to->toDateString()]);
        $totalRevenue = (float) (clone $invoices)->sum('total_ttc');
        $customerRows = $this->customerQuery($from, $to)->get();
        $topFiveRevenue = (float) $customerRows->take(5)->sum('revenue');
        $topTenRevenue = (float) $customerRows->take(10)->sum('revenue');

        return [
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'customers' => [
                'total_revenue' => $totalRevenue,
                'top_5_concentration_percent' => $this->percent($topFiveRevenue, $totalRevenue),
                'top_10_concentration_percent' => $this->percent($topTenRevenue, $totalRevenue),
                'top_by_revenue' => $customerRows->take(10)->values(),
                'inactive_customers' => $this->inactiveCustomers(),
            ],
            'payments' => $this->paymentSummary($from, $to),
            'sales' => [
                'top_products' => $this->topProducts($from, $to),
                'top_cities' => $this->topCities($from, $to),
                'daily_revenue' => (clone $invoices)->selectRaw('invoice_date as date, SUM(total_ttc) as revenue')->groupBy('invoice_date')->orderBy('invoice_date')->get(),
                'discounts' => $this->discountSummary($from, $to),
            ],
            'limitations' => [
                'Revenue uses invoice totals and invoice-line totals recorded in the system.',
                'Payment delay is based on recorded payment dates; it is not necessarily the final settlement delay for partially paid invoices.',
                'Discount results describe observed invoices only and do not establish that discounts caused sales changes.',
            ],
        ];
    }

    private function customerQuery(Carbon $from, Carbon $to)
    {
        return DB::table('invoices')
            ->leftJoin('clients', 'clients.id', '=', 'invoices.client_id')
            ->whereBetween('invoices.invoice_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw("COALESCE(clients.nom, invoices.client_name, 'Client non attribué') as client")
            ->selectRaw('COUNT(invoices.id) as orders')
            ->selectRaw('SUM(invoices.total_ttc) as revenue')
            ->selectRaw('SUM(CASE WHEN invoices.total_ttc > COALESCE(invoices.montant_paye, 0) THEN invoices.total_ttc - COALESCE(invoices.montant_paye, 0) ELSE 0 END) as outstanding')
            ->groupBy('clients.id', 'clients.nom', 'invoices.client_name')
            ->orderByDesc('revenue');
    }

    /** @return array<int, object> */
    private function inactiveCustomers()
    {
        $cutoff = now()->subDays(90)->toDateString();

        return DB::table('clients')
            ->leftJoin('invoices', 'invoices.client_id', '=', 'clients.id')
            ->select('clients.nom as client')
            ->selectRaw('MAX(invoices.invoice_date) as last_purchase_date')
            ->groupBy('clients.id', 'clients.nom')
            ->havingRaw('MAX(invoices.invoice_date) IS NULL OR MAX(invoices.invoice_date) < ?', [$cutoff])
            ->orderBy('last_purchase_date')
            ->limit(10)
            ->get()
            ->all();
    }

    /** @return array<string, mixed> */
    private function paymentSummary(Carbon $from, Carbon $to): array
    {
        $paymentQuery = DB::table('paiements')
            ->join('invoices', 'invoices.id', '=', 'paiements.invoice_id')
            ->whereBetween('paiements.date_paiement', [$from->toDateString(), $to->toDateString()]);
        $delayExpression = DB::connection()->getDriverName() === 'sqlite'
            ? 'AVG(julianday(paiements.date_paiement) - julianday(invoices.invoice_date))'
            : 'AVG(DATEDIFF(paiements.date_paiement, invoices.invoice_date))';

        return [
            'recorded_amount' => (float) (clone $paymentQuery)->sum('paiements.montant'),
            'average_recorded_payment_days' => (float) ((clone $paymentQuery)->selectRaw($delayExpression.' as average_days')->value('average_days') ?? 0),
            'by_customer' => (clone $paymentQuery)
                ->leftJoin('clients', 'clients.id', '=', 'invoices.client_id')
                ->selectRaw("COALESCE(clients.nom, invoices.client_name, 'Client non attribué') as client")
                ->selectRaw('SUM(paiements.montant) as paid_amount')
                ->selectRaw($delayExpression.' as average_days')
                ->groupBy('clients.id', 'clients.nom', 'invoices.client_name')
                ->orderByDesc('paid_amount')
                ->limit(10)
                ->get(),
        ];
    }

    /** @return array<int, object> */
    private function topProducts(Carbon $from, Carbon $to): array
    {
        return DB::table('invoice_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->leftJoin('products', 'products.id', '=', 'invoice_items.product_id')
            ->whereBetween('invoices.invoice_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw("COALESCE(products.name, 'Produit supprimé') as product")
            ->selectRaw('SUM(invoice_items.quantity) as quantity')
            ->selectRaw('SUM(invoice_items.total) as revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->all();
    }

    /** @return array<int, object> */
    private function topCities(Carbon $from, Carbon $to): array
    {
        return DB::table('invoices')
            ->join('clients', 'clients.id', '=', 'invoices.client_id')
            ->whereBetween('invoices.invoice_date', [$from->toDateString(), $to->toDateString()])
            ->whereNotNull('clients.ville')
            ->where('clients.ville', '!=', '')
            ->selectRaw('clients.ville as city, SUM(invoices.total_ttc) as revenue')
            ->groupBy('clients.ville')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->all();
    }

    /** @return array<string, float|int> */
    private function discountSummary(Carbon $from, Carbon $to): array
    {
        $query = DB::table('invoices')->whereBetween('invoice_date', [$from->toDateString(), $to->toDateString()]);

        return [
            'discounted_invoices' => (clone $query)->where('remise_percentage', '>', 0)->count(),
            'average_discount_percent' => (float) ((clone $query)->where('remise_percentage', '>', 0)->avg('remise_percentage') ?? 0),
            'total_discount_amount' => (float) (clone $query)->sum('remise_amount'),
        ];
    }

    private function percent(float $value, float $total): ?float
    {
        return $total > 0 ? round(($value / $total) * 100, 2) : null;
    }
}
