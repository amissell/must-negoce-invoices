<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('properties');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->index('created_at');
            $table->index(['subject_type', 'subject_id']);
            $table->index('causer_id');
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex(['activity_log_created_at_index']);
            $table->dropIndex(['activity_log_subject_type_subject_id_index']);
            $table->dropIndex(['activity_log_causer_id_index']);
            $table->dropColumn(['ip_address', 'user_agent']);
        });
    }
};
