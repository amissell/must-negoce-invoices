<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;

/**
 * Generates invoice numbers following the existing project rules.
 */
class InvoiceNumberGenerator
{
    /**
     * Generate the next invoice number using the last invoice for the two-digit year prefix.
     */
    public function generateNextInvoiceNumber(): string
    {
        $year = date('y');

        $lastInvoice = Invoice::where('invoice_number', 'like', $year . '/%')
            ->orderByRaw('CAST(SUBSTRING(invoice_number, 4) AS UNSIGNED) DESC')
            ->first();

        $newNumber = $lastInvoice
            ? intval(explode('/', $lastInvoice->invoice_number)[1]) + 1
            : 1;

        return $year . '/' . str_pad((string) $newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate an invoice number for the current year, filling gaps when present.
     */
    public function generateInvoiceNumber(): string
    {
        $year = date('y');
        $currentYear = date('Y');

        $existingNumbers = Invoice::whereYear('created_at', $currentYear)
            ->pluck('invoice_number')
            ->map(function ($number) {
                $parts = explode('/', $number);
                return isset($parts[1]) ? (int) $parts[1] : null;
            })
            ->filter()
            ->sort()
            ->values()
            ->toArray();

        if (empty($existingNumbers)) {
            return $year . '/001';
        }

        $nextNumber = 1;
        foreach ($existingNumbers as $num) {
            if ($num != $nextNumber) {
                break;
            }
            $nextNumber++;
        }

        return $year . '/' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
