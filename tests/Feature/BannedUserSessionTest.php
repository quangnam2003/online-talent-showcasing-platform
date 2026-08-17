<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

// Hoi quy: tai khoan bi khoa (is_active = false) phai bi dang xuat ngay o request
// ke tiep bang middleware EnsureUserIsActive, khong cho dung tiep phien cu.
// Dung DatabaseTransactions (khong migrate) de chay duoc tren DB dev MySQL.
class BannedUserSessionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_active_user_browses_normally(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get('/')->assertOk();
        $this->assertAuthenticatedAs($user);
    }

    public function test_banned_user_is_logged_out_on_next_request(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $user->forceFill(['is_active' => false])->save();

        $response = $this->get('/');

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Tài khoản của bạn đã bị khóa.');
        $this->assertGuest();
    }

    public function test_banned_user_gets_json_403_on_xhr(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->actingAs($user)
            ->getJson('/')
            ->assertStatus(403)
            ->assertJson(['ok' => false]);

        $this->assertGuest();
    }
}
