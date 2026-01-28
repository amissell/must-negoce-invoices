<?php
// database/seeders/ProductSeeder.php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // From the invoice PDF - cleaning products
            ['name' => 'MON SECRET AEROSOL 300ML*24 bmrj', 'price_ttc' => 10.98],
            ['name' => 'SIR DEGRESSANT 1L', 'price_ttc' => 23.50],
            ['name' => 'U.C LINGETTE ANTIBACTERIENNE 100P', 'price_ttc' => 19.40],
            ['name' => 'U.C LINGETTE LOTION 72 PCS', 'price_ttc' => 12.50],
            ['name' => 'U.C LINGETTE OTTOMAN 100PCS', 'price_ttc' => 19.50],
            ['name' => 'VASELINE NATURE 100 M', 'price_ttc' => 11.50],
            ['name' => 'DESODOR VAISSELLE', 'price_ttc' => 13.00],
            ['name' => 'CUISINET sacs Poubelle 40/40U x24', 'price_ttc' => 18.40],
            ['name' => 'OSCAR sacs Poubelle 35L/40P x30', 'price_ttc' => 14.20],
            ['name' => 'OSCAR sacs Poubelle 50L/30P x24', 'price_ttc' => 20.50],
            ['name' => 'COTTON TIGE SOFT 300 *6', 'price_ttc' => 12.00],
            ['name' => 'COTTON TIGE SOFT 100 PCS', 'price_ttc' => 5.80],
            ['name' => 'NETTOYAGE RACLETTE BROSAHEL 45CM x12', 'price_ttc' => 8.50],
            ['name' => 'WELLNAX WC 1', 'price_ttc' => 12.50],
            ['name' => 'NOXES 125G Rose', 'price_ttc' => 5.00],
            ['name' => 'NETTOYAGE BAYBAY 2 x37', 'price_ttc' => 7.00],
            ['name' => 'DEBOUCHEUR SPARK 1 LITRE', 'price_ttc' => 18.50],
            ['name' => 'MY COMFORT SAVON MAIN x15', 'price_ttc' => 12.00],
            ['name' => 'MOUCHOIR CAREFFULL 600 X24', 'price_ttc' => 8.00],
            ['name' => 'LOGIX CAPSULE LAVE VAISSELLE *6', 'price_ttc' => 27.50],
            ['name' => 'JOYFUL MAXI 4', 'price_ttc' => 55.00],
            ['name' => 'JOYFUL JUNIOR 5', 'price_ttc' => 55.00],
            ['name' => 'JOYFUL MINI 2', 'price_ttc' => 55.00],
            ['name' => 'JOYFUL MIDI 3', 'price_ttc' => 55.00],
            
            // Additional cleaning products for variety
            ['name' => 'AJAX MULTI-SURFACE 1L', 'price_ttc' => 15.90],
            ['name' => 'MR PROPRE CITRON 1.5L', 'price_ttc' => 22.00],
            ['name' => 'HARPIC GEL WC 750ML', 'price_ttc' => 18.00],
            ['name' => 'DOMESTOS JAVEL 1L', 'price_ttc' => 14.50],
            ['name' => 'CLOROX BLEACH 2L', 'price_ttc' => 25.00],
            ['name' => 'PAIC CITRON 500ML', 'price_ttc' => 9.80],
            ['name' => 'FAIRY ORIGINAL 450ML', 'price_ttc' => 16.50],
            ['name' => 'TIDE POWDER 3KG', 'price_ttc' => 65.00],
            ['name' => 'ARIEL LIQUIDE 2L', 'price_ttc' => 78.00],
            ['name' => 'PERSIL GEL 1.5L', 'price_ttc' => 58.00],
            ['name' => 'OMO SENSITIVE 2KG', 'price_ttc' => 45.00],
            ['name' => 'COMFORT ASSOUPLISSANT 1L', 'price_ttc' => 28.00],
            ['name' => 'SOUPLINE CONCENTRE 750ML', 'price_ttc' => 32.00],
            ['name' => 'LENOR SPRING 1L', 'price_ttc' => 35.00],
            ['name' => 'SCOTCH BRITE EPONGE x6', 'price_ttc' => 15.00],
            ['name' => 'SPONTEX GANTS M x12', 'price_ttc' => 42.00],
            ['name' => 'VILEDA SERPILLIERE x10', 'price_ttc' => 38.00],
            ['name' => 'MICROFIBRE CHIFFON x5', 'price_ttc' => 22.00],
            ['name' => 'BALAI BROSSE CLASSIQUE', 'price_ttc' => 28.00],
            ['name' => 'SEAU PLASTIQUE 10L', 'price_ttc' => 18.00],
            ['name' => 'PAPIER TOILETTE 24 ROULEAUX', 'price_ttc' => 48.00],
            ['name' => 'ESSUIE-TOUT MEGA x6', 'price_ttc' => 35.00],
            ['name' => 'SERVIETTES TABLE 100PCS', 'price_ttc' => 12.00],
            ['name' => 'NAPPE PLASTIQUE 10M', 'price_ttc' => 25.00],
            ['name' => 'GOBELET PLASTIQUE 50PCS', 'price_ttc' => 8.50],
            ['name' => 'ASSIETTE CARTON 25PCS', 'price_ttc' => 15.00],
            ['name' => 'COUVERTS JETABLES 50PCS', 'price_ttc' => 18.00],
            ['name' => 'FILM ALIMENTAIRE 100M', 'price_ttc' => 22.00],
            ['name' => 'PAPIER ALU 50M', 'price_ttc' => 28.00],
            ['name' => 'SAC CONGELATION 50PCS', 'price_ttc' => 16.00],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}