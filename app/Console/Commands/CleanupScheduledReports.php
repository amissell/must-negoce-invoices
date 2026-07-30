<?php

namespace App\Console\Commands;

use App\Models\ScheduledReportRun;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupScheduledReports extends Command
{
    protected $signature = 'reports:cleanup';
    protected $description = 'Cleanup old scheduled report files and run history according to retention_days';

    public function handle()
    {
        $disk = config('reports.disk', 'local');

        $now = now();
        $runs = ScheduledReportRun::whereNotNull('finished_at')->get();
        $deleted = 0;
        foreach ($runs as $run) {
            $retention = $run->report->retention_days ?? 30;
            $expiry = $run->finished_at->addDays($retention);
            if ($now->greaterThan($expiry)) {
                if ($run->file_path && Storage::disk($disk)->exists($run->file_path)) {
                    Storage::disk($disk)->delete($run->file_path);
                }
                $run->delete();
                $deleted++;
            }
        }

        $this->info('Cleaned '.$deleted.' runs.');
        return 0;
    }
}
