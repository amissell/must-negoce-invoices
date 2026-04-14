<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('objectifs_vendeurs', function (Blueprint $table) {
            $table->decimal('objectif_ca', 12, 2)->default(0)->after('annee');
            $table->unsignedInteger('objectif_bl')->default(0)->after('objectif_ca');
        });
    }

    public function down(): void
    {
        Schema::table('objectifs_vendeurs', function (Blueprint $table) {
            $table->dropColumn(['objectif_ca', 'objectif_bl']);
        });
    }
};