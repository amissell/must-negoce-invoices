<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Invoice;
use App\Services\InvoiceNumberGenerator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceNumberGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceNumberGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 1, 1, 0, 0, 0));

        $this->generator = app(InvoiceNumberGenerator::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_generate_next_invoice_number_returns_001_when_no_invoices_exist(): void
    {
        $year = Carbon::now()->format('y');

        $this->assertSame($year . '/001', $this->generator->generateNextInvoiceNumber());
    }

    public function test_generate_next_invoice_number_increments_existing_invoice_number(): void
    {
        $year = Carbon::now()->format('y');

        Invoice::create([
            'invoice_number' => $year . '/001',
            'invoice_date' => Carbon::now(),
            'montant_ht' => 1000.00,
            'tva' => 200.00,
            'total_ttc' => 1200.00,
            'is_locked' => true,
            'type' => 'automatic',
            'created_by' => null,
        ]);

        $this->assertSame($year . '/002', $this->generator->generateNextInvoiceNumber());
    }

    public function test_generate_invoice_number_fills_gap_in_sequence(): void
    {
        $year = Carbon::now()->format('y');

        Invoice::create([
            'invoice_number' => $year . '/001',
            'invoice_date' => Carbon::now(),
            'montant_ht' => 1000.00,
            'tva' => 200.00,
            'total_ttc' => 1200.00,
            'is_locked' => true,
            'type' => 'automatic',
            'created_by' => null,
            'created_at' => Carbon::now(),
        ]);

        Invoice::create([
            'invoice_number' => $year . '/003',
            'invoice_date' => Carbon::now(),
            'montant_ht' => 1000.00,
            'tva' => 200.00,
            'total_ttc' => 1200.00,
            'is_locked' => true,
            'type' => 'automatic',
            'created_by' => null,
            'created_at' => Carbon::now(),
        ]);

        $this->assertSame($year . '/002', $this->generator->generateInvoiceNumber());
    }
}
