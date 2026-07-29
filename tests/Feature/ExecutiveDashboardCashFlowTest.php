<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Services\ExecutiveDashboardService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExecutiveDashboardCashFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::table('paiements')->delete();
        DB::table('invoice_items')->delete();
        Invoice::query()->delete();
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_cash_flow_and_payment_modes_are_zero_filled_and_aligned_by_month(): void
    {
        Carbon::setTestNow('2026-07-15 10:00:00');
        $june = $this->invoice('2026-06-10', 1000);
        $july = $this->invoice('2026-07-10', 2000);

        DB::table('paiements')->insert([
            ['invoice_id' => $june->id, 'montant' => 500, 'date_paiement' => '2026-06-12', 'mode' => 'espèces', 'created_at' => now(), 'updated_at' => now()],
            ['invoice_id' => $july->id, 'montant' => 800, 'date_paiement' => '2026-07-11', 'mode' => 'virement', 'created_at' => now(), 'updated_at' => now()],
            ['invoice_id' => $july->id, 'montant' => 200, 'date_paiement' => '2026-07-12', 'mode' => 'chèque', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $cashFlow = app(ExecutiveDashboardService::class)->getDashboardData()['cash_flow'];
        $comparison = $cashFlow['collected_vs_invoiced'];

        $this->assertSame(1000.0, $comparison['invoiced'][10]);
        $this->assertSame(2000.0, $comparison['invoiced'][11]);
        $this->assertSame(500.0, $comparison['collected'][10]);
        $this->assertSame(1000.0, $comparison['collected'][11]);
        $this->assertSame(50.0, $comparison['collection_ratios'][11]);
        $this->assertSame([500.0, 0.0], array_slice($cashFlow['payment_mode_mix']['datasets'][0]['data'], -2));
        $this->assertSame([0.0, 200.0], array_slice($cashFlow['payment_mode_mix']['datasets'][1]['data'], -2));
        $this->assertSame([0.0, 800.0], array_slice($cashFlow['payment_mode_mix']['datasets'][2]['data'], -2));
    }

    private function invoice(string $date, float $total): Invoice
    {
        return Invoice::create([
            'invoice_number' => 'CASH-' . uniqid(),
            'invoice_date' => $date,
            'montant_ht' => $total / 1.2,
            'tva' => $total - ($total / 1.2),
            'total_ttc' => $total,
            'is_locked' => false,
        ]);
    }
}
