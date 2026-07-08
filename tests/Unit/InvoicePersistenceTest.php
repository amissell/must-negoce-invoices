<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use App\Services\InvoicePersistence;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePersistenceTest extends TestCase
{
    use RefreshDatabase;

    private InvoicePersistence $persistence;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 1, 1, 0, 0, 0));

        $this->persistence = app(InvoicePersistence::class);
    }

    public function test_create_automatic_invoice_persists_invoice_and_items(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user+persistence@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'prix_ttc' => 100.00,
            'unite' => 'pcs',
        ]);

        $invoice = $this->persistence->createAutomaticInvoice([
            'invoice_number' => '26/001',
            'invoice_date' => Carbon::now(),
            'montant_ht' => 80.00,
            'tva' => 20.00,
            'total_ttc' => 100.00,
            'is_locked' => true,
            'type' => 'automatic',
            'client_name' => 'Client',
            'created_by' => $user->id,
        ], [
            [
                'product' => $product,
                'quantity' => 1,
                'unit_price' => 100.00,
                'total' => 100.00,
            ],
        ]);

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'invoice_number' => '26/001']);
        $this->assertDatabaseHas('invoice_items', ['invoice_id' => $invoice->id, 'product_id' => $product->id]);
    }
}
