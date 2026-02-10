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
        'remise_percentage',
        'remise_amount',
        'tva',
        'tva_rate',
        'total_ttc',
        'is_locked',
        'type',
        'client_name',
        'client_address',
        'client_phone',
        'client_ice',
        'payment_mode',
        'amount_in_words',
    ];

    protected $casts = [
        'invoice_date' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
    
    public function isManual(): bool
    {
        return $this->type === 'manual';
    }
    
    public function scopeManual($query)
    {
        return $query->where('type', 'manual');
    }
    
    public function scopeAutomatic($query)
    {
        return $query->where('type', 'automatic');
    }
}