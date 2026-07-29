<?php

namespace Tests\Feature;

use App\Models\CompanyTarget;
use App\Models\Invoice;
use App\Models\User;
use App\Services\ExecutiveDashboardService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExecutiveDashboardStepOneTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Invoice::query()->delete();
        CompanyTarget::query()->delete();
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_company_target_migration_rolls_back_and_reapplies_cleanly(): void
    {
        $migration = require database_path('migrations/2026_07_29_000005_create_company_targets_table.php');

        $migration->down();
        $this->assertFalse(Schema::hasTable('company_targets'));

        $migration->up();
        $this->assertTrue(Schema::hasTable('company_targets'));
    }

    public function test_scorecard_uses_equivalent_periods_target_and_overdue_invoice_rules(): void
    {
        Carbon::setTestNow('2026-07-15 10:00:00');
        Cache::flush();
        CompanyTarget::query()->delete();
        CompanyTarget::create(['month' => 7, 'year' => 2026, 'target_amount' => 3000]);

        $client = \App\Models\Client::create(['nom' => 'Client KPI']);
        $this->invoice('2026-07-15', 600, $client->id, '2026-07-14');
        $this->invoice('2026-07-08', 300, $client->id);
        $this->invoice('2026-06-15', 150, $client->id);
        $this->invoice('2025-07-15', 100, $client->id);

        $data = app(ExecutiveDashboardService::class)->getDashboardData();

        $this->assertSame(600.0, $data['sales_pulse']['current']);
        $this->assertSame(900.0, $data['company_target']['actual']);
        $this->assertSame(3000.0, $data['company_target']['target']);
        $this->assertSame(1, $data['kpis']['overdue_invoice_count']);
        $this->assertSame(1, $data['kpis']['active_clients']['last_30_days']);
    }

    public function test_admin_can_read_dashboard_json_and_upsert_a_company_target(): void
    {
        $user = User::create([
            'name' => 'Dashboard Admin',
            'email' => 'dashboard-admin-' . uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'approved',
        ]);

        $this->actingAs($user)
            ->putJson(route('admin.company-targets.upsert'), ['month' => 8, 'year' => 2026, 'target_amount' => 5000])
            ->assertCreated()
            ->assertJsonPath('target_amount', '5000.00');

        $this->actingAs($user)
            ->getJson(route('dashboard.executive.data'))
            ->assertOk()
            ->assertJsonStructure(['kpis', 'sales_pulse', 'company_target', 'revenue_comparison']);

        $this->actingAs($user)
            ->getJson(route('admin.company-targets.index'))
            ->assertOk()
            ->assertJsonCount(1);
    }

    private function invoice(string $date, float $total, int $clientId, ?string $dueDate = null): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'KPI-' . uniqid(),
            'invoice_date' => $date,
            'client_id' => $clientId,
            'montant_ht' => $total / 1.2,
            'tva' => $total - ($total / 1.2),
            'total_ttc' => $total,
            'is_locked' => false,
        ]);

        $invoice->forceFill([
            'date_echeance' => $dueDate,
            'statut_paiement' => 'impayée',
            'montant_paye' => 0,
        ])->save();
    }
}
