<?php

namespace App\Http\Controllers;

use App\Services\Analytics\AnalyticsDateRange;
use App\Services\Analytics\ProfitabilityAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProfitabilityAnalyticsController extends Controller
{
    public function __construct(private AnalyticsDateRange $dateRange, private ProfitabilityAnalyticsService $analytics) {}

    public function index(Request $request)
    {
        Gate::authorize('view-dashboard');
        $range = $this->dateRange->fromRequest($request);

        return view('pages.analytics.profitability', ['analytics' => $this->analytics->report($range['from'], $range['to'])]);
    }

    public function data(Request $request)
    {
        Gate::authorize('view-dashboard');
        $range = $this->dateRange->fromRequest($request);

        return response()->json($this->analytics->report($range['from'], $range['to']));
    }

    public function details(Request $request)
    {
        Gate::authorize('view-dashboard');
        $data = $request->validate([
            'dimension' => ['required', 'in:product,customer,salesperson'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $range = $this->dateRange->fromRequest($request);
        $query = $this->analytics->report($range['from'], $range['to']);

        $items = $this->analyticsDetailRows($data['dimension'], $range['from'], $range['to']);

        return response()->json([
            'data' => $items,
            'total' => count($items),
            'period' => ['from' => $range['from']->toDateString(), 'to' => $range['to']->toDateString()],
        ]);
    }

    public function detailsExport(Request $request)
    {
        Gate::authorize('view-dashboard');
        $data = $request->validate([
            'dimension' => ['required', 'in:product,customer,salesperson'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $range = $this->dateRange->fromRequest($request);
        $items = $this->analyticsDetailRows($data['dimension'], $range['from'], $range['to']);

        $filename = sprintf('profitability_details_%s_%s.csv', $range['from']->format('Ymd'), $range['to']->format('Ymd'));

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($items) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['label', 'revenue', 'purchase_cost', 'gross_profit', 'gross_margin_percent', 'quantity']);

            foreach ($items as $row) {
                fputcsv($out, [
                    (string) $row['label'],
                    (float) $row['revenue'],
                    (float) $row['purchase_cost'],
                    (float) $row['gross_profit'],
                    $row['gross_margin_percent'] ?? '',
                    (int) $row['quantity'],
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function export(Request $request)
    {
        Gate::authorize('view-dashboard');
        $range = $this->dateRange->fromRequest($request);
        $report = $this->analytics->report($range['from'], $range['to']);

        $filename = sprintf('profitability_%s_%s.csv', $range['from']->format('Ymd'), $range['to']->format('Ymd'));

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($report) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['label', 'revenue', 'purchase_cost', 'gross_profit', 'gross_margin_percent', 'quantity']);

            foreach ($report['products'] as $row) {
                fputcsv($out, [
                    (string) $row->label,
                    (float) $row->revenue,
                    (float) $row->purchase_cost,
                    (float) $row->gross_profit,
                    $row->gross_margin_percent ?? '',
                    (int) $row->quantity,
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function analyticsDetailRows(string $dimension, \Carbon\Carbon $from, \Carbon\Carbon $to): array
    {
        $query = \Illuminate\Support\Facades\DB::table('invoice_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->leftJoin('products', 'products.id', '=', 'invoice_items.product_id')
            ->leftJoin('clients', 'clients.id', '=', 'invoices.client_id')
            ->leftJoin('users', 'users.id', '=', 'invoices.created_by')
            ->whereDate('invoices.invoice_date', '>=', $from->toDateString())
            ->whereDate('invoices.invoice_date', '<=', $to->toDateString())
            ->where(function ($query) {
                $query->whereNotNull('invoice_items.unit_cost_at_sale')
                    ->orWhereNotNull('products.cost_price');
            });

        $labelSql = match ($dimension) {
            'product' => "COALESCE(products.name, 'Produit supprimé')",
            'customer' => "COALESCE(clients.nom, invoices.client_name, 'Client non attribué')",
            'salesperson' => "COALESCE(users.name, 'Non attribué')",
            default => "COALESCE(products.name, 'Produit supprimé')",
        };

        return $query
            ->selectRaw("{$labelSql} as label")
            ->selectRaw('SUM(CASE WHEN COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) IS NOT NULL AND COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) >= 0 THEN (invoice_items.total - COALESCE(invoice_items.discount_amount, invoice_items.total * COALESCE(invoices.remise_percentage, 0) / 100)) ELSE 0 END) as revenue')
            ->selectRaw('SUM(CASE WHEN COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) IS NOT NULL AND COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) >= 0 THEN COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) * invoice_items.quantity ELSE 0 END) as purchase_cost')
            ->selectRaw('SUM(CASE WHEN COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) IS NOT NULL AND COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) >= 0 THEN (invoice_items.total - COALESCE(invoice_items.discount_amount, invoice_items.total * COALESCE(invoices.remise_percentage, 0) / 100)) - (COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) * invoice_items.quantity) ELSE 0 END) as gross_profit')
            ->selectRaw('SUM(CASE WHEN COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) IS NOT NULL AND COALESCE(invoice_items.unit_cost_at_sale, products.cost_price) >= 0 THEN invoice_items.quantity ELSE 0 END) as quantity')
            ->groupBy('label')
            ->orderByDesc('gross_profit')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $revenue = (float) ($row->revenue ?? 0);
                $grossProfit = (float) ($row->gross_profit ?? 0);

                return [
                    'label' => $row->label,
                    'revenue' => round($revenue, 2),
                    'purchase_cost' => round((float) ($row->purchase_cost ?? 0), 2),
                    'gross_profit' => round($grossProfit, 2),
                    'quantity' => (int) ($row->quantity ?? 0),
                    'gross_margin_percent' => $revenue > 0 ? round(($grossProfit / $revenue) * 100, 2) : null,
                ];
            })
            ->all();
    }
}
