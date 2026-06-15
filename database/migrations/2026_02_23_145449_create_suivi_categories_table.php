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
    Schema::create('suivi_categories', function (Blueprint $table) {
        $table->id();
        $table->foreignId('suivi_vendeur_id')->constrained('suivi_vendeurs')->onDelete('cascade');
        $table->string('categorie');
        $table->string('categorie_id')->nullable()->index(); // foreign key dila la table categories
        $table->string('vendeur_id')->nullable()->index(); // foreign key dila la table vendeurs
        $table->integer('quantite')->default(0);
        $table->decimal('ca', 10, 2)->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suivi_categories');
    }
};
