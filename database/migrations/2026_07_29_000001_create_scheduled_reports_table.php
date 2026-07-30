<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('scheduled_reports')) {
            return;
        }

        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
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

    public function down()
    {
        if (!Schema::hasTable('scheduled_reports')) {
            return;
        }
        Schema::dropIfExists('scheduled_reports');
    }
};
