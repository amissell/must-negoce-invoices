<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'nom', 'ice', 'if_number', 'rc', 'cnss',
        'telephone', 'email', 'adresse', 'ville',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // Total de toutes les factures
    public function totalFacture(): float
    {
        return (float) $this->invoices()->sum('total_ttc');
    }

    // Nombre total de factures
    public function nombreFactures(): int
    {
        return $this->invoices()->count();
    }
}