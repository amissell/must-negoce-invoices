<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ManualInvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        return User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
    }

    public function test_manual_invoice_can_be_stored(): void
    {
        $user = $this->createAdminUser();
        $product = Product::create([
            'name' => 'Test product',
            'prix_ttc' => 100.00,
            'unite' => 'pcs',
        ]);

        $response = $this->actingAs($user)->post(route('invoices.manual.store'), [
            'invoice_date' => '2026-01-01',
            'payment_mode' => 'CH',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => '100.00',
                ],
            ],
        ]);

        $response->assertRedirect(route('invoices.index'));
        $response->assertSessionHas('success', 'Facture créée');

        $this->assertDatabaseHas('invoices', [
            'type' => 'manual',
            'payment_mode' => 'CH',
            'total_ttc' => '200.00',
        ]);

        $this->assertDatabaseCount('invoice_items', 1);
    }

    public function test_manual_invoice_store_validation_errors_are_returned(): void
    {
        $user = $this->createAdminUser();

        $response = $this->actingAs($user)->post(route('invoices.manual.store'), []);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['invoice_date', 'payment_mode', 'items']);
    }

    public function test_manual_invoice_can_be_updated(): void
    {
        $user = $this->createAdminUser();
        $product = Product::create([
            'name' => 'Update product',
            'prix_ttc' => 100.00,
            'unite' => 'pcs',
        ]);

        $invoice = Invoice::create([
            'invoice_number' => '26/001',
            'type' => 'manual',
            'invoice_date' => '2026-01-01',
            'payment_mode' => 'CH',
            'montant_ht' => 166.67,
            'tva' => 33.33,
            'total_ttc' => 200.00,
            'amount_in_words' => 'DEUX CENT DHS',
            'created_by' => $user->id,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'description' => $product->name,
            'quantity' => 1,
            'unit_price' => 100.00,
            'total' => 100.00,
        ]);

        $response = $this->actingAs($user)->put(route('invoices.update', $invoice->id), [
            'invoice_number' => $invoice->invoice_number,
            'invoice_date' => '2026-02-01',
            'payment_mode' => 'ESP',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'unit_price' => '100.00',
                ],
            ],
        ]);

        $response->assertRedirect(route('invoices.index'));
        $response->assertSessionHas('success', 'Facture modifiée');

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'payment_mode' => 'ESP',
            'total_ttc' => '300.00',
        ]);

        $this->assertDatabaseCount('invoice_items', 1);
    }

    public function test_manual_invoice_update_validation_errors_are_returned(): void
    {
        $user = $this->createAdminUser();
        $product = Product::create([
            'name' => 'Bad update product',
            'prix_ttc' => 100.00,
            'unite' => 'pcs',
        ]);

        $invoice = Invoice::create([
            'invoice_number' => '26/002',
            'type' => 'manual',
            'invoice_date' => '2026-01-01',
            'payment_mode' => 'CH',
            'montant_ht' => 166.67,
            'tva' => 33.33,
            'total_ttc' => 200.00,
            'amount_in_words' => 'DEUX CENT DHS',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->put(route('invoices.update', $invoice->id), [
            'invoice_number' => '',
            'invoice_date' => 'not-a-date',
            'payment_mode' => 'BAD',
            'items' => [],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['invoice_number', 'invoice_date', 'payment_mode', 'items']);
    }
}
