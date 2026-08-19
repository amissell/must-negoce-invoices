<?php

namespace App\Http\Controllers;

use App\Models\ScheduledReportRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ScheduledReportDownloadController extends Controller
{
    public function downloadRun(Request $request, ScheduledReportRun $run)
    {
        // require signed url
        if (! $request->hasValidSignature()) {
            abort(403);
        }

        // route should be protected by auth middleware; signature is checked above

        $disk = config('reports.disk', 'local');
        if (! $run->file_path || ! Storage::disk($disk)->exists($run->file_path)) {
            abort(404);
        }

        $headers = [];
        if (str_ends_with(strtolower($run->file_path), '.xlsx')) {
            $headers['Content-Type'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        }

        return Storage::disk($disk)->download($run->file_path, basename($run->file_path), $headers);
    }
}
