<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    // Note: the controller `store` method is not routed. Manual invoice
    // creation is handled by `ManualInvoiceController::store` (covered in
    // ManualInvoiceControllerTest).

    public function test_generate_route_redirects_and_sets_success_flash(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user+' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'approved',
        ]);

        $this->actingAs($user);

        $response = $this->post('/invoices/generate', [
            'montant_global_ttc' => 6000.00,
        ]);

        $response->assertRedirect(route('invoices.index'));
        $response->assertSessionHas('success');
    }

    public function test_download_pdf_returns_pdf_for_existing_invoice(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user+' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'approved',
        ]);

        $product = Product::create([
            'name' => 'PDF Product',
            'prix_ttc' => 100.00,
            'unite' => 'pcs',
        ]);

        $invoice = Invoice::create([
            'invoice_number' => '2026/999',
            'invoice_date' => now(),
            'montant_ht' => 100.00,
            'tva' => 20.00,
            'total_ttc' => 120.00,
            'is_locked' => false,
            'type' => 'manual',
            'client_name' => 'Client PDF',
            'created_by' => $user->id,
        ]);

        \DB::table('invoice_items')->insert([
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100.00,
            'total' => 100.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/invoices/' . $invoice->id . '/download');

        $response->assertStatus(200);
        $this->assertStringContainsString('pdf', $response->headers->get('content-type'));
    }
}
