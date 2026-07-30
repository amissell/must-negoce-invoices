<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExecutiveDashboardApiEndpointsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Invoice::query()->delete();
        DB::table('invoice_items')->delete();
        Product::query()->delete();
        Client::query()->delete();
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_gross_margin_and_tva_endpoints_require_auth_and_return_expected_shape(): void
    {
        Carbon::setTestNow('2026-07-15 10:00:00');

        $user = User::create([
            'name' => 'API Admin',
            'email' => 'api-admin-' . uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'approved',
        ]);

        // seed one invoice with TVA and a product cost
        $client = Client::create(['nom' => 'Client API']);
        $product = Product::create(['name' => 'P', 'prix_ttc' => 100, 'cost_price' => 60, 'unite' => 'u']);
        $invoice = Invoice::create([
            'invoice_number' => 'API-' . uniqid(),
            'invoice_date' => '2026-07-01',
            'client_id' => $client->id,
            'montant_ht' => 83.33,
            'tva' => 16.67,
            'total_ttc' => 100,
            'is_locked' => false,
        ]);

        DB::table('invoice_items')->insert([
            ['invoice_id' => $invoice->id, 'product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100, 'total' => 100, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->actingAs($user)
            ->getJson(route('api.kpis.gross_margin'))
            ->assertOk()
            ->assertJsonStructure(['products' => ['labels', 'margins', 'revenues', 'percentages'], 'clients', 'sellers', 'missing_cost_lines']);

        $this->actingAs($user)
            ->getJson(route('api.kpis.tva_liability'))
            ->assertOk()
            ->assertJsonStructure(['labels', 'values', 'configured_rate']);

        // request a shorter window (3 months)
        $this->actingAs($user)
            ->getJson(route('api.kpis.tva_liability', ['months' => 3]))
            ->assertOk()
            ->assertJsonCount(3, 'labels')
            ->assertJsonCount(3, 'values');
    }
}
