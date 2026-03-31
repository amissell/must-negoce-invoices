<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('objectifs_vendeurs', function (Blueprint $table) {
            $table->decimal('objectif_spark', 12, 2)->default(0)->after('annee');
            $table->decimal('objectif_ecosolutions', 12, 2)->default(0)->after('objectif_spark');
            $table->decimal('objectif_total', 12, 2)->default(0)->after('objectif_ecosolutions');
            $table->decimal('prime', 12, 2)->default(0)->after('objectif_total');

            $table->dropColumn('objectif');
        });
    }

    public function down(): void
    {
        Schema::table('objectifs_vendeurs', function (Blueprint $table) {
            $table->decimal('objectif', 12, 2)->default(0);
            $table->dropColumn([
                'objectif_spark',
                'objectif_ecosolutions',
                'objectif_total',
                'prime',
            ]);
        });
    }
};
