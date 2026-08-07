<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\Vendeur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InputValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Client $client;
    private Vendeur $vendeur;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $this->client = Client::factory()->create();
        $this->vendeur = Vendeur::create(['nom_complet' => 'Validation Vendor']);
    }

    public function test_invoice_amount_must_be_non_negative(): void
    {
        $this->actingAs($this->admin)->patchJson('/invoices/1', ['montant_ht' => -100])
            ->assertUnprocessable()->assertJsonValidationErrors('montant_ht');
    }

    public function test_invoice_tva_rate_cannot_exceed_100_percent(): void
    {
        $this->actingAs($this->admin)->patchJson('/invoices/1', ['tva_rate' => 150])
            ->assertUnprocessable()->assertJsonValidationErrors('tva_rate');
    }

    public function test_invoice_discount_cannot_exceed_100_percent(): void
    {
        $this->actingAs($this->admin)->patchJson('/invoices/1', ['remise_percentage' => 101])
            ->assertUnprocessable()->assertJsonValidationErrors('remise_percentage');
    }

    public function test_invoice_payment_mode_must_be_valid(): void
    {
        $this->actingAs($this->admin)->patchJson('/invoices/1', ['payment_mode' => 'invalid_mode'])
            ->assertUnprocessable()->assertJsonValidationErrors('payment_mode');
    }

    public function test_invoice_date_must_be_valid(): void
    {
        $this->actingAs($this->admin)->patchJson('/invoices/1', ['invoice_date' => 'not-a-date'])
            ->assertUnprocessable()->assertJsonValidationErrors('invoice_date');
    }

    public function test_client_email_must_be_valid(): void
    {
        $this->actingAs($this->admin)->patchJson('/clients/' . $this->client->id, ['email' => 'invalid-email'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_client_phone_must_be_valid_format(): void
    {
        $this->actingAs($this->admin)->patchJson('/clients/' . $this->client->id, ['telephone' => 'not-a-phone-number!!!'])
            ->assertUnprocessable()->assertJsonValidationErrors('telephone');
    }

    public function test_client_credit_limit_must_be_non_negative(): void
    {
        $this->actingAs($this->admin)->patchJson('/clients/' . $this->client->id, ['credit_limit' => -1000])
            ->assertUnprocessable()->assertJsonValidationErrors('credit_limit');
    }

    public function test_client_payment_terms_cannot_exceed_365_days(): void
    {
        $this->actingAs($this->admin)->patchJson('/clients/' . $this->client->id, ['payment_terms_days' => 400])
            ->assertUnprocessable()->assertJsonValidationErrors('payment_terms_days');
    }

    public function test_vendor_phone_must_be_valid_format(): void
    {
        $this->actingAs($this->admin)->patchJson('/vendeurs/' . $this->vendeur->id, ['phone' => 'invalid-phone'])
            ->assertUnprocessable()->assertJsonValidationErrors('phone');
    }

    public function test_vendor_status_must_be_valid(): void
    {
        $this->actingAs($this->admin)->patchJson('/vendeurs/' . $this->vendeur->id, ['status' => 'unknown_status'])
            ->assertUnprocessable()->assertJsonValidationErrors('status');
    }

    public function test_duplicate_ice_number_rejected(): void
    {
        Client::factory()->create(['ice' => 'ICE123456']);
        $this->actingAs($this->admin)->patchJson('/clients/' . $this->client->id, ['ice' => 'ICE123456'])
            ->assertUnprocessable()->assertJsonValidationErrors('ice');
    }

    public function test_string_fields_with_excessive_length_rejected(): void
    {
        $this->actingAs($this->admin)->patchJson('/clients/' . $this->client->id, ['nom' => str_repeat('a', 500)])
            ->assertUnprocessable()->assertJsonValidationErrors('nom');
    }

    public function test_numeric_fields_must_be_numeric(): void
    {
        $this->actingAs($this->admin)->patchJson('/invoices/1', ['montant_ht' => 'not-a-number'])
            ->assertUnprocessable()->assertJsonValidationErrors('montant_ht');
    }

    public function test_custom_validation_messages_are_returned(): void
    {
        $response = $this->actingAs($this->admin)->patchJson('/invoices/1', ['type' => 'invalid_type']);
        $response->assertUnprocessable();
        $this->assertStringContainsString('manual', $response->json('errors.type.0'));
    }
}
