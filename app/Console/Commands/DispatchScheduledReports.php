<?php

namespace App\Console\Commands;

use App\Jobs\GenerateScheduledReport;
use App\Models\ScheduledReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class DispatchScheduledReports extends Command
{
    protected $signature = 'reports:dispatch-due';
    protected $description = 'Dispatch scheduled reports that are due';

    public function handle()
    {
        $now = now();
        $due = ScheduledReport::where('status', 'active')
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<=', $now)
            ->get();

        $dispatched = 0;

        foreach ($due as $report) {
            if ($report->recurrence === 'none') {
                $report->update(['status' => 'inactive', 'next_run_at' => null]);
                continue;
            }

            $guardKey = 'scheduled_report_dispatch_'.$report->id;
            if (Cache::has($guardKey)) {
                continue;
            }

            Cache::put($guardKey, $now->toDateTimeString(), now()->addMinutes(5));

            try {
                $freshReport = $report->fresh();
                if (! $freshReport || $freshReport->status !== 'active') {
                    continue;
                }

                if ($freshReport->recurrence === 'none' || $freshReport->next_run_at === null || $freshReport->next_run_at->gt($now)) {
                    continue;
                }

                GenerateScheduledReport::dispatch($freshReport);
                $dispatched++;
            } catch (\Throwable $e) {
                Cache::forget($guardKey);
                throw $e;
            }
        }

        $this->info('Dispatched '.$dispatched.' scheduled reports.');
        return 0;
    }
}
