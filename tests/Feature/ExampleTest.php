<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Authenticate an admin user because the root route is protected by
        // auth + admin_or_adv middleware.
        $user = \App\Models\User::create([
            'name' => 'Test Admin',
            'email' => 'admin+' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'approved',
        ]);

        $this->actingAs($user);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
