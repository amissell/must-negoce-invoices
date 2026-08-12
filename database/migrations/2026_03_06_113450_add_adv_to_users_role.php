<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'superviseur', 'adv') NOT NULL DEFAULT 'superviseur'");

            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'superviseur', 'adv'])->default('superviseur')->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'superviseur') NOT NULL DEFAULT 'superviseur'");

            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'superviseur'])->default('superviseur')->change();
        });
    }
};
