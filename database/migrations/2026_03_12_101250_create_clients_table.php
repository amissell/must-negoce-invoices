<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clients')) {
            Schema::create('clients', function (Blueprint $table) {
                $table->id();
                $table->string('nom');
                $table->string('ice')->unique()->nullable();
                $table->string('if_number')->nullable();
                $table->string('rc')->nullable();
                $table->string('cnss')->nullable();
                $table->string('telephone')->nullable();
                $table->string('email')->nullable();
                $table->text('adresse')->nullable();
                $table->string('ville')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('invoices') && !Schema::hasColumn('invoices', 'client_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            });
        }

        if (Schema::hasTable('manual_invoices') && !Schema::hasColumn('manual_invoices', 'client_id')) {
            Schema::table('manual_invoices', function (Blueprint $table) {
                $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'client_id')) {
            Schema::table('invoices', fn(Blueprint $t) => $t->dropForeign(['client_id']));
        }

        if (Schema::hasTable('manual_invoices') && Schema::hasColumn('manual_invoices', 'client_id')) {
            Schema::table('manual_invoices', fn(Blueprint $t) => $t->dropForeign(['client_id']));
        }

        Schema::dropIfExists('clients');
    }
};