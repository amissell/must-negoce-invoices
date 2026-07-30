<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Services\ExecutiveDashboardService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExecutiveDashboardFinancialAnalysisTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::table('invoice_items')->delete();
        Invoice::query()->delete();
        Product::query()->delete();
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_margin_excludes_missing_costs_and_tva_is_grouped_by_month(): void
    {
        Carbon::setTestNow('2026-07-15 10:00:00');
        $client = Client::create(['nom' => 'Client marge']);
        $costed = Product::create(['name' => 'Produit rentable', 'prix_ttc' => 100, 'cost_price' => 60, 'unite' => 'unité']);
        $missingCost = Product::create(['name' => 'Produit sans coût', 'prix_ttc' => 50, 'unite' => 'unité']);
        $invoice = Invoice::create([
            'invoice_number' => 'MARGIN-' . uniqid(),
            'invoice_date' => '2026-07-10',
            'client_id' => $client->id,
            'montant_ht' => 208.33,
            'tva' => 41.67,
            'total_ttc' => 250,
            'is_locked' => false,
        ]);

        DB::table('invoice_items')->insert([
            ['invoice_id' => $invoice->id, 'product_id' => $costed->id, 'quantity' => 2, 'unit_price' => 100, 'total' => 200, 'created_at' => now(), 'updated_at' => now()],
            ['invoice_id' => $invoice->id, 'product_id' => $missingCost->id, 'quantity' => 1, 'unit_price' => 50, 'total' => 50, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $data = app(ExecutiveDashboardService::class)->getDashboardData();
        $margin = $data['financial_analysis'];

        $this->assertSame(['Produit rentable'], $margin['products']['labels']);
        $this->assertSame([80.0], $margin['products']['margins']);
        $this->assertSame([40.0], $margin['products']['percentages']);
        $this->assertSame(1, $margin['missing_cost_lines']);
        $this->assertSame(41.67, $data['charts']['tva_liability']['values'][11]);
    }
}
