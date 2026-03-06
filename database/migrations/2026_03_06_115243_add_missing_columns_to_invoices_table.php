<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'payment_mode'))
                $table->string('payment_mode')->nullable()->after('client_ice');
            if (!Schema::hasColumn('invoices', 'tva_rate'))
                $table->decimal('tva_rate', 5, 2)->nullable()->after('total_ttc');
            if (!Schema::hasColumn('invoices', 'tva'))
                $table->decimal('tva', 10, 2)->nullable()->after('tva_rate');
            if (!Schema::hasColumn('invoices', 'montant_ht'))
                $table->decimal('montant_ht', 10, 2)->nullable()->after('tva');
            if (!Schema::hasColumn('invoices', 'remise_percentage'))
                $table->decimal('remise_percentage', 5, 2)->nullable()->after('montant_ht');
            if (!Schema::hasColumn('invoices', 'remise_amount'))
                $table->decimal('remise_amount', 10, 2)->nullable()->after('remise_percentage');
            if (!Schema::hasColumn('invoices', 'amount_in_words'))
                $table->string('amount_in_words')->nullable()->after('remise_amount');
            if (!Schema::hasColumn('invoices', 'created_by'))
                $table->unsignedBigInteger('created_by')->nullable()->after('amount_in_words');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'payment_mode', 'tva_rate', 'tva', 'montant_ht',
                'remise_percentage', 'remise_amount', 'amount_in_words', 'created_by'
            ]);
        });
    }
};