<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'montant_ht',
        'tva',
        'total_ttc',
        'is_locked',
        'status'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'is_locked' => 'boolean',
        'montant_ht' => 'decimal:2',
        'tva' => 'decimal:2',
        'total_ttc' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // هاد الـ method المهم!
    public static function generateInvoiceNumber()
    {
        $year = date('y');
        $lastInvoice = self::whereYear('created_at', date('Y'))
            ->orderBy('id', 'desc')
            ->first();
        
        $number = $lastInvoice ? intval(substr($lastInvoice->invoice_number, 3)) + 1 : 1;
        
        return $year . '/' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}