<?php

namespace App\Http\Controllers;

use App\Services\Analytics\AnalyticsDateRange;
use App\Services\Analytics\CommercialAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommercialAnalyticsController extends Controller
{
    public function __construct(private AnalyticsDateRange $dateRange, private CommercialAnalyticsService $analytics) {}

    public function index(Request $request)
    {
        Gate::authorize('view-dashboard');
        $range = $this->dateRange->fromRequest($request);

        return view('pages.analytics.commercial', ['analytics' => $this->analytics->report($range['from'], $range['to'])]);
    }

    public function data(Request $request)
    {
        Gate::authorize('view-dashboard');
        $range = $this->dateRange->fromRequest($request);

        return response()->json($this->analytics->report($range['from'], $range['to']));
    }
}
