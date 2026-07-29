<?php

namespace App\Http\Controllers;

use App\Services\ExecutiveDashboardService;

class ExecutiveDashboardController extends Controller
{
    public function __construct(private ExecutiveDashboardService $dashboard)
    {
    }

    public function index()
    {
        $data = $this->dashboard->getDashboardData();

        return view('pages.dashboards.executive', $data);
    }

    public function data()
    {
        return response()->json($this->dashboard->getDashboardData());
    }

    /**
     * Return only the sales pulse payload (small, used by lightweight widgets).
     */
    public function salesPulse()
    {
        return response()->json($this->dashboard->salesPulse());
    }

    /**
     * CEO Scorecard — 5-number summary intended for the top-of-dashboard KPI row.
     */
    public function ceoScorecard()
    {
        $scorecard = [
            'mtd_revenue' => $this->dashboard->currentMonthRevenue(),
            'mtd_vs_target' => $this->dashboard->companyTarget(),
            'outstanding_receivables' => $this->dashboard->receivablesSummary()['total'] ?? 0.0,
            'active_clients' => $this->dashboard->activeClients(),
            'overdue_invoices' => $this->dashboard->overdueInvoiceCount(),
        ];

        return response()->json($scorecard);
    }
}
