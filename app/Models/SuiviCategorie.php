<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuiviCategorie extends Model
{
    protected $fillable = [
        'suivi_vendeur_id',
        'categorie',
        'categorie_id',
        'vendeur_id',
        'quantite',
        'ca',
    ];

    public function suiviVendeur()
    {
        return $this->belongsTo(SuiviVendeur::class);
    }
}
