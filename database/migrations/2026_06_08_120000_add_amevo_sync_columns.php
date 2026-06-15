<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('suivi_vendeurs') && !Schema::hasColumn('suivi_vendeurs', 'vendeur_id')) {
            Schema::table('suivi_vendeurs', function (Blueprint $table) {
                $table->string('vendeur_id')->nullable()->index()->after('notes');
            });
        }

        if (Schema::hasTable('suivi_categories') && !Schema::hasColumn('suivi_categories', 'categorie_id')) {
            Schema::table('suivi_categories', function (Blueprint $table) {
                $table->string('categorie_id')->nullable()->index()->after('categorie');
            });
        }

        if (Schema::hasTable('suivi_categories') && !Schema::hasColumn('suivi_categories', 'vendeur_id')) {
            Schema::table('suivi_categories', function (Blueprint $table) {
                $table->string('vendeur_id')->nullable()->index()->after('categorie_id');
            });
        }

        if (Schema::hasTable('categories') && !Schema::hasColumn('categories', 'amevo_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('amevo_id')->nullable()->unique()->after('nom');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('suivi_categories')) {
            Schema::table('suivi_categories', function (Blueprint $table) {
                if (Schema::hasColumn('suivi_categories', 'vendeur_id')) {
                    $table->dropColumn('vendeur_id');
                }

                if (Schema::hasColumn('suivi_categories', 'categorie_id')) {
                    $table->dropColumn('categorie_id');
                }
            });
        }

        if (Schema::hasTable('suivi_vendeurs') && Schema::hasColumn('suivi_vendeurs', 'vendeur_id')) {
            Schema::table('suivi_vendeurs', function (Blueprint $table) {
                $table->dropColumn('vendeur_id');
            });
        }

        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'amevo_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('amevo_id');
            });
        }
    }
};
