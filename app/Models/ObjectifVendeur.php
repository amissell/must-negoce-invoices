<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $vendeur_id
 * @property string $vendeur_nom
 */
class ObjectifVendeur extends Model
{
    protected $table = 'objectifs_vendeurs';
    protected $fillable = [
        'vendeur_id',
        'vendeur_nom',
        'mois',
        'annee',
        'objectif_ca',
        'objectif_bl',
        'prime',
    ];

    public function vendeur(): BelongsTo
    {
        return $this->belongsTo(Vendeur::class, 'vendeur_id');
    }
}
