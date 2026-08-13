<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CommercialAnalyticsTest extends TestCase
{
    public function test_commercial_analytics_returns_observed_customer_sales_and_payment_metrics(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $client = Client::create(['nom' => 'Client analytique', 'ville' => 'Casablanca']);
        $product = Product::create(['name' => 'Produit analytique', 'prix_ttc' => 100, 'unite' => 'u']);
        $invoice = Invoice::create([
            'invoice_number' => 'CA-'.uniqid(),
            'invoice_date' => now()->subDays(10)->toDateString(),
            'client_id' => $client->id,
            'montant_ht' => 100,
            'tva' => 0,
            'total_ttc' => 100,
            'montant_paye' => 40,
            'remise_percentage' => 10,
            'remise_amount' => 10,
        ]);
        DB::table('invoice_items')->insert([
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 50,
            'total' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('paiements')->insert([
            'invoice_id' => $invoice->id,
            'montant' => 40,
            'date_paiement' => now()->subDays(5)->toDateString(),
            'mode' => 'virement',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson(route('api.business-analytics.commercial'))->assertUnauthorized();

        $response = $this->actingAs($admin)
            ->getJson(route('api.business-analytics.commercial', ['from' => now()->subDays(30)->toDateString(), 'to' => now()->toDateString()]))
            ->assertOk()
            ->assertJsonPath('customers.total_revenue', 100)
            ->assertJsonPath('payments.recorded_amount', 40)
            ->assertJsonPath('sales.discounts.discounted_invoices', 1)
            ->assertJsonStructure(['customers' => ['top_by_revenue'], 'payments' => ['by_customer'], 'sales' => ['top_products', 'top_cities']]);

        $this->assertSame('Client analytique', $response->json('customers.top_by_revenue.0.client'));
        $this->assertSame('Produit analytique', $response->json('sales.top_products.0.product'));
    }
}
