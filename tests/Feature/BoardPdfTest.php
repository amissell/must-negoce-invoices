<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class BoardPdfTest extends TestCase
{
    public function test_non_admin_cannot_access_board_pdf(): void
    {
        $user = User::create([
            'name' => 'Regular',
            'email' => 'regular-' . uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'adv',
            'status' => 'approved',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.executive.board_pdf'))
            ->assertForbidden();
    }

    public function test_admin_can_download_board_pdf(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin-' . uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($user)
            ->get(route('dashboard.executive.board_pdf'));

        $response->assertOk();
        $this->assertStringContainsString('pdf', $response->headers->get('content-type'));
    }
}
