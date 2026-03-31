<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('objectifs_vendeurs', function (Blueprint $table) {
            $table->id();
            $table->string('vendeur_nom');
            $table->unsignedTinyInteger('mois');
            $table->unsignedSmallInteger('annee');
            $table->decimal('objectif', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['vendeur_nom', 'mois', 'annee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('objectifs_vendeurs');
    }
};