<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'statut_paiement')) {
                $table->enum('statut_paiement', ['payée', 'impayée', 'partielle'])->default('impayée');
            }
            if (!Schema::hasColumn('invoices', 'montant_paye')) {
                $table->decimal('montant_paye', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('invoices', 'date_echeance')) {
                $table->date('date_echeance')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['statut_paiement', 'montant_paye', 'date_echeance']);
        });
    }
};
