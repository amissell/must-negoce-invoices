<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuiviVendeur extends Model
{
    protected $fillable = [
        'vendeur_nom', 'date', 'chiffre', 'total_paye',
        'heure_debut', 'heure_fin', 'bl', 'notes', 'created_by'
    ];

    protected $casts = [
        'date' => 'date',
        'chiffre' => 'decimal:2',
        'total_paye' => 'decimal:2',
    ];

    // Calcul automatique du reste à payer
    public function getResteAPayerAttribute()
    {
        return $this->chiffre - $this->total_paye;
    }

    // Scope pour filtrer par mois
    public function scopeParMois($query, $annee, $mois)
    {
        return $query->whereYear('date', $annee)->whereMonth('date', $mois);
    }

    // Scope pour filtrer par vendeur
    public function scopeVendeur($query, $nom)
    {
        return $query->where('vendeur_nom', $nom);
    }
}
