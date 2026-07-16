<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoice_sequences', function (Blueprint $table) {
            $table->string('year_prefix', 2)->primary(); // e.g. "26"
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
        });

        // Seed current year with the highest existing invoice number so we
        // do not collide with previously issued numbers.
        $year = date('y');

        $lastNumber = (int) DB::table('invoices')
            ->where('invoice_number', 'like', $year . '/%')
            ->selectRaw('COALESCE(MAX(CAST(SUBSTRING(invoice_number, 4) AS UNSIGNED)), 0) as max_num')
            ->value('max_num');

        DB::table('invoice_sequences')->insert([
            'year_prefix' => $year,
            'last_number' => $lastNumber,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_sequences');
    }
};
