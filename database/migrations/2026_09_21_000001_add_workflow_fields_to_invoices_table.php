<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('vendeur_id')->nullable()->after('client_id')->constrained('vendeurs')->nullOnDelete();
            $table->string('delivery_status')->default('non_envoyee')->after('status');
            $table->timestamp('delivered_at')->nullable()->after('delivery_status');
            $table->date('remis_compta_at')->nullable()->after('delivered_at');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['vendeur_id']);
            $table->dropColumn(['vendeur_id', 'delivery_status', 'delivered_at', 'remis_compta_at']);
        });
    }
};
