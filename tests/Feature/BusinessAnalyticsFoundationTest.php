<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BusinessAnalyticsFoundationTest extends TestCase
{
    public function test_analytics_foundation_requires_executive_access_and_reports_known_data_gaps(): void
    {
        $this->getJson(route('api.business-analytics'))->assertUnauthorized();

        $adv = User::factory()->create(['role' => 'adv', 'status' => 'approved']);
        $this->actingAs($adv)->getJson(route('api.business-analytics'))->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $client = Client::create(['nom' => 'Client sans ville']);
        $product = Product::create(['name' => 'Produit sans coût', 'prix_ttc' => 100, 'unite' => 'u']);
        $invoice = Invoice::create([
            'invoice_number' => 'AN-'.uniqid(),
            'invoice_date' => now()->toDateString(),
            'client_id' => $client->id,
            'montant_ht' => 100,
            'tva' => 0,
            'total_ttc' => 100,
        ]);
        DB::table('invoice_items')->insert([
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100,
            'total' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->getJson(route('api.business-analytics'))
            ->assertOk()
            ->assertJsonPath('data_quality.summary.checks', 9)
            ->assertJsonPath('data_quality.schema_capabilities.inventory', false)
            ->assertJsonStructure(['data_quality' => ['checks'], 'data_gaps']);

        $this->assertArrayHasKey('count', collect($response->json('data_quality.checks'))->firstWhere('key', 'missing_product_cost'));
        $this->assertSame('data_unavailable', collect($response->json('data_gaps'))->firstWhere('question', 'Inventory turnover, stockouts and dead stock')['status']);
    }
}
