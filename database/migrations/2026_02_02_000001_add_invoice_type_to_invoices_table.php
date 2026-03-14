<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {

        $table->enum('type', ['automatic', 'manual'])->default('automatic')->after('invoice_number');
            
            // Add client contact fields for manual invoices
            $table->string('client_name')->nullable();
            $table->string('client_address')->nullable()->after('client_name');
            $table->string('client_phone')->nullable()->after('client_address');
            $table->string('client_email')->nullable()->after('client_phone');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['type', 'client_address', 'client_phone', 'client_email']);
        });
    }
};