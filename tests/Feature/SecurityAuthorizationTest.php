<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $superviseur;
    private User $adv;
    private User $unapprovedUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users with different roles
        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $this->superviseur = User::factory()->create(['role' => 'superviseur', 'status' => 'approved']);
        $this->adv = User::factory()->create(['role' => 'adv', 'status' => 'approved']);
        $this->unapprovedUser = User::factory()->create(['role' => 'adv', 'status' => 'pending']);
    }

    /**
     * Test that unapproved users cannot access dashboard
     */
    public function test_unapproved_user_cannot_access_dashboard(): void
    {
        $this->actingAs($this->unapprovedUser)
            ->get('/api/dashboard')
            ->assertForbidden();
    }

    /**
     * Test that only admin and superviseur can access dashboard
     */
    public function test_only_admin_and_superviseur_can_access_dashboard(): void
    {
        // Admin can access
        $this->actingAs($this->admin)
            ->get('/api/dashboard')
            ->assertSuccessful();

        // Superviseur can access
        $this->actingAs($this->superviseur)
            ->get('/api/dashboard')
            ->assertSuccessful();

        // ADV cannot access
        $this->actingAs($this->adv)
            ->get('/api/dashboard')
            ->assertForbidden();
    }

    /**
     * Test row-level security for invoices
     */
    public function test_users_can_only_view_own_invoices(): void
    {
        // Create invoices from different users
        $adminInvoice = Invoice::factory()->create(['created_by' => $this->admin->id]);
        $advInvoice = Invoice::factory()->create(['created_by' => $this->adv->id]);

        // ADV can view their own invoice
        $this->actingAs($this->adv)
            ->get('/invoices')
            ->assertSee($advInvoice->invoice_number);

        // ADV cannot see admin's invoice
        $this->actingAs($this->adv)
            ->get('/invoices')
            ->assertDontSee($adminInvoice->invoice_number);

        // Admin can see all invoices
        $this->actingAs($this->admin)
            ->get('/invoices')
            ->assertSee($advInvoice->invoice_number)
            ->assertSee($adminInvoice->invoice_number);
    }

    /**
     * Test that users cannot update invoices they don't own
     */
    public function test_user_cannot_update_another_users_invoice(): void
    {
        $invoice = Invoice::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->adv)
            ->patchJson("/invoices/{$invoice->id}", [
                'client_name' => 'Modified Client',
            ]);

        $response->assertForbidden();
    }

    /**
     * Test that locked invoices cannot be updated except by admin
     */
    public function test_locked_invoice_cannot_be_updated_by_non_admin(): void
    {
        $invoice = Invoice::factory()->create([
            'created_by' => $this->adv->id,
            'is_locked' => true,
        ]);

        $response = $this->actingAs($this->adv)
            ->patchJson("/invoices/{$invoice->id}", [
                'client_name' => 'Modified',
            ]);

        $response->assertForbidden();

        // Admin can update locked invoice
        $response = $this->actingAs($this->admin)
            ->patchJson("/invoices/{$invoice->id}", [
                'client_name' => 'Modified by Admin',
            ]);

        $response->assertSuccessful();
    }

    /**
     * Test that only admin can delete invoices
     */
    public function test_only_admin_can_delete_invoices(): void
    {
        $invoice = Invoice::factory()->create(['created_by' => $this->adv->id]);

        // ADV cannot delete
        $this->actingAs($this->adv)
            ->deleteJson("/invoices/{$invoice->id}")
            ->assertForbidden();

        // Admin can delete
        $this->actingAs($this->admin)
            ->deleteJson("/invoices/{$invoice->id}")
            ->assertSuccessful();
    }

    /**
     * Test that only admin/superviseur can manage clients
     */
    public function test_only_admin_superviseur_can_manage_clients(): void
    {
        $client = Client::factory()->create();

        // ADV cannot update client
        $this->actingAs($this->adv)
            ->patchJson("/clients/{$client->id}", ['nom' => 'Updated'])
            ->assertForbidden();

        // Superviseur can update client
        $this->actingAs($this->superviseur)
            ->patchJson("/clients/{$client->id}", ['nom' => 'Updated'])
            ->assertSuccessful();

        // Admin can update client
        $this->actingAs($this->admin)
            ->patchJson("/clients/{$client->id}", ['nom' => 'Updated by Admin'])
            ->assertSuccessful();
    }

    /**
     * Test that unapproved users have limited access
     */
    public function test_unapproved_user_has_limited_access(): void
    {
        // Unapproved user cannot view invoices
        $this->actingAs($this->unapprovedUser)
            ->get('/invoices')
            ->assertForbidden();

        // Unapproved user cannot create invoices
        $this->actingAs($this->unapprovedUser)
            ->postJson('/invoices', [
                'invoice_number' => 'INV-001',
                'client_name' => 'Test Client',
            ])
            ->assertForbidden();
    }

    /**
     * Test that export operations are restricted
     */
    public function test_only_admin_superviseur_can_export_data(): void
    {
        // ADV cannot export
        $this->actingAs($this->adv)
            ->get('/api/dashboard/margin-export?dimension=product')
            ->assertForbidden();

        // Superviseur can export
        $this->actingAs($this->superviseur)
            ->get('/api/dashboard/margin-export?dimension=product')
            ->assertSuccessful();

        // Admin can export
        $this->actingAs($this->admin)
            ->get('/api/dashboard/margin-export?dimension=product')
            ->assertSuccessful();
    }
}
