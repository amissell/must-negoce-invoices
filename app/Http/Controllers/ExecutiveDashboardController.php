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
}
