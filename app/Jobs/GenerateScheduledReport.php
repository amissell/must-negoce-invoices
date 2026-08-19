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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

class GenerateScheduledReport implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(public ScheduledReport $report)
    {
        $this->report = $report->fresh();
    }

    protected function buildRowsForReport(ExecutiveDashboardService $dashboard, string $reportType, array $params): array
    {
        if ($reportType === 'margins') {
            $dimension = $params['dimension'] ?? 'product';
            $from = isset($params['from']) ? \Carbon\Carbon::parse($params['from']) : null;
            $to = isset($params['to']) ? \Carbon\Carbon::parse($params['to']) : null;
            $qb = $dashboard->marginDetails($dimension, $from, $to)->orderBy('margin', 'desc');

            $rows = [['label', 'margin', 'revenue', 'qty', 'margin_percent']];
            foreach ($qb->cursor() as $row) {
                $margin = (float) $row->margin;
                $revenue = (float) $row->revenue;
                $qty = (int) $row->qty;
                $pct = $revenue > 0 ? ($margin / $revenue) * 100 : '';
                $rows[] = [(string) $row->label, $margin, $revenue, $qty, $pct];
            }

            return $rows;
        }

        if ($reportType === 'tva') {
            $from = isset($params['from']) ? \Carbon\Carbon::parse($params['from']) : null;
            $to = isset($params['to']) ? \Carbon\Carbon::parse($params['to']) : null;
            $qb = $dashboard->tvaDetails($from, $to)->orderBy('invoice_date', 'desc');

            $rows = [['invoice_id', 'invoice_number', 'invoice_date', 'client_label', 'tva_amount']];
            foreach ($qb->cursor() as $row) {
                $rows[] = [$row->invoice_id, $row->invoice_number, $row->invoice_date, $row->client_label, (float) $row->tva_amount];
            }

            return $rows;
        }

        if ($reportType === 'profitability') {
            $dimension = $params['dimension'] ?? 'product';
            $from = isset($params['from']) ? \Carbon\Carbon::parse($params['from']) : null;
            $to = isset($params['to']) ? \Carbon\Carbon::parse($params['to']) : null;
            $fromDate = $from ?? \Carbon\Carbon::now()->subMonth();
            $toDate = $to ?? \Carbon\Carbon::now();

            $service = app(\App\Services\Analytics\ProfitabilityAnalyticsService::class);
            $report = $service->report($fromDate, $toDate);
            $records = $report['products'] ?? [];

            if ($dimension === 'customer') {
                $records = $report['customers'] ?? [];
            } elseif ($dimension === 'salesperson') {
                $records = $report['salespeople'] ?? [];
            }

            $rows = [['label', 'revenue', 'purchase_cost', 'gross_profit', 'gross_margin_percent', 'quantity']];
            foreach ($records as $row) {
                $rows[] = [
                    (string) ($row->label ?? ''),
                    (float) ($row->revenue ?? 0),
                    (float) ($row->purchase_cost ?? 0),
                    (float) ($row->gross_profit ?? 0),
                    $row->gross_margin_percent ?? '',
                    (int) ($row->quantity ?? 0),
                ];
            }

            return $rows;
        }

        $data = $dashboard->getDashboardData();
        $rows = [['key', 'value']];
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $rows[] = [$k, json_encode($v)];
            } else {
                $rows[] = [$k, (string) $v];
            }
        }

        return $rows;
    }

    protected function writeCsvRows(string $path, array $rows, string $disk): void
    {
        $stream = fopen('php://temp', 'w+');
        foreach ($rows as $row) {
            fputcsv($stream, $row);
        }
        rewind($stream);
        Storage::disk($disk)->put($path, stream_get_contents($stream));
        fclose($stream);
    }

    protected function writeXlsxRows(string $path, array $rows, string $disk): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        if (! empty($rows)) {
            $sheet->fromArray($rows, null, 'A1');
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'scheduled-report-xlsx-');
        if ($tmpFile === false) {
            throw new \RuntimeException('Unable to create temporary XLSX file.');
        }

        try {
            $writer = new Xlsx($spreadsheet);
            $writer->save($tmpFile);
            Storage::disk($disk)->put($path, file_get_contents($tmpFile));
        } finally {
            if (is_file($tmpFile)) {
                unlink($tmpFile);
            }
        }
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

            $rows = $this->buildRowsForReport($dashboard, $this->report->report_type, $params);

            if ($format === 'xlsx') {
                $this->writeXlsxRows($path, $rows, $disk);
            } else {
                $this->writeCsvRows($path, $rows, $disk);
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
                }
            }

            $nextRunAt = null;
            $recurrence = $this->report->recurrence ?? 'none';

            if ($recurrence !== 'none') {
                $base = $this->report->next_run_at ?? now();
                $nextRunAt = $this->report->computeNextRunAt($base);
            }

            $update = [
                'last_run_at' => now(),
                'next_run_at' => $nextRunAt,
            ];

            if ($recurrence === 'none') {
                $update['status'] = 'inactive';
                $update['next_run_at'] = null;
            }

            $this->report->update($update);
            Cache::forget('scheduled_report_dispatch_'.$this->report->id);

        } catch (Throwable $e) {
            Cache::forget('scheduled_report_dispatch_'.$this->report->id);
            $run->update(['finished_at' => now(), 'duration_ms' => (int) ((microtime(true) - $start) * 1000), 'status' => 'failed', 'error_message' => $e->getMessage()]);
            throw $e;
        } finally {
            $lock->release();
        }
    }
}
