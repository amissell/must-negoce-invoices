<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Ma Société');
            $table->string('logo_path')->nullable();
            $table->string('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('ice', 50)->nullable();
            $table->string('if_number', 50)->nullable();
            $table->string('rc', 50)->nullable();
            $table->string('patente', 50)->nullable();
            $table->string('cnss', 50)->nullable();
            $table->decimal('tva_rate', 5, 2)->default(20.00);
            $table->string('currency', 10)->default('MAD');
            $table->string('invoice_prefix', 20)->default('FAC');
            $table->text('footer_text')->nullable();
            $table->text('legal_mentions')->nullable();
            $table->string('default_pdf_template', 20)->default('classic');
            $table->timestamps();
        });

        DB::table('company_settings')->insert([
            'name'                 => 'Sté. MUST-NEGOCE',
            'address'              => 'MASSIRA 1 C IMM 90 MARRAKECH',
            'phone'                => '0632629412',
            'ice'                  => '001996252000022',
            'if_number'            => '25055643',
            'rc'                   => '86613',
            'patente'              => '92105859',
            'tva_rate'             => 20.00,
            'currency'             => 'MAD',
            'invoice_prefix'       => 'FAC',
            'default_pdf_template' => 'classic',
            'footer_text'          => 'Consulting - Formation - Négoce',
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
