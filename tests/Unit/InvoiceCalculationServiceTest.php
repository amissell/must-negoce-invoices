<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\InvoiceCalculationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceCalculationService $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 1, 1, 0, 0, 0));

        $this->calculator = app(InvoiceCalculationService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_empty_items_returns_zero_totals(): void
    {
        $result = $this->calculator->calculateInvoiceTotals([], 0);

        $this->assertSame([
            'items' => [],
            'montant_ht' => 0.0,
            'remise_percentage' => 0.0,
            'remise_amount' => 0.0,
            'tva' => 0.0,
            'total_ttc' => 0.0,
        ], $result);
    }

    public function test_one_item_calculation_without_discount(): void
    {
        $result = $this->calculator->calculateInvoiceTotals([
            ['product_id' => 1, 'quantity' => 1, 'unit_price' => 1200.00],
        ], 0);

        $this->assertSame(1, count($result['items']));
        $this->assertSame(1200.00, $result['items'][0]['unit_price_ttc']);
        $this->assertSame(1200.00, $result['items'][0]['total_ttc']);
        $this->assertSame(1000.00, $result['montant_ht']);
        $this->assertSame(0.0, $result['remise_percentage']);
        $this->assertSame(0.0, $result['remise_amount']);
        $this->assertSame(200.00, $result['tva']);
        $this->assertSame(1200.00, $result['total_ttc']);
    }

    public function test_multiple_items_calculation_with_discount(): void
    {
        $result = $this->calculator->calculateInvoiceTotals([
            ['product_id' => 1, 'quantity' => 2, 'unit_price' => 500.00],
            ['product_id' => 2, 'quantity' => 1, 'unit_price' => 200.00],
        ], 10);

        $this->assertSame(2, count($result['items']));
        $this->assertSame(1000.00, $result['items'][0]['total_ttc']);
        $this->assertSame(200.00, $result['items'][1]['total_ttc']);
        $this->assertSame(900.00, $result['montant_ht']);
        $this->assertSame(10.0, $result['remise_percentage']);
        $this->assertSame(100.00, $result['remise_amount']);
        $this->assertSame(180.00, $result['tva']);
        $this->assertSame(1080.00, $result['total_ttc']);
    }

    public function test_decimal_prices_are_rounded_before_total(): void
    {
        $result = $this->calculator->calculateInvoiceTotals([
            ['product_id' => 1, 'quantity' => 2, 'unit_price' => 123.456],
        ], 0);

        $this->assertSame(123.46, $result['items'][0]['unit_price_ttc']);
        $this->assertSame(246.92, $result['items'][0]['total_ttc']);
        $this->assertSame(205.77, $result['montant_ht']);
        $this->assertSame(41.15, $result['tva']);
        $this->assertSame(246.92, $result['total_ttc']);
    }

    public function test_rounding_behavior_matches_existing_invoice_service(): void
    {
        $result = $this->calculator->calculateInvoiceTotals([
            ['product_id' => 1, 'quantity' => 1, 'unit_price' => 1000.00],
        ], 12.345);

        $this->assertSame(1000.00, $result['items'][0]['unit_price_ttc']);
        $this->assertSame(1000.00, $result['items'][0]['total_ttc']);
        $this->assertSame(12.35, $result['remise_percentage']);
        $this->assertSame(102.92, $result['remise_amount']);
        $this->assertSame(730.41, $result['montant_ht']);
        $this->assertSame(146.08, $result['tva']);
        $this->assertSame(876.49, $result['total_ttc']);
    }
}
