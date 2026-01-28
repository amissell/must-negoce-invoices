<?php

namespace App\Services;

use App\Models\InvoiceSequence;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Generate the next invoice number for the current year
     * Format: 001/2026, 002/2026, etc.
     */
    public function generateInvoiceNumber(): string
    {
        return DB::transaction(function () {
            $currentYear = date('Y');
            
            // Get or create sequence for current year
            $sequence = InvoiceSequence::firstOrCreate(
                ['year' => $currentYear],
                ['last_number' => 0]
            );
            
            // Increment the number
            $sequence->increment('last_number');
            
            // Format: 001/2026
            return str_pad($sequence->last_number, 3, '0', STR_PAD_LEFT) . '/' . $currentYear;
        });
    }
}