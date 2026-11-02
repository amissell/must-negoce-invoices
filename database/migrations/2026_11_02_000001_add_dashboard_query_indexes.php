<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->index('invoice_date', 'invoices_dashboard_invoice_date_idx');
            $table->index('date_echeance', 'invoices_dashboard_due_date_idx');
        });

        Schema::table('paiements', function (Blueprint $table) {
            $table->index('date_paiement', 'paiements_dashboard_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_dashboard_invoice_date_idx');
            $table->dropIndex('invoices_dashboard_due_date_idx');
        });

        Schema::table('paiements', function (Blueprint $table) {
            $table->dropIndex('paiements_dashboard_date_idx');
        });
    }
};
