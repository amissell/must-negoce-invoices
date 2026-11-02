<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;


/**
 * @property string|null $credit_limit
 * @property int|null $payment_terms_days
 */
class Client extends Model
{
     use HasFactory, LogsActivity;
    protected $fillable = [
        'nom', 'ice', 'if_number', 'rc', 'cnss',
        'telephone', 'email', 'adresse', 'ville', 'credit_limit', 'payment_terms_days',
    ];
    protected $casts = [
        'credit_limit' => 'decimal:2',
        'payment_terms_days' => 'integer',
    ];
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }
    public function totalFacture(): float
    {
        return (float) $this->invoices()->sum('total_ttc');
    }
    public function nombreFactures(): int
    {
        return $this->invoices()->count();
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'phone', 'address', 'city', 'country', 'tax_number'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('client')
            ->setDescriptionForEvent(fn(string $eventName) => "Client {$this->name} was {$eventName}");
    }
}
