<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Emerald Mcconnell', 'prix_ttc' => 65.00, 'unite' => 'unité'],
            ['name' => 'Nathaniel Barrera', 'prix_ttc' => 64.00, 'unite' => 'unité'],
            ['name' => 'rgregy', 'prix_ttc' => 98.00, 'unite' => 'unité'],
            ['name' => 'Détergent Multi-surfaces', 'prix_ttc' => 45.00, 'unite' => 'litre'],
            ['name' => 'Eau de Javel', 'prix_ttc' => 25.00, 'unite' => 'litre'],
            ['name' => 'Savon Liquide', 'prix_ttc' => 35.00, 'unite' => 'litre'],
            ['name' => 'Désinfectant Sol', 'prix_ttc' => 55.00, 'unite' => 'litre'],
            ['name' => 'Nettoyant Vitre', 'prix_ttc' => 30.00, 'unite' => 'litre'],
            ['name' => 'Dégraissant Cuisine', 'prix_ttc' => 48.00, 'unite' => 'litre'],
            ['name' => 'Lingettes Désinfectantes', 'prix_ttc' => 42.00, 'unite' => 'paquet'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}