<?php

namespace Tests\Feature;

use App\Jobs\GenerateScheduledReport;
use App\Models\ScheduledReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use App\Mail\ScheduledReportReady;
use Schema;

class ScheduledReportsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('users')) {
            \Schema::create('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('role')->default('superviseur');
                $table->string('status')->default('approved');
                $table->rememberToken();
                $table->timestamps();
            });
        }

        // ensure our scheduled tables exist
        if (!\Schema::hasTable('scheduled_reports')) {
            \Schema::create('scheduled_reports', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->string('name');
                $table->string('report_type');
                $table->json('params')->nullable();
                $table->json('recipients')->nullable();
                $table->string('format')->default('csv');
                $table->string('recurrence')->default('none');
                $table->timestamp('next_run_at')->nullable();
                $table->timestamp('last_run_at')->nullable();
                $table->integer('retention_days')->default(30);
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }
        if (!\Schema::hasTable('scheduled_report_runs')) {
            \Schema::create('scheduled_report_runs', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->foreignId('scheduled_report_id');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->integer('duration_ms')->nullable();
                $table->string('status')->default('pending');
                $table->text('error_message')->nullable();
                $table->string('file_path')->nullable();
                $table->bigInteger('file_size')->nullable();
                $table->timestamps();
            });
        }

        if (!\Schema::hasTable('activity_log')) {
            \Schema::create('activity_log', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('log_name')->nullable();
                $table->text('description')->nullable();
                $table->text('subject_type')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->text('properties')->nullable();
                $table->string('batch_uuid')->nullable();
                $table->string('event')->nullable();
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamps();
            });
        }
    }
    public function test_create_schedule_requires_validation_and_allows_admin()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $this->actingAs($admin);

        $res = $this->postJson('/admin/scheduled-reports', [
            'name' => 'Monthly margins',
            'report_type' => 'margins',
            'format' => 'csv',
            'recurrence' => 'monthly',
            'recipients' => ['foo@example.com'],
        ]);

        $res->assertStatus(201);
        $this->assertDatabaseHas('scheduled_reports', ['name' => 'Monthly margins']);
    }

    public function test_profitability_reports_are_accepted_for_scheduling_and_generation()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $this->actingAs($admin);

        $res = $this->postJson('/admin/scheduled-reports', [
            'name' => 'Monthly profitability',
            'report_type' => 'profitability',
            'format' => 'csv',
            'recurrence' => 'monthly',
            'recipients' => ['foo@example.com'],
        ]);

        $res->assertStatus(201);
        $this->assertDatabaseHas('scheduled_reports', ['name' => 'Monthly profitability', 'report_type' => 'profitability']);

        $report = ScheduledReport::query()->where('name', 'Monthly profitability')->firstOrFail();
        $job = new GenerateScheduledReport($report);

        $this->app->instance(
            \App\Services\Analytics\ProfitabilityAnalyticsService::class,
            \Mockery::mock(\App\Services\Analytics\ProfitabilityAnalyticsService::class, function ($mock) {
                $mock->shouldReceive('report')->andReturn([
                    'products' => [
                        (object) [
                            'label' => 'Produit rentable',
                            'revenue' => 100,
                            'purchase_cost' => 70,
                            'gross_profit' => 30,
                            'gross_margin_percent' => 30,
                            'quantity' => 1,
                        ],
                    ],
                ]);
            })
        );

        $job->handle(app(\App\Services\ExecutiveDashboardService::class), app(\App\Services\Analytics\ProfitabilityAnalyticsService::class));
        $this->assertNotNull($report->runs()->latest()->first());
    }

    public function test_supervisor_can_create_and_list_reports()
    {
        $supervisor = User::factory()->create(['role' => 'superviseur', 'status' => 'approved']);
        $this->actingAs($supervisor);

        $res = $this->postJson('/admin/scheduled-reports', [
            'name' => 'Profitability weekly',
            'report_type' => 'profitability',
            'format' => 'csv',
            'recurrence' => 'weekly',
            'params' => ['dimension' => 'customer', 'from' => '2026-08-01', 'to' => '2026-08-31'],
            'recipients' => ['ops@example.com'],
        ]);

        $res->assertStatus(201);
        $this->getJson('/admin/scheduled-reports')->assertStatus(200)
            ->assertJsonFragment(['name' => 'Profitability weekly']);
    }

    public function test_admin_can_update_report_status_and_fetch_latest_download_url()
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $this->actingAs($admin);

        $report = ScheduledReport::create([
            'name' => 'TVA report',
            'report_type' => 'tva',
            'format' => 'csv',
            'recurrence' => 'monthly',
            'status' => 'active',
            'params' => ['from' => '2026-08-01', 'to' => '2026-08-31'],
            'recipients' => ['ops@example.com'],
        ]);

        $this->putJson('/admin/scheduled-reports/'.$report->id, [
            'status' => 'inactive',
            'params' => ['from' => '2026-07-01', 'to' => '2026-07-31'],
        ])->assertStatus(200);

        $this->assertDatabaseHas('scheduled_reports', ['id' => $report->id, 'status' => 'inactive']);

        $report->runs()->create([
            'started_at' => now(),
            'finished_at' => now(),
            'status' => 'success',
            'file_path' => 'reports/latest.csv',
            'file_size' => 10,
        ]);
        Storage::disk('local')->put('reports/latest.csv', 'ok');

        $this->getJson('/admin/scheduled-reports/'.$report->id.'/download-latest')
            ->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    public function test_admin_can_update_report()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $this->actingAs($admin);

        $report = ScheduledReport::create([
            'name' => 'Original report',
            'report_type' => 'kpis',
            'format' => 'csv',
            'recurrence' => 'monthly',
            'status' => 'active',
            'params' => ['from' => '2026-01-01', 'to' => '2026-01-31'],
            'recipients' => ['first@example.com'],
        ]);

        $this->putJson('/admin/scheduled-reports/'.$report->id, [
            'name' => 'Updated report',
            'status' => 'inactive',
            'params' => ['from' => '2026-02-01', 'to' => '2026-02-28'],
        ])->assertStatus(200)
            ->assertJsonPath('name', 'Updated report')
            ->assertJsonPath('status', 'inactive');

        $this->assertDatabaseHas('scheduled_reports', ['id' => $report->id, 'name' => 'Updated report', 'status' => 'inactive']);
    }

    public function test_supervisor_can_update_report_when_authorized()
    {
        $supervisor = User::factory()->create(['role' => 'superviseur', 'status' => 'approved']);
        $this->actingAs($supervisor);

        $report = ScheduledReport::create([
            'name' => 'Supervisor report',
            'report_type' => 'profitability',
            'format' => 'csv',
            'recurrence' => 'weekly',
            'status' => 'active',
            'params' => ['dimension' => 'customer'],
            'recipients' => ['ops@example.com'],
        ]);

        $this->putJson('/admin/scheduled-reports/'.$report->id, [
            'name' => 'Supervisor updated',
            'status' => 'inactive',
        ])->assertStatus(200)
            ->assertJsonPath('name', 'Supervisor updated');

        $this->assertDatabaseHas('scheduled_reports', ['id' => $report->id, 'name' => 'Supervisor updated', 'status' => 'inactive']);
    }

    public function test_unauthorized_user_cannot_update_report()
    {
        $regular = User::factory()->create(['role' => 'adv', 'status' => 'approved']);
        $this->actingAs($regular);

        $report = ScheduledReport::create([
            'name' => 'Protected report',
            'report_type' => 'margins',
            'format' => 'csv',
            'recurrence' => 'none',
            'status' => 'active',
        ]);

        $this->putJson('/admin/scheduled-reports/'.$report->id, ['name' => 'Hacked'])
            ->assertStatus(403);
    }

    public function test_update_report_validates_input()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $this->actingAs($admin);

        $report = ScheduledReport::create([
            'name' => 'Validate report',
            'report_type' => 'tva',
            'format' => 'csv',
            'recurrence' => 'none',
        ]);

        $this->putJson('/admin/scheduled-reports/'.$report->id, [
            'report_type' => 'unknown',
        ])->assertStatus(422);
    }

    public function test_update_report_persists_changes()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $this->actingAs($admin);

        $report = ScheduledReport::create([
            'name' => 'Persist report',
            'report_type' => 'margins',
            'format' => 'csv',
            'recurrence' => 'daily',
            'status' => 'active',
            'recipients' => ['old@example.com'],
        ]);

        $this->putJson('/admin/scheduled-reports/'.$report->id, [
            'recipients' => ['new@example.com'],
            'format' => 'xlsx',
        ])->assertStatus(200);

        $this->assertDatabaseHas('scheduled_reports', ['id' => $report->id, 'format' => 'xlsx']);
        $this->assertSame(['new@example.com'], ScheduledReport::find($report->id)->recipients);
    }

    public function test_run_now_dispatches_job()
    {
        Bus::fake();
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $this->actingAs($admin);

        $report = ScheduledReport::create([ 'name' => 'Test', 'report_type' => 'kpis', 'format' => 'csv', 'recurrence' => 'none' ]);

        $res = $this->postJson('/admin/scheduled-reports/'.$report->id.'/run');
        $res->assertStatus(200);

        Bus::assertDispatched(GenerateScheduledReport::class);
    }

    public function test_dispatch_command_enqueues_due_daily_reports()
    {
        Bus::fake();
        $report = ScheduledReport::create(['name' => 'Due', 'report_type' => 'kpis', 'format' => 'csv', 'recurrence' => 'daily', 'status' => 'active', 'next_run_at' => now()->subMinute()]);
        Artisan::call('reports:dispatch-due');
        Bus::assertDispatched(GenerateScheduledReport::class);
        $this->assertNotNull($report->fresh()->next_run_at);
    }

    public function test_none_recurrence_does_not_reschedule_or_dispatch()
    {
        Bus::fake();
        $report = ScheduledReport::create([
            'name' => 'No recurrence',
            'report_type' => 'kpis',
            'format' => 'csv',
            'recurrence' => 'none',
            'status' => 'active',
            'next_run_at' => now()->subMinute(),
        ]);

        Artisan::call('reports:dispatch-due');

        Bus::assertNotDispatched(GenerateScheduledReport::class);
        $this->assertNull($report->fresh()->next_run_at);
        $this->assertSame('inactive', $report->fresh()->status);
    }

    public function test_daily_recurrence_compute_next_run_at()
    {
        $report = new ScheduledReport([
            'recurrence' => 'daily',
            'next_run_at' => now()->subMinute(),
        ]);

        $next = $report->computeNextRunAt($report->next_run_at);

        $this->assertNotNull($next);
        $this->assertTrue($next->greaterThan($report->next_run_at));
    }

    public function test_weekly_recurrence_compute_next_run_at()
    {
        $report = new ScheduledReport([
            'recurrence' => 'weekly',
            'next_run_at' => now()->subWeek()->subMinute(),
        ]);

        $next = $report->computeNextRunAt($report->next_run_at);

        $this->assertNotNull($next);
        $this->assertTrue($next->greaterThan($report->next_run_at));
    }

    public function test_monthly_recurrence_compute_next_run_at()
    {
        $report = new ScheduledReport([
            'recurrence' => 'monthly',
            'next_run_at' => now()->subMonth()->subMinute(),
        ]);

        $next = $report->computeNextRunAt($report->next_run_at);

        $this->assertNotNull($next);
        $this->assertTrue($next->greaterThan($report->next_run_at));
    }

    public function test_inactive_report_is_skipped_by_dispatch_command()
    {
        Bus::fake();
        ScheduledReport::create([
            'name' => 'Inactive report',
            'report_type' => 'kpis',
            'format' => 'csv',
            'recurrence' => 'daily',
            'status' => 'inactive',
            'next_run_at' => now()->subMinute(),
        ]);

        Artisan::call('reports:dispatch-due');

        Bus::assertNotDispatched(GenerateScheduledReport::class);
    }

    public function test_duplicate_dispatch_is_prevented_for_due_report()
    {
        Bus::fake();
        $report = ScheduledReport::create([
            'name' => 'Duplicate guard',
            'report_type' => 'kpis',
            'format' => 'csv',
            'recurrence' => 'daily',
            'status' => 'active',
            'next_run_at' => now()->subMinute(),
        ]);

        Artisan::call('reports:dispatch-due');
        Artisan::call('reports:dispatch-due');

        Bus::assertDispatchedTimes(GenerateScheduledReport::class, 1);
        $this->assertNotNull($report->fresh()->next_run_at);
    }

    public function test_next_run_at_updates_after_execution()
    {
        Storage::fake('local');

        $report = ScheduledReport::create([
            'name' => 'Run update',
            'report_type' => 'kpis',
            'format' => 'csv',
            'recurrence' => 'daily',
            'status' => 'active',
            'next_run_at' => now()->subMinute(),
        ]);

        $previous = $report->next_run_at->copy();
        $mock = \Mockery::mock(\App\Services\ExecutiveDashboardService::class);
        $mock->shouldReceive('getDashboardData')->andReturn(['a' => 1]);
        $this->app->instance(\App\Services\ExecutiveDashboardService::class, $mock);

        $job = new GenerateScheduledReport($report);
        $job->handle(app(\App\Services\ExecutiveDashboardService::class));

        $report->refresh();
        $this->assertNotNull($report->last_run_at);
        $this->assertTrue($report->next_run_at->greaterThan($previous));
    }

    public function test_signed_download_requires_auth()
    {
        Storage::fake('local');
        $report = ScheduledReport::create(['name' => 'R', 'report_type' => 'kpis', 'format' => 'csv', 'recurrence' => 'none']);
        $run = $report->runs()->create(['started_at' => now(), 'finished_at' => now(), 'status' => 'success', 'file_path' => 'reports/test.csv', 'file_size' => 10]);
        Storage::disk('local')->put('reports/test.csv', 'ok');

        $url = URL::signedRoute('admin.scheduled_reports.download', ['run' => $run->id], now()->addMinutes(5));

        // unauthenticated should be blocked (middleware auth applies in routes)
        $this->get($url)->assertStatus(302);

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $this->actingAs($admin)->get($url)->assertStatus(200)->assertHeader('content-disposition');
    }

    public function test_xlsx_generation_creates_real_workbook()
    {
        Storage::fake('local');

        $report = ScheduledReport::create([
            'name' => 'XLSX test',
            'report_type' => 'kpis',
            'format' => 'xlsx',
            'recurrence' => 'none',
            'recipients' => ['notify@example.com'],
        ]);

        $mock = \Mockery::mock(\App\Services\ExecutiveDashboardService::class);
        $mock->shouldReceive('getDashboardData')->andReturn(['a' => 1]);
        $this->app->instance(\App\Services\ExecutiveDashboardService::class, $mock);

        $job = new GenerateScheduledReport($report);
        $job->handle(app(\App\Services\ExecutiveDashboardService::class));

        $run = $report->runs()->latest()->firstOrFail();
        $path = Storage::disk('local')->path($run->file_path);

        $this->assertStringEndsWith('.xlsx', $run->file_path);
        $this->assertFileExists($path);
        $this->assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', mime_content_type($path));

        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $this->assertNotFalse($zip->locateName('xl/workbook.xml'));
        $zip->close();

        $this->assertSame('PK', substr(file_get_contents($path), 0, 2));
    }

    public function test_xlsx_download_has_excel_mime_and_filename()
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $this->actingAs($admin);

        $report = ScheduledReport::create([
            'name' => 'XLSX download',
            'report_type' => 'kpis',
            'format' => 'xlsx',
            'recurrence' => 'none',
            'recipients' => ['notify@example.com'],
        ]);

        $mock = \Mockery::mock(\App\Services\ExecutiveDashboardService::class);
        $mock->shouldReceive('getDashboardData')->andReturn(['a' => 1]);
        $this->app->instance(\App\Services\ExecutiveDashboardService::class, $mock);

        $job = new GenerateScheduledReport($report);
        $job->handle(app(\App\Services\ExecutiveDashboardService::class));

        $run = $report->runs()->latest()->firstOrFail();
        $url = URL::signedRoute('admin.scheduled_reports.download', ['run' => $run->id], now()->addMinutes(5));

        $response = $this->get($url);
        $response->assertStatus(200);
        $this->assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_job_generates_file_and_sends_mail()
    {
        Mail::fake();
        Storage::fake('local');

        $report = ScheduledReport::create(['name' => 'MailTest', 'report_type' => 'kpis', 'format' => 'csv', 'recurrence' => 'none', 'recipients' => ['notify@example.com']]);

        // mock dashboard service used by job
        $mock = \Mockery::mock(\App\Services\ExecutiveDashboardService::class);
        $mock->shouldReceive('getDashboardData')->andReturn(['a' => 1]);
        $this->app->instance(\App\Services\ExecutiveDashboardService::class, $mock);

        $job = new GenerateScheduledReport($report);
        $job->handle(app(\App\Services\ExecutiveDashboardService::class));

        $run = $report->runs()->latest()->firstOrFail();
        $this->assertStringEndsWith('.csv', $run->file_path);
        $this->assertNotSame('PK', substr(file_get_contents(Storage::disk('local')->path($run->file_path)), 0, 2));

        Mail::assertSent(ScheduledReportReady::class);
    }

    public function test_cleanup_removes_expired_files_and_runs()
    {
        Storage::fake('local');
        $report = ScheduledReport::create(['name' => 'C', 'report_type' => 'kpis', 'format' => 'csv', 'recurrence' => 'none', 'retention_days' => 0]);
        $run = $report->runs()->create(['started_at' => now()->subDays(10), 'finished_at' => now()->subDays(10), 'status' => 'success', 'file_path' => 'reports/old.csv', 'file_size' => 10]);
        Storage::disk('local')->put('reports/old.csv', 'old');

        Artisan::call('reports:cleanup');

        $this->assertDatabaseMissing('scheduled_report_runs', ['id' => $run->id]);
        $this->assertFalse(Storage::disk('local')->exists('reports/old.csv'));
    }
}
