<?php

namespace Tests\Feature;

use App\Models\Client;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StepZeroClientCreditFieldsTest extends TestCase
{
    public function test_credit_fields_are_nullable_castable_and_mass_assignable(): void
    {
        $client = Client::create(['nom' => 'Client historique']);

        $this->assertNull($client->credit_limit);
        $this->assertNull($client->payment_terms_days);

        $client->update(['credit_limit' => 25000, 'payment_terms_days' => 45]);

        $this->assertSame('25000.00', $client->fresh()->credit_limit);
        $this->assertSame(45, $client->fresh()->payment_terms_days);
    }

    public function test_credit_fields_migration_rolls_back_and_reapplies_cleanly(): void
    {
        $migration = require database_path('migrations/2026_07_29_000002_add_credit_fields_to_clients_table.php');

        $migration->down();
        $this->assertFalse(Schema::hasColumn('clients', 'credit_limit'));
        $this->assertFalse(Schema::hasColumn('clients', 'payment_terms_days'));

        $migration->up();
        $this->assertTrue(Schema::hasColumn('clients', 'credit_limit'));
        $this->assertTrue(Schema::hasColumn('clients', 'payment_terms_days'));
    }
}
