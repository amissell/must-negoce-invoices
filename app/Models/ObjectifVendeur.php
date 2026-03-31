<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectifVendeur extends Model
{
    protected $table = 'objectifs_vendeurs';

    protected $fillable = [
        'vendeur_nom',
        'mois',
        'annee',
        'objectif_spark',
        'objectif_ecosolutions',
        'objectif_total',
        'prime',
    ];
}