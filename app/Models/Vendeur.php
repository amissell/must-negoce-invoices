<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendeur extends Model
{
    protected $fillable = [
        'nom_complet',
        'amevo_id',
    ];
}
