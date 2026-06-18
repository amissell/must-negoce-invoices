<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Console\Command;

class BackfillInvoiceClientId extends Command
{
    /**
     * php artisan invoices:backfill-client-id
     */
    protected $signature = 'invoices:backfill-client-id {--dry-run : Show what would change without saving}';

    protected $description = 'Attach client_id to existing invoices by matching client_ice / client_name to the clients table';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $invoices = Invoice::whereNull('client_id')
            ->where(function ($q) {
                $q->whereNotNull('client_ice')->orWhereNotNull('client_name');
            })
            ->get();

        if ($invoices->isEmpty()) {
            $this->info('No invoices need backfilling. Nothing to do.');
            return self::SUCCESS;
        }

        $matched = 0;
        $skipped = 0;

        foreach ($invoices as $invoice) {
            $client = null;

            if ($invoice->client_ice) {
                $client = Client::where('ice', $invoice->client_ice)->first();
            }

            if (!$client && $invoice->client_name && $invoice->client_name !== 'divers') {
                $client = Client::where('nom', $invoice->client_name)->first();
            }

            if ($client) {
                $this->line("Facture {$invoice->invoice_number} -> client #{$client->id} ({$client->nom})");
                if (!$dryRun) {
                    $invoice->update(['client_id' => $client->id]);
                }
                $matched++;
            } else {
                $skipped++;
            }
        }

        $this->newLine();
        $this->info("Matched: {$matched}");
        $this->warn("Skipped (no matching client, e.g. 'divers'): {$skipped}");

        if ($dryRun) {
            $this->comment('Dry run — no changes were saved. Re-run without --dry-run to apply.');
        }

        return self::SUCCESS;
    }
}