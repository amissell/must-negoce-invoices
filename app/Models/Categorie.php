<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    protected $fillable = [
        'nom'
    ];


    public function create()
{
    $vendeurs = SuiviVendeur::distinct()
        ->orderBy('vendeur_nom')
        ->pluck('vendeur_nom');

    $categories = Categorie::all();

    return view(
        'suivi_vendeurs.create',
        compact(
            'vendeurs',
            'categories'
        )
    );
}
}
