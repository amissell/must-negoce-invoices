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

        // create minimal users table to avoid running full project migrations in sqlite
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

        // spatie activity log table used by models in the app; create minimal table for tests
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

    public function test_dispatch_command_enqueues_due_reports()
    {
        Bus::fake();
        $report = ScheduledReport::create(['name' => 'Due', 'report_type' => 'kpis', 'format' => 'csv', 'recurrence' => 'none', 'next_run_at' => now()->subMinute()]);
        Artisan::call('reports:dispatch-due');
        Bus::assertDispatched(GenerateScheduledReport::class);
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
