<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
class Invoice extends Model
{
    use HasFactory, LogsActivity;
    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'client_id',
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
        'created_by',
        'updated_by',
        'vendeur_id',
        'delivery_status',
        'delivered_at',
        'remis_compta_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'is_locked' => 'boolean',
        'montant_ht' => 'decimal:2',
        'tva' => 'decimal:2',
        'total_ttc' => 'decimal:2',
        'remise_amount' => 'decimal:2',
        'remise_percentage' => 'decimal:2',
        'delivered_at' => 'datetime',
        'remis_compta_at' => 'date',
    ];
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
    public function scopeManual($query)
    {
        return $query->where('type', 'manual');
    }
    public function scopeAutomatic($query)
    {
        return $query->where('type', 'automatic');
    }
    public function getCreatorNameAttribute()
    {
        return $this->creator ? $this->creator->name : 'Système';
    }
    public function getUpdaterNameAttribute()
    {
        return $this->updater ? $this->updater->name : null;
    }
    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class);
    }
    public function vendeur()
    {
        return $this->belongsTo(Vendeur::class);
    }
    public function paiements()
    {
        return $this->belongsToMany(Paiement::class, 'paiement_invoice')->withPivot('montant_affecte')->withTimestamps();
    }
    public function getMontantRegleAttribute(): float
    {
        if (array_key_exists('montant_regle_sum', $this->attributes)) {
            return (float) $this->attributes['montant_regle_sum'];
        }
        return (float) $this->paiements()->sum('paiement_invoice.montant_affecte');
    }
    public function getResteAPayerAttribute(): float
    {
        return max(0, round((float) $this->total_ttc - $this->montant_regle, 2));
    }
    public function getPaymentStatusAttribute(): string
    {
        if ($this->montant_regle <= 0) return 'non_payee';
        return $this->reste_a_payer <= 0 ? 'payee' : 'partiellement_payee';
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['invoice_number', 'client_id', 'total', 'status', 'due_date', 'issue_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('invoice')
            ->setDescriptionForEvent(fn(string $eventName) => "Invoice {$this->invoice_number} was {$eventName}");
    }
}
