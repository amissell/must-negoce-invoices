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
        'client_name',
        'client_address',
        'client_phone',
        'client_email',
        'client_ice',
        'montant_ht',
        'tva',
        'tva_rate',
        'total_ttc',
        'remise_percentage',
        'remise_amount',
        'payment_mode',
        'amount_in_words',
        'type',
        'is_locked',
        'created_by',      // ✅ Added
        'updated_by',      // ✅ Added
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'is_locked' => 'boolean',
        'montant_ht' => 'decimal:2',
        'tva' => 'decimal:2',
        'total_ttc' => 'decimal:2',
        'remise_amount' => 'decimal:2',
        'remise_percentage' => 'decimal:2',
    ];

    /**
     * Relationship: Invoice belongs to a creator (User who created it)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Invoice belongs to an updater (User who last updated it)
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relationship: Invoice has many items
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Scope: Get only manual invoices
     */
    public function scopeManual($query)
    {
        return $query->where('type', 'manual');
    }

    /**
     * Scope: Get only automatic invoices
     */
    public function scopeAutomatic($query)
    {
        return $query->where('type', 'automatic');
    }

    /**
     * Get the creator's name or return 'Système' if not set
     */
    public function getCreatorNameAttribute()
    {
        return $this->creator ? $this->creator->name : 'Système';
    }

    /**
     * Get the updater's name or return null if not set
     */
    public function getUpdaterNameAttribute()
    {
        return $this->updater ? $this->updater->name : null;
    }
}