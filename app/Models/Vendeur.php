<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendeur extends Model
{
    protected $fillable = [
        'nom_complet',
        'amevo_id',
    ];

    public function objectifs(): HasMany
    {
        return $this->hasMany(ObjectifVendeur::class, 'vendeur_id');
    }
}
