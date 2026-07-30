<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DevAdminSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'devadmin@example.test'],
            [
                'name' => 'Dev Admin',
                'password' => Hash::make('secret123'),
                'role' => 'admin',
                'status' => 'approved',
            ]
        );
    }
}
