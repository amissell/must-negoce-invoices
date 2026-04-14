<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suivi_vendeurs', function (Blueprint $table) {
            if (!Schema::hasColumn('suivi_vendeurs', 'jour_repos')) {
                $table->boolean('jour_repos')->default(0)->after('date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('suivi_vendeurs', function (Blueprint $table) {
            if (Schema::hasColumn('suivi_vendeurs', 'jour_repos')) {
                $table->dropColumn('jour_repos');
            }
        });
    }
};