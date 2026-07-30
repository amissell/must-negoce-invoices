<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIpAndAgentToActivityLogTable extends Migration
{
    public function up(): void
    {
        $tableName = config('activitylog.table_name');
        if (! Schema::hasTable($tableName)) {
            return;
        }

        if (! Schema::hasColumn($tableName, 'ip_address')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('ip_address', 45)->nullable()->after('description');
            });
        }

        if (! Schema::hasColumn($tableName, 'user_agent')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->text('user_agent')->nullable()->after('ip_address');
            });
        }
    }

    public function down(): void
    {
        $tableName = config('activitylog.table_name');
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (Schema::hasColumn($tableName, 'ip_address')) {
                $table->dropColumn('ip_address');
            }
            if (Schema::hasColumn($tableName, 'user_agent')) {
                $table->dropColumn('user_agent');
            }
        });
    }
}
