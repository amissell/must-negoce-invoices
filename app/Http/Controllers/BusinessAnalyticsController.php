<?php

namespace App\Http\Controllers;

use App\Services\Analytics\AnalyticsDateRange;
use App\Services\Analytics\DataGapService;
use App\Services\Analytics\DataQualityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BusinessAnalyticsController extends Controller
{
    public function __construct(
        private AnalyticsDateRange $dateRange,
        private DataQualityService $dataQuality,
        private DataGapService $dataGaps,
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('view-dashboard');
        $range = $this->dateRange->fromRequest($request);

        return view('pages.analytics.foundation', [
            'dataQuality' => $this->dataQuality->report($range['from'], $range['to']),
            'dataGaps' => $this->dataGaps->report(),
        ]);
    }

    public function data(Request $request)
    {
        Gate::authorize('view-dashboard');
        $range = $this->dateRange->fromRequest($request);

        return response()->json([
            'data_quality' => $this->dataQuality->report($range['from'], $range['to']),
            'data_gaps' => $this->dataGaps->report(),
        ]);
    }
}
