<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExecutiveDashboardDrilldownsTest extends TestCase
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

    public function test_invalid_dimension_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $this->actingAs($user)
            ->getJson(route('api.kpis.margins_details', ['dimension' => 'invalid']))
            ->assertStatus(422);
    }

    public function test_invalid_date_range_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $this->actingAs($user)
            ->getJson(route('api.kpis.margins_details', ['dimension' => 'product', 'from' => '2026-07-10', 'to' => '2026-07-01']))
            ->assertStatus(422);
    }

    public function test_empty_dataset_returns_empty_paginated(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $resp = $this->actingAs($user)->getJson(route('api.kpis.margins_details', ['dimension' => 'product']));
        $resp->assertOk()->assertJsonStructure(['data', 'current_page', 'per_page']);
        $this->assertCount(0, $resp->json('data'));
    }

    public function test_large_pagination_and_csv_and_authorization(): void
    {
        Carbon::setTestNow('2026-07-15 10:00:00');
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        // use a valid non-admin role value defined in the schema
        $user = User::factory()->create(['role' => 'superviseur', 'status' => 'approved']);

        $client = Client::create(['nom' => 'Pagination Client']);

        // Create 60 invoices each with a distinct product to produce many groups
        for ($i = 0; $i < 60; $i++) {
            $prod = Product::create(['name' => 'Bulk ' . $i, 'prix_ttc' => 10 + $i, 'cost_price' => 5 + $i, 'unite' => 'u']);
            $inv = Invoice::create([
                'invoice_number' => 'BULK-' . $i,
                'invoice_date' => '2026-07-01',
                'client_id' => $client->id,
                'montant_ht' => (8.33 + $i),
                'tva' => (1.67 + $i),
                'total_ttc' => (10 + $i),
                'is_locked' => false,
            ]);
            DB::table('invoice_items')->insert(['invoice_id' => $inv->id, 'product_id' => $prod->id, 'quantity' => 1, 'unit_price' => 10 + $i, 'total' => 10 + $i, 'created_at' => now(), 'updated_at' => now()]);
        }

        // non-admin should be denied
        $this->actingAs($user)->getJson(route('api.kpis.margins_details', ['dimension' => 'product']))->assertForbidden();

        // admin can fetch page 1 per_page=20
        $this->actingAs($admin)
            ->getJson(route('api.kpis.margins_details', ['dimension' => 'product', 'per_page' => 20]))
            ->assertOk()
            ->assertJsonPath('per_page', 20)
            ->assertJsonPath('total', 60);

        // CSV export returns CSV content-type and first line is headers
        $csv = $this->actingAs($admin)->get(route('api.kpis.margins_export', ['dimension' => 'product']));
        $csv->assertStatus(200);
        $this->assertStringContainsString('text/csv', $csv->headers->get('content-type'));
        $this->assertStringContainsString('text/csv', $csv->headers->get('content-type'));
        $this->assertStringContainsString('.csv', $csv->headers->get('content-disposition'));
    }
}
