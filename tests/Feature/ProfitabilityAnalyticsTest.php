<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProfitabilityAnalyticsTest extends TestCase
{
    public function test_profitability_uses_sale_cost_snapshot_and_excludes_unknown_cost_lines(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $client = Client::create(['nom' => 'Client rentable']);
        $snapshottedProduct = Product::create(['name' => 'Produit snapshot', 'prix_ttc' => 100, 'cost_price' => 90, 'unite' => 'u']);
        $unknownCostProduct = Product::create(['name' => 'Produit sans coût', 'prix_ttc' => 100, 'unite' => 'u']);
        $invoice = Invoice::create([
            'invoice_number' => 'PR-'.uniqid(),
            'invoice_date' => now()->toDateString(),
            'client_id' => $client->id,
            'montant_ht' => 200,
            'tva' => 0,
            'total_ttc' => 200,
        ]);
        DB::table('invoice_items')->insert([
            ['invoice_id' => $invoice->id, 'product_id' => $snapshottedProduct->id, 'quantity' => 1, 'unit_price' => 100, 'unit_cost_at_sale' => 60, 'total' => 100, 'discount_amount' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['invoice_id' => $invoice->id, 'product_id' => $unknownCostProduct->id, 'quantity' => 1, 'unit_price' => 100, 'unit_cost_at_sale' => null, 'total' => 100, 'discount_amount' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->actingAs($admin)->getJson(route('api.business-analytics.profitability'))
            ->assertOk()
            ->assertJsonPath('summary.known_cost_revenue', 90)
            ->assertJsonPath('summary.gross_profit', 30)
            ->assertJsonPath('summary.missing_cost_line_count', 1)
            ->assertJsonPath('summary.snapshot_cost_line_count', 1)
            ->assertJsonPath('summary.confidence', 'Low');

        $this->assertSame('Produit snapshot', $response->json('products.0.label'));
    }

    public function test_profitability_export_returns_only_known_cost_rows(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $client = Client::create(['nom' => 'Client export']);
        $known = Product::create(['name' => 'Produit rentable', 'prix_ttc' => 100, 'cost_price' => 70, 'unite' => 'u']);
        $unknown = Product::create(['name' => 'Produit sans coût', 'prix_ttc' => 100, 'unite' => 'u']);
        $invoice = Invoice::create([
            'invoice_number' => 'EXP-'.uniqid(),
            'invoice_date' => now()->toDateString(),
            'client_id' => $client->id,
            'montant_ht' => 200,
            'tva' => 0,
            'total_ttc' => 200,
        ]);

        DB::table('invoice_items')->insert([
            ['invoice_id' => $invoice->id, 'product_id' => $known->id, 'quantity' => 1, 'unit_price' => 100, 'unit_cost_at_sale' => 50, 'total' => 100, 'discount_amount' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['invoice_id' => $invoice->id, 'product_id' => $unknown->id, 'quantity' => 1, 'unit_price' => 100, 'unit_cost_at_sale' => null, 'total' => 100, 'discount_amount' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->actingAs($admin)
            ->get(route('api.business-analytics.profitability.export', ['from' => now()->subDay()->toDateString(), 'to' => now()->addDay()->toDateString()]));

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Produit rentable', $csv);
        $this->assertStringNotContainsString('Produit sans coût', $csv);
    }

    public function test_profitability_details_are_grouped_by_dimension_and_skip_missing_costs(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $client = Client::create(['nom' => 'Client détail']);
        $known = Product::create(['name' => 'Produit rentable', 'prix_ttc' => 100, 'cost_price' => 70, 'unite' => 'u']);
        $unknown = Product::create(['name' => 'Produit sans coût', 'prix_ttc' => 100, 'unite' => 'u']);
        $invoice = Invoice::create([
            'invoice_number' => 'DET-'.uniqid(),
            'invoice_date' => now()->toDateString(),
            'client_id' => $client->id,
            'montant_ht' => 200,
            'tva' => 0,
            'total_ttc' => 200,
        ]);
        DB::table('invoice_items')->insert([
            ['invoice_id' => $invoice->id, 'product_id' => $known->id, 'quantity' => 1, 'unit_price' => 100, 'unit_cost_at_sale' => 50, 'total' => 100, 'discount_amount' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['invoice_id' => $invoice->id, 'product_id' => $unknown->id, 'quantity' => 1, 'unit_price' => 100, 'unit_cost_at_sale' => null, 'total' => 100, 'discount_amount' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
        $response = $this->actingAs($admin)
            ->getJson(route('api.business-analytics.profitability.details', ['dimension' => 'product']))
            ->assertOk();
        $this->assertSame('Produit rentable', $response->json('data.0.label'));
        $this->assertSame(1, $response->json('total'));
        $this->assertStringNotContainsString('Produit sans coût', json_encode($response->json('data')));
    }

    public function test_profitability_detail_export_returns_only_known_cost_rows(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $client = Client::create(['nom' => 'Client export détail']);
        $known = Product::create(['name' => 'Produit exportable', 'prix_ttc' => 100, 'cost_price' => 70, 'unite' => 'u']);
        $unknown = Product::create(['name' => 'Produit sans coût export', 'prix_ttc' => 100, 'unite' => 'u']);
        $invoice = Invoice::create([
            'invoice_number' => 'DETEXP-'.uniqid(),
            'invoice_date' => now()->toDateString(),
            'client_id' => $client->id,
            'montant_ht' => 200,
            'tva' => 0,
            'total_ttc' => 200,
        ]);

        DB::table('invoice_items')->insert([
            ['invoice_id' => $invoice->id, 'product_id' => $known->id, 'quantity' => 1, 'unit_price' => 100, 'unit_cost_at_sale' => 50, 'total' => 100, 'discount_amount' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['invoice_id' => $invoice->id, 'product_id' => $unknown->id, 'quantity' => 1, 'unit_price' => 100, 'unit_cost_at_sale' => null, 'total' => 100, 'discount_amount' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->actingAs($admin)
            ->get(route('api.business-analytics.profitability.details.export', ['dimension' => 'product', 'from' => now()->subDay()->toDateString(), 'to' => now()->addDay()->toDateString()]));

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Produit exportable', $csv);
        $this->assertStringNotContainsString('Produit sans coût export', $csv);
    }
}
