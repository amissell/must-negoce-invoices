<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StepZeroProductCostPriceTest extends TestCase
{
    public function test_cost_price_is_nullable_castable_and_mass_assignable(): void
    {
        $product = Product::create([
            'name' => 'Produit sans cout historique',
            'prix_ttc' => 120,
            'unite' => 'unité',
        ]);

        $this->assertNull($product->cost_price);

        $product->update(['cost_price' => 80]);

        $this->assertSame('80.00', $product->fresh()->cost_price);
    }

    public function test_cost_price_migration_rolls_back_and_reapplies_cleanly(): void
    {
        $migration = require database_path('migrations/2026_07_29_000001_add_cost_price_to_products_table.php');

        $this->assertTrue(Schema::hasColumn('products', 'cost_price'));

        $migration->down();
        $this->assertFalse(Schema::hasColumn('products', 'cost_price'));

        $migration->up();
        $this->assertTrue(Schema::hasColumn('products', 'cost_price'));
    }
}
