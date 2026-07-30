<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('clients')) {
            return;
        }

        Schema::table('clients', function (Blueprint $table) {
            if (! Schema::hasColumn('clients', 'credit_limit')) {
                $table->decimal('credit_limit', 12, 2)->nullable()->after('ville');
            }

            if (! Schema::hasColumn('clients', 'payment_terms_days')) {
                $table->unsignedSmallInteger('payment_terms_days')->nullable()->after('credit_limit');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('clients')) {
            return;
        }

        Schema::table('clients', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('clients', 'payment_terms_days') ? 'payment_terms_days' : null,
                Schema::hasColumn('clients', 'credit_limit') ? 'credit_limit' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
