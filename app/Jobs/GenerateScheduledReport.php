<?php

namespace App\Jobs;

use App\Models\ScheduledReport;
use App\Models\ScheduledReportRun;
use App\Services\ExecutiveDashboardService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class GenerateScheduledReport implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(public ScheduledReport $report)
    {
        $this->report = $report->fresh();
    }

    public function handle(ExecutiveDashboardService $dashboard)
    {
        $lockKey = 'scheduled_report_lock_'.$this->report->id;
        $lock = Cache::lock($lockKey, 300);
        if (! $lock->get()) {
            // already running
            return;
        }

        $run = ScheduledReportRun::create([
            'scheduled_report_id' => $this->report->id,
            'started_at' => now(),
            'status' => 'running',
        ]);

        $start = microtime(true);

        try {
            $params = $this->report->params ?? [];
            $format = $this->report->format ?? 'csv';
            $disk = config('reports.disk', 'local');
            $dir = 'reports/'.$this->report->id;
            $filename = sprintf('%s_%s.%s', $this->report->report_type, now()->format('YmdHis'), $format === 'xlsx' ? 'xlsx' : 'csv');
            $path = $dir.'/'.$filename;

            // generate content by reusing existing service queries
            if ($this->report->report_type === 'margins') {
                $dimension = $params['dimension'] ?? 'product';
                $from = isset($params['from']) ? \Carbon\Carbon::parse($params['from']) : null;
                $to = isset($params['to']) ? \Carbon\Carbon::parse($params['to']) : null;
                $qb = $dashboard->marginDetails($dimension, $from, $to)->orderBy('margin', 'desc');
                // stream to storage
                $stream = fopen('php://temp', 'w+');
                fputcsv($stream, ['label', 'margin', 'revenue', 'qty', 'margin_percent']);
                foreach ($qb->cursor() as $row) {
                    $margin = (float) $row->margin;
                    $revenue = (float) $row->revenue;
                    $qty = (int) $row->qty;
                    $pct = $revenue > 0 ? ($margin / $revenue) * 100 : '';
                    fputcsv($stream, [(string) $row->label, $margin, $revenue, $qty, $pct]);
                }
                rewind($stream);
                Storage::disk($disk)->put($path, stream_get_contents($stream));
                fclose($stream);
            } elseif ($this->report->report_type === 'tva') {
                $from = isset($params['from']) ? \Carbon\Carbon::parse($params['from']) : null;
                $to = isset($params['to']) ? \Carbon\Carbon::parse($params['to']) : null;
                $qb = $dashboard->tvaDetails($from, $to)->orderBy('invoice_date', 'desc');
                $stream = fopen('php://temp', 'w+');
                fputcsv($stream, ['invoice_id', 'invoice_number', 'invoice_date', 'client_label', 'tva_amount']);
                foreach ($qb->cursor() as $row) {
                    fputcsv($stream, [$row->invoice_id, $row->invoice_number, $row->invoice_date, $row->client_label, (float) $row->tva_amount]);
                }
                rewind($stream);
                Storage::disk($disk)->put($path, stream_get_contents($stream));
                fclose($stream);
            } else {
                // fallback: kpis json -> csv
                $data = $dashboard->getDashboardData();
                $stream = fopen('php://temp', 'w+');
                fputcsv($stream, ['key', 'value']);
                foreach ($data as $k => $v) {
                    if (is_array($v)) {
                        fputcsv($stream, [$k, json_encode($v)]);
                    } else {
                        fputcsv($stream, [$k, (string) $v]);
                    }
                }
                rewind($stream);
                Storage::disk($disk)->put($path, stream_get_contents($stream));
                fclose($stream);
            }

            $size = Storage::disk($disk)->size($path);

            $run->update([
                'finished_at' => now(),
                'duration_ms' => (int) ((microtime(true) - $start) * 1000),
                'status' => 'success',
                'file_path' => $path,
                'file_size' => $size,
            ]);

            // email attachments to recipients
            $recipients = $this->report->recipients ?? [];
            foreach ($recipients as $email) {
                try {
                    \Mail::to($email)->send(new \App\Mail\ScheduledReportReady($this->report, $run));
                } catch (Throwable $e) {
                    // log and continue
                }
            }

            // update last_run_at and compute next_run_at for recurrence
            $this->report->update(['last_run_at' => now()]);
            // simple recurrence handling omitted for brevity; command will recompute next_run_at if needed

        } catch (Throwable $e) {
            $run->update(['finished_at' => now(), 'duration_ms' => (int) ((microtime(true) - $start) * 1000), 'status' => 'failed', 'error_message' => $e->getMessage()]);
            throw $e;
        } finally {
            $lock->release();
        }
    }
}
