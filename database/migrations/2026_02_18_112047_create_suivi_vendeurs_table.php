<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('suivi_vendeurs', function (Blueprint $table) {
        $table->id();
        $table->string('vendeur_nom');
        $table->date('date');
        $table->decimal('chiffre', 10, 2)->default(0);
        $table->decimal('total_paye', 10, 2)->default(0);
        $table->decimal('reste_a_payer', 10, 2)->virtualAs('chiffre - total_paye');
        $table->time('heure_debut')->nullable();
        $table->time('heure_fin')->nullable();
        $table->integer('bl')->default(0); 
        $table->text('notes')->nullable();
        $table->foreignId('created_by')->nullable()->constrained('users');
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suivi_vendeurs');
    }
};
