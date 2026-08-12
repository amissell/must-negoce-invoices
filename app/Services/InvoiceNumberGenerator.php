<?php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Generates invoice numbers atomically using a dedicated sequence table
 * protected by SELECT ... FOR UPDATE. Must be called inside an active
 * database transaction.
 */
class InvoiceNumberGenerator
{

    public function generateNextInvoiceNumber(): string
    {
        if (DB::transactionLevel() < 1) {
            throw new RuntimeException(
                'InvoiceNumberGenerator::generateNextInvoiceNumber() must be called inside a database transaction.'
            );
        }

        $year = date('y');

        // Ensure the row exists before locking (safe upsert; no-op if present).
        DB::table('invoice_sequences')->insertOrIgnore([
            'year_prefix' => $year,
            'last_number' => 0,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Lock the row for the duration of the transaction.
        $row = DB::table('invoice_sequences')
            ->where('year_prefix', $year)
            ->lockForUpdate()
            ->first();

        $next = ((int) $row->last_number) + 1;
        if (((int) $row->last_number) === 0) {
            $maxNumber = (int) DB::table('invoices')
                ->where('invoice_number', 'like', $year . '/%')
                ->selectRaw('COALESCE(MAX(CAST(SUBSTRING(invoice_number, 4) AS UNSIGNED)), 0) as max_num')
                ->value('max_num');

            if ($maxNumber >= 1) {
                $next = $maxNumber + 1;
            }
        }

        DB::table('invoice_sequences')
            ->where('year_prefix', $year)
            ->update([
                'last_number' => $next,
                'updated_at'  => now(),
            ]);

        return $year . '/' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
    public function generateInvoiceNumber(): string
    {
        $year = date('y');

        $numbers = DB::table('invoices')
            ->where('invoice_number', 'like', $year . '/%')
            ->pluck('invoice_number')
            ->map(fn($n) => (int) substr($n, 3))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $i = 1;
        foreach ($numbers as $num) {
            if ($num !== $i) {
                return $year . '/' . str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            }
            $i++;
        }

        return $year . '/' . str_pad((string) $i, 3, '0', STR_PAD_LEFT);
    }
}
