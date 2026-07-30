<?php

namespace App\Mail;

use App\Models\ScheduledReport;
use App\Models\ScheduledReportRun;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ScheduledReportReady extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ScheduledReport $report, public ScheduledReportRun $run)
    {
    }

    public function build()
    {
        $disk = config('reports.disk', 'local');
        $mail = $this->subject('Scheduled report: '.$this->report->name)
            ->view('emails.scheduled_report_ready')
            ->with(['report' => $this->report, 'run' => $this->run]);

        if ($this->run->file_path && Storage::disk($disk)->exists($this->run->file_path)) {
            $mail->attach(Storage::disk($disk)->path($this->run->file_path));
        }

        return $mail;
    }
}
