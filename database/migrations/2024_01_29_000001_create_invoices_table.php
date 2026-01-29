<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->decimal('montant_ht', 10, 2);
            $table->decimal('tva', 10, 2);
            $table->decimal('total_ttc', 10, 2);
            $table->boolean('is_locked')->default(false);
            $table->string('status')->default('Verrouillée');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};