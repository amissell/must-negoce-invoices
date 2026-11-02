<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            // The legacy invoice_id is retained for backwards compatibility; new allocations use paiement_invoice.
            $table->foreignId('invoice_id')->nullable()->change();
            $table->foreignId('client_id')->nullable()->after('id')->constrained('clients')->nullOnDelete();
            $table->string('banque')->nullable()->after('reference');
            $table->date('date_encaissement')->nullable()->after('date_paiement');
            $table->decimal('montant_total', 12, 2)->nullable()->after('montant');
        });
        Schema::create('paiement_invoice', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paiement_id')->constrained('paiements')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->decimal('montant_affecte', 12, 2);
            $table->timestamps();
            $table->unique(['paiement_id', 'invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiement_invoice');
        Schema::table('paiements', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn(['client_id', 'banque', 'date_encaissement', 'montant_total']);
        });
    }
};
