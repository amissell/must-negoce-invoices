<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceService $invoiceService;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 1, 1, 0, 0, 0));
        $this->invoiceService = new InvoiceService();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_generate_next_invoice_number_returns_001_when_no_invoices_exist(): void
    {
        $year = Carbon::now()->format('y');

        $this->assertSame($year . '/001', $this->invoiceService->generateNextInvoiceNumber());
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
            'status' => 'Verrouillée',
            'type' => 'automatic',
            'created_by' => null,
        ]);

        $this->assertSame($year . '/002', $this->invoiceService->generateNextInvoiceNumber());
    }

    public function test_generate_invoice_number_returns_001_when_no_current_year_invoices_exist(): void
    {
        $year = Carbon::now()->format('y');

        $this->assertSame($year . '/001', $this->invoiceService->generateInvoiceNumber());
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
            'status' => 'Verrouillée',
            'type' => 'automatic',
            'created_by' => null,
        ]);

        Invoice::create([
            'invoice_number' => $year . '/003',
            'invoice_date' => Carbon::now(),
            'montant_ht' => 1000.00,
            'tva' => 200.00,
            'total_ttc' => 1200.00,
            'is_locked' => true,
            'status' => 'Verrouillée',
            'type' => 'automatic',
            'created_by' => null,
        ]);

        $this->assertSame($year . '/002', $this->invoiceService->generateInvoiceNumber());
    }

    public function test_split_into_multiple_invoices_creates_two_invoices_for_large_total(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user+split@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $productA = Product::create([
            'name' => 'Product A',
            'prix_ttc' => 3000.00,
            'unite' => 'pcs',
        ]);

        $productB = Product::create([
            'name' => 'Product B',
            'prix_ttc' => 2500.00,
            'unite' => 'pcs',
        ]);

        $items = [
            [
                'product' => $productA,
                'quantity' => 1,
                'unit_price' => 3000.00,
                'total' => 3000.00,
            ],
            [
                'product' => $productB,
                'quantity' => 1,
                'unit_price' => 2500.00,
                'total' => 2500.00,
            ],
        ];

        $method = new \ReflectionMethod($this->invoiceService, 'splitIntoMultipleInvoices');
        $method->setAccessible(true);
        $method->invoke($this->invoiceService, 'Client X', $items, $user->id);

        $this->assertDatabaseCount('invoices', 2);
        $this->assertDatabaseHas('invoices', ['total_ttc' => 3000.00]);
        $this->assertDatabaseHas('invoices', ['total_ttc' => 2500.00]);
        $this->assertDatabaseCount('invoice_items', 2);
    }

    public function test_create_invoices_from_products_creates_single_invoice_for_total_under_5000(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user+single@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $product = Product::create([
            'name' => 'Small Product',
            'prix_ttc' => 1000.00,
            'unite' => 'pcs',
        ]);

        $this->invoiceService->createInvoicesFromProducts([
            ['id' => $product->id, 'quantity' => 3],
        ], 'Client A', $user->id);

        $this->assertDatabaseCount('invoices', 1);
        $this->assertDatabaseHas('invoices', ['total_ttc' => 3000.00, 'type' => 'automatic']);
        $this->assertDatabaseCount('invoice_items', 1);
    }

    public function test_create_invoices_from_products_splits_into_multiple_invoices_when_total_is_5000_or_more(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user+splitproducts@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $productA = Product::create([
            'name' => 'Product A',
            'prix_ttc' => 3000.00,
            'unite' => 'pcs',
        ]);

        $productB = Product::create([
            'name' => 'Product B',
            'prix_ttc' => 2500.00,
            'unite' => 'pcs',
        ]);

        $this->invoiceService->createInvoicesFromProducts([
            ['id' => $productA->id, 'quantity' => 1],
            ['id' => $productB->id, 'quantity' => 1],
        ], 'Client B', $user->id);

        $this->assertDatabaseCount('invoices', 2);
        $this->assertDatabaseHas('invoices', ['total_ttc' => 3000.00, 'type' => 'automatic']);
        $this->assertDatabaseHas('invoices', ['total_ttc' => 2500.00, 'type' => 'automatic']);
        $this->assertDatabaseCount('invoice_items', 2);
    }

    public function test_create_invoices_from_amount_throws_query_exception_for_amount_only_invoice(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user+amount@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->expectException(QueryException::class);

        $this->invoiceService->createInvoicesFromAmount(4000.00, 'Client Amount', $user->id);
    }
}
