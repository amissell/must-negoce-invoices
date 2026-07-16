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
    /**
     * Reserve and return the next invoice number for the current year.
     * MUST be called inside a DB::transaction() to guarantee the row lock.
     */
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

        DB::table('invoice_sequences')
            ->where('year_prefix', $year)
            ->update([
                'last_number' => $next,
                'updated_at'  => now(),
            ]);

        return $year . '/' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
