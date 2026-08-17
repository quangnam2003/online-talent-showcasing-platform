<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

// Bao mat: khong lo trang thai "bi khoa" khi mat khau sai (chong user enumeration).
// Dung DatabaseTransactions (khong migrate) de chay duoc tren DB dev MySQL.
class LoginBannedEnumerationTest extends TestCase
{
    use DatabaseTransactions;

    private function banned(): User
    {
        return User::factory()->create(['is_active' => false, 'password' => Hash::make('dung-mat-khau')]);
    }

    public function test_wrong_password_on_banned_account_gives_generic_error(): void
    {
        $user = $this->banned();

        $this->from('/login')
            ->post('/login', ['email' => $user->email, 'password' => 'sai-mat-khau'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'Email hoặc mật khẩu không đúng.']);

        $this->assertGuest();
    }

    public function test_wrong_password_on_unknown_email_gives_same_generic_error(): void
    {
        $this->from('/login')
            ->post('/login', ['email' => 'khong-ton-tai@example.com', 'password' => 'x'])
            ->assertSessionHasErrors(['email' => 'Email hoặc mật khẩu không đúng.']);
    }

    public function test_correct_password_on_banned_account_shows_banned_and_stays_logged_out(): void
    {
        $user = $this->banned();

        $this->from('/login')
            ->post('/login', ['email' => $user->email, 'password' => 'dung-mat-khau'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'Tài khoản của bạn đã bị khóa.']);

        $this->assertGuest();
    }

    public function test_active_account_logs_in_normally(): void
    {
        $user = User::factory()->create(['password' => Hash::make('dung-mat-khau')]);

        $this->post('/login', ['email' => $user->email, 'password' => 'dung-mat-khau'])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }
}
