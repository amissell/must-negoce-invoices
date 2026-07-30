<?php

namespace App\Console\Commands;

use App\Jobs\GenerateScheduledReport;
use App\Models\ScheduledReport;
use Illuminate\Console\Command;

class DispatchScheduledReports extends Command
{
    protected $signature = 'reports:dispatch-due';
    protected $description = 'Dispatch scheduled reports that are due';

    public function handle()
    {
        $now = now();
        $due = ScheduledReport::whereNotNull('next_run_at')->where('next_run_at', '<=', $now)->where('status', 'active')->get();
        foreach ($due as $report) {
            GenerateScheduledReport::dispatch($report);
            // compute next_run_at simple recurrence
            if ($report->recurrence === 'daily') {
                $report->next_run_at = $report->next_run_at->addDay();
            } elseif ($report->recurrence === 'weekly') {
                $report->next_run_at = $report->next_run_at->addWeek();
            } elseif ($report->recurrence === 'monthly') {
                $report->next_run_at = $report->next_run_at->addMonth();
            } else {
                $report->status = 'inactive';
            }
            $report->save();
        }

        $this->info('Dispatched '.$due->count().' scheduled reports.');
        return 0;
    }
}
