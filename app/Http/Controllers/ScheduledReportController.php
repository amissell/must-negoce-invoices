<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateScheduledReport;
use App\Models\ScheduledReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ScheduledReportController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', ScheduledReport::class);

        $reports = ScheduledReport::with('runs')->orderBy('id', 'desc')->get();
        return response()->json($reports);
    }

    public function store(Request $request)
    {
        $this->authorize('create', ScheduledReport::class);

        $data = $request->validate([
            'name' => 'required|string',
            'report_type' => 'required|string|in:margins,tva,kpis,profitability',
            'params' => 'nullable|array',
            'params.dimension' => 'nullable|string|in:product,customer,salesperson,client,seller',
            'params.from' => 'nullable|date',
            'params.to' => 'nullable|date',
            'recipients' => 'nullable|array',
            'recipients.*' => 'email',
            'format' => 'required|string|in:csv,xlsx',
            'recurrence' => 'required|string|in:none,daily,weekly,monthly',
            'next_run_at' => 'nullable|date',
            'retention_days' => 'nullable|integer|min:1',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $report = ScheduledReport::create(array_merge($data, [
            'user_id' => Auth::id(),
            'status' => $data['status'] ?? 'active',
            'params' => $data['params'] ?? [],
            'recipients' => $data['recipients'] ?? [],
        ]));

        return response()->json($report, 201);
    }

    public function update(Request $request, ScheduledReport $scheduledReport)
    {
        $this->authorize('update', $scheduledReport);

        $data = $request->validate([
            'name' => 'sometimes|string',
            'report_type' => 'sometimes|string|in:margins,tva,kpis,profitability',
            'params' => 'sometimes|nullable|array',
            'params.dimension' => 'nullable|string|in:product,customer,salesperson,client,seller',
            'params.from' => 'nullable|date',
            'params.to' => 'nullable|date',
            'recipients' => 'sometimes|nullable|array',
            'recipients.*' => 'email',
            'format' => 'sometimes|string|in:csv,xlsx',
            'recurrence' => 'sometimes|string|in:none,daily,weekly,monthly',
            'next_run_at' => 'sometimes|nullable|date',
            'retention_days' => 'sometimes|nullable|integer|min:1',
            'status' => 'sometimes|string|in:active,inactive',
        ]);

        $scheduledReport->fill($data);
        $scheduledReport->save();

        return response()->json($scheduledReport->fresh());
    }

    public function destroy(ScheduledReport $scheduledReport)
    {
        $this->authorize('delete', $scheduledReport);
        $scheduledReport->delete();
        return response()->json(null, 204);
    }

    public function runNow(ScheduledReport $scheduledReport)
    {
        $this->authorize('execute', $scheduledReport);
        GenerateScheduledReport::dispatch($scheduledReport);
        return response()->json(['message' => 'dispatched']);
    }

    public function downloadLatest(Request $request, ScheduledReport $scheduledReport)
    {
        $this->authorize('export', $scheduledReport);

        $run = $scheduledReport->runs()->latest('finished_at')->latest()->first();
        if (! $run || ! $run->file_path) {
            return response()->json(['message' => 'No generated report available.'], 404);
        }

        $disk = config('reports.disk', 'local');
        if (! Storage::disk($disk)->exists($run->file_path)) {
            return response()->json(['message' => 'Generated file not found.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'report_id' => $scheduledReport->id,
            'run_id' => $run->id,
            'file_name' => basename($run->file_path),
            'download_url' => URL::temporarySignedRoute('admin.scheduled_reports.download', now()->addMinutes(30), ['run' => $run->id]),
        ]);
    }
}
