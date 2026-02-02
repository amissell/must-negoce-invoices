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
        'type',
        'client_address',
        'client_phone',
        'client_email',
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

