<?php

namespace App\Http\Controllers;

use App\Models\CompanyTarget;
use App\Services\ExecutiveDashboardService;
use Illuminate\Http\Request;

class CompanyTargetController extends Controller
{
    public function index()
    {
        return response()->json(
            CompanyTarget::query()->orderByDesc('year')->orderByDesc('month')->get(),
        );
    }

    public function upsert(Request $request, ExecutiveDashboardService $dashboard)
    {
        $data = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'target_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $target = CompanyTarget::updateOrCreate(
            ['month' => $data['month'], 'year' => $data['year']],
            ['target_amount' => $data['target_amount']],
        );

        $dashboard->flushCache();

        return response()->json($target, 201);
    }

    public function destroy(CompanyTarget $companyTarget, ExecutiveDashboardService $dashboard)
    {
        $companyTarget->delete();
        $dashboard->flushCache();

        return response()->noContent();
    }
}
