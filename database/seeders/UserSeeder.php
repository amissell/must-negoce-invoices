<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if it doesn't exist
        User::firstOrCreate(
            ['email' => 'amalissellay01@gmail.com'],
            [
                'name' => 'AMAL',
                'password' => Hash::make('Aissell@+2001'), // Change this in production!
            ]
        );

        echo "✅ Admin user created successfully!\n";
        echo "📧 Email: admin@mustnegoce.com\n";
        echo "🔑 Password: password123\n";
        echo "⚠️  Please change the password after first login!\n";
    }
}
