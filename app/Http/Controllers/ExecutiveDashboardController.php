<?php

namespace App\Http\Controllers;

use App\Services\ExecutiveDashboardService;
use App\Services\PdfService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class ExecutiveDashboardController extends Controller
{
    public function __construct(private ExecutiveDashboardService $dashboard, private PdfService $pdfService)
    {
    }

    public function index()
    {
        // Authorize: only admin and superviseur can view dashboard
        Gate::authorize('view-dashboard');

        $data = $this->dashboard->getDashboardData();

        return view('pages.dashboards.executive', $data);
    }

    public function data()
    {
        // Authorize: only admin and superviseur can view dashboard data
        Gate::authorize('view-dashboard');

        return response()->json($this->dashboard->getDashboardData());
    }

    /**
     * Return only the sales pulse payload (small, used by lightweight widgets).
     */
    public function salesPulse()
    {
        Gate::authorize('view-dashboard');

        return response()->json($this->dashboard->salesPulse());
    }

    /**
     * Gross margin breakdown used by financial widgets (products / clients / sellers).
     */
    public function grossMargin()
    {
        Gate::authorize('view-dashboard');

        return response()->json($this->dashboard->grossMarginAnalysis());
    }

    public function marginDetails()
    {
        Gate::authorize('view-dashboard');

        $data = request()->validate([
            'dimension' => ['required', 'string'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
            'sort' => ['nullable', 'string'],
            'dir' => ['nullable', 'in:asc,desc'],
        ]);

        $allowed = ['product', 'client', 'seller'];
        if (!in_array($data['dimension'], $allowed, true)) {
            return response()->json(['message' => 'Invalid dimension'], 422);
        }

        $from = isset($data['from']) ? \Carbon\Carbon::parse($data['from']) : null;
        $to = isset($data['to']) ? \Carbon\Carbon::parse($data['to']) : null;

        if ($from && $to && $from->gt($to)) {
            return response()->json(['message' => 'Invalid date range: from must be <= to'], 422);
        }

        // default to current month
        if (!$from && !$to) {
            $from = \Carbon\Carbon::today()->startOfMonth();
            $to = \Carbon\Carbon::today()->endOfMonth();
        }

        $qb = $this->dashboard->marginDetails($data['dimension'], $from, $to);

        $sort = $data['sort'] ?? 'margin';
        $dir = $data['dir'] ?? 'desc';

        $sortMap = [
            'margin' => 'margin',
            'revenue' => 'revenue',
            'qty' => 'qty',
            'label' => 'label',
        ];

        if ($sort === 'margin_percent') {
            // order by margin/revenue (avoid division by zero)
            $expr = '(SUM((invoice_items.unit_price - products.cost_price) * invoice_items.quantity) / NULLIF(SUM(invoice_items.unit_price * invoice_items.quantity),0))';
            $qb = $qb->orderByRaw("{$expr} {$dir}");
        } elseif (isset($sortMap[$sort])) {
            $qb = $qb->orderBy($sortMap[$sort], $dir);
        }

        $perPage = $data['per_page'] ?? 25;

        $results = $qb->paginate($perPage)->appends(request()->query());

        // compute margin_percent client-side consumer friendly
        $results->getCollection()->transform(function ($row) {
            $row->margin = (float) $row->margin;
            $row->revenue = (float) $row->revenue;
            $row->qty = (int) $row->qty;
            $row->margin_percent = $row->revenue > 0 ? ($row->margin / $row->revenue) * 100 : null;
            return $row;
        });

        return response()->json($results);
    }

    public function marginExport()
    {
        // Authorize: only admin and superviseur can export
        Gate::authorize('view-dashboard');

        $data = request()->validate([
            'dimension' => ['required', 'string'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'sort' => ['nullable', 'string'],
            'dir' => ['nullable', 'in:asc,desc'],
        ]);

        $allowed = ['product', 'client', 'seller'];
        if (!in_array($data['dimension'], $allowed, true)) {
            return response()->json(['message' => 'Invalid dimension'], 422);
        }

        $from = isset($data['from']) ? \Carbon\Carbon::parse($data['from']) : null;
        $to = isset($data['to']) ? \Carbon\Carbon::parse($data['to']) : null;
        if ($from && $to && $from->gt($to)) {
            return response()->json(['message' => 'Invalid date range: from must be <= to'], 422);
        }

        if (!$from && !$to) {
            $from = \Carbon\Carbon::today()->startOfMonth();
            $to = \Carbon\Carbon::today()->endOfMonth();
        }

        $qb = $this->dashboard->marginDetails($data['dimension'], $from, $to)->orderBy('margin', 'desc');

        // streaming CSV
        $filename = sprintf(
            'margins_%s_%s_%s.csv',
            $data['dimension'],
            $from?->format('Ymd') ?? 'all',
            $to?->format('Ymd') ?? 'all',
        );

        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];

        $callback = function () use ($qb) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['label', 'margin', 'revenue', 'qty', 'margin_percent']);
            foreach ($qb->cursor() as $row) {
                $margin = (float) $row->margin;
                $revenue = (float) $row->revenue;
                $qty = (int) $row->qty;
                $pct = $revenue > 0 ? ($margin / $revenue) * 100 : '';
                fputcsv($out, [$this->csvSafeText($row->label), $margin, $revenue, $qty, $pct]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * CEO Scorecard — 5-number summary intended for the top-of-dashboard KPI row.
     */
    public function ceoScorecard()
    {
        // Authorize: only admin and superviseur can view scorecard
        Gate::authorize('view-dashboard');

        $scorecard = [
            'mtd_revenue' => $this->dashboard->currentMonthRevenue(),
            'mtd_vs_target' => $this->dashboard->companyTarget(),
            'outstanding_receivables' => $this->dashboard->receivablesSummary()['total'] ?? 0.0,
            'active_clients' => $this->dashboard->activeClients(),
            'overdue_invoices' => $this->dashboard->overdueInvoiceCount(),
        ];

        return response()->json($scorecard);
    }

    /**
     * TVA / tax liability series for the last N months (default 12).
     */
    public function tvaLiability()
    {
        Gate::authorize('view-dashboard');

        $data = request()->validate([
            'months' => ['sometimes', 'integer', 'min:1', 'max:24'],
        ]);
        $months = (int) ($data['months'] ?? 12);

        return response()->json($this->dashboard->tvaLiabilityByMonth($months));
    }

    public function tvaDetails()
    {
        Gate::authorize('view-dashboard');

        $data = request()->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
            'sort' => ['nullable', 'string'],
            'dir' => ['nullable', 'in:asc,desc'],
        ]);

        $from = isset($data['from']) ? \Carbon\Carbon::parse($data['from']) : null;
        $to = isset($data['to']) ? \Carbon\Carbon::parse($data['to']) : null;
        if ($from && $to && $from->gt($to)) {
            return response()->json(['message' => 'Invalid date range: from must be <= to'], 422);
        }

        if (!$from && !$to) {
            $from = \Carbon\Carbon::today()->startOfMonth();
            $to = \Carbon\Carbon::today()->endOfMonth();
        }

        $qb = $this->dashboard->tvaDetails($from, $to);

        $sort = $data['sort'] ?? 'invoice_date';
        $dir = $data['dir'] ?? 'desc';

        $sortMap = [
            'invoice_date' => 'invoice_date',
            'invoice_number' => 'invoice_number',
            'tva_amount' => 'tva_amount',
            'client_label' => 'client_label',
        ];

        if (isset($sortMap[$sort])) {
            $qb = $qb->orderBy($sortMap[$sort], $dir);
        }

        $perPage = $data['per_page'] ?? 25;
        $results = $qb->paginate($perPage)->appends(request()->query());

        return response()->json($results);
    }

    public function tvaExport()
    {
        // Authorize: only admin and superviseur can export
        Gate::authorize('view-dashboard');

        $data = request()->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'sort' => ['nullable', 'string'],
            'dir' => ['nullable', 'in:asc,desc'],
        ]);

        $from = isset($data['from']) ? \Carbon\Carbon::parse($data['from']) : null;
        $to = isset($data['to']) ? \Carbon\Carbon::parse($data['to']) : null;
        if ($from && $to && $from->gt($to)) {
            return response()->json(['message' => 'Invalid date range: from must be <= to'], 422);
        }

        if (!$from && !$to) {
            $from = \Carbon\Carbon::today()->startOfMonth();
            $to = \Carbon\Carbon::today()->endOfMonth();
        }

        $qb = $this->dashboard->tvaDetails($from, $to)->orderBy('invoice_date', 'desc');

        $filename = sprintf(
            'tva_%s_%s.csv',
            $from?->format('Ymd') ?? 'all',
            $to?->format('Ymd') ?? 'all',
        );
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];

        $callback = function () use ($qb) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['invoice_id', 'invoice_number', 'invoice_date', 'client_label', 'tva_amount']);
            foreach ($qb->cursor() as $row) {
                fputcsv($out, [$row->invoice_id, $this->csvSafeText($row->invoice_number), $row->invoice_date, $this->csvSafeText($row->client_label), (float) $row->tva_amount]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate a print-friendly executive board PDF and stream it to the browser.
     */
    public function boardPdf()
    {
        // Authorize: only admin and superviseur can export PDF
        Gate::authorize('view-dashboard');

        $data = $this->dashboard->getDashboardData();

        $filename = sprintf('board_%s.pdf', now()->format('Ymd'));

        // landscape for wider layout
        return $this->pdfService->download('pages.dashboards.board', $data, $filename, 'A4', 'landscape');
    }

    /** Prefix spreadsheet formula-like text so CSV exports open as plain text. */
    private function csvSafeText(mixed $value): string
    {
        $text = (string) ($value ?? '');

        return preg_match('/^[\x00-\x20]*[=+\-@]/', $text) === 1
            ? "'{$text}"
            : $text;
    }
}
