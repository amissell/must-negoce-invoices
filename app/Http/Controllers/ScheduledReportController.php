<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateScheduledReport;
use App\Models\ScheduledReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ScheduledReportController extends Controller
{
    public function index()
    {
        $reports = ScheduledReport::orderBy('id', 'desc')->get();
        return response()->json($reports);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'report_type' => 'required|string|in:margins,tva,kpis',
            'params' => 'nullable|array',
            'recipients' => 'nullable|array',
            'recipients.*' => 'email',
            'format' => 'required|string|in:csv,xlsx',
            'recurrence' => 'required|string|in:none,daily,weekly,monthly',
            'next_run_at' => 'nullable|date',
            'retention_days' => 'nullable|integer|min:1',
        ]);

        $report = ScheduledReport::create(array_merge($data, ['user_id' => Auth::id()]));

        return response()->json($report, 201);
    }

    public function destroy(ScheduledReport $scheduledReport)
    {
        $scheduledReport->delete();
        return response()->json(null, 204);
    }

    public function runNow(ScheduledReport $scheduledReport)
    {
        GenerateScheduledReport::dispatch($scheduledReport);
        return response()->json(['message' => 'dispatched']);
    }
}
