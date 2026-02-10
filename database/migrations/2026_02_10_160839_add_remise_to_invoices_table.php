<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('remise_percentage', 5, 2)->default(0)->after('montant_ht');
            $table->decimal('remise_amount', 10, 2)->default(0)->after('remise_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['remise_percentage', 'remise_amount']);
        });
    }
};