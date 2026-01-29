<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceSequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'last_number',
    ];

    public $timestamps = false; // optional if you don't have created_at/updated_at

    /**
     * Get the next invoice number for a given year.
     */
    public static function getNextNumber(int $year): string
    {
        $sequence = self::firstOrCreate(
            ['year' => $year],
            ['last_number' => 0]
        );

        $sequence->last_number++;
        $sequence->save();

        $yy = substr($year, 2); // last 2 digits of year
        $nnn = str_pad($sequence->last_number, 3, '0', STR_PAD_LEFT);

        return "$yy/$nnn"; // format: 26/001
    }
}
