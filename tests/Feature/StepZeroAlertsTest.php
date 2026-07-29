<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Client;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StepZeroAlertsTest extends TestCase
{
    public function test_alert_model_uses_constrained_type_and_severity_and_morphs_related_records(): void
    {
        $client = Client::create(['nom' => 'Client en alerte']);
        $alert = Alert::create([
            'type' => Alert::TYPE_CREDIT_LIMIT_BREACH,
            'severity' => Alert::SEVERITY_WARNING,
            'message' => 'Seuil de credit depasse.',
            'related_type' => Client::class,
            'related_id' => $client->id,
        ]);

        $this->assertTrue($alert->related->is($client));
        $this->assertFalse(Validator::make([
            'type' => 'unknown',
            'severity' => 'unknown',
        ], Alert::validationRules())->passes());
    }

    public function test_alerts_migration_rolls_back_and_reapplies_cleanly(): void
    {
        $migration = require database_path('migrations/2026_07_29_000004_create_alerts_table.php');

        $migration->down();
        $this->assertFalse(Schema::hasTable('alerts'));

        $migration->up();
        $this->assertTrue(Schema::hasTable('alerts'));
        $this->assertTrue(Schema::hasColumns('alerts', [
            'type', 'severity', 'message', 'related_type', 'related_id', 'user_id', 'read_at',
        ]));
    }
}
