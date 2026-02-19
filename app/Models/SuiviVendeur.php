<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuiviVendeur extends Model
{
    protected $fillable = [
        'vendeur_nom', 'date', 'chiffre', 'total_paye',
        'heure_debut', 'heure_fin', 'bl', 'jour_repos', 'notes', 'created_by'
    ];

    protected $casts = [
        'date' => 'date',
        'chiffre' => 'decimal:2',
        'total_paye' => 'decimal:2',
    ];

    public function scopeParMois($query, $annee, $mois)
    {
        return $query->whereYear('date', $annee)->whereMonth('date', $mois);
    }

    public function scopeVendeur($query, $nom)
    {
        return $query->where('vendeur_nom', $nom);
    }

    // Champ calculé : reste à payer
    public function getResteAttribute()
    {
        return $this->chiffre - $this->total_paye;
    }

    // Taux d'encaissement
    public function getTauxAttribute()
    {
        if ($this->chiffre == 0) return 0;
        return round(($this->total_paye / $this->chiffre) * 100, 1);
    }
}
