<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\NewFollower;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

// Endpoint polling cho thong bao gan thoi gian thuc (/notifications/poll).
// Dung DatabaseTransactions (khong migrate) de chay duoc tren DB dev MySQL.
class NotificationPollTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_cannot_poll(): void
    {
        $this->getJson(route('notifications.poll'))->assertUnauthorized();
    }

    public function test_poll_returns_unread_count_and_new_items_since(): void
    {
        $me = User::factory()->create();
        $follower = User::factory()->create(['name' => 'Người Theo Dõi Mới']);

        // Thong bao cu (truoc moc since) → khong nam trong items nhung van tinh vao unread
        $me->notify(new NewFollower($follower));
        $old = $me->notifications()->first();
        $old->forceFill(['created_at' => now()->subMinutes(5), 'updated_at' => now()->subMinutes(5)])->save();

        $since = now()->subSecond()->toIso8601String();

        // Thong bao moi sau moc since
        $me->notify(new NewFollower($follower));

        $res = $this->actingAs($me)
            ->getJson(route('notifications.poll', ['since' => $since]))
            ->assertOk()
            ->assertJsonStructure(['unread', 'items', 'now'])
            ->assertJsonPath('unread', 2)
            ->assertJsonCount(1, 'items');

        $item = $res->json('items.0');
        $this->assertStringContainsString('Người Theo Dõi Mới', $item['message']);
        $this->assertSame('follower', $item['kind']);
        $this->assertStringContainsString('/users/'.$follower->id, $item['url']);
    }

    public function test_mark_single_notification_read(): void
    {
        $me = User::factory()->create();
        $follower = User::factory()->create();
        $me->notify(new NewFollower($follower));
        $me->notify(new NewFollower($follower));
        $target = $me->unreadNotifications()->first();

        $this->actingAs($me)
            ->postJson(route('notifications.read'), ['id' => $target->id])
            ->assertOk()
            ->assertJsonPath('unread', 1);

        $this->assertNotNull($target->fresh()->read_at);
    }

    public function test_cannot_mark_other_users_notification_read(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        $other->notify(new NewFollower($me));
        $theirs = $other->unreadNotifications()->first();

        $this->actingAs($me)->postJson(route('notifications.read'), ['id' => $theirs->id])->assertOk();

        $this->assertNull($theirs->fresh()->read_at);
    }

    public function test_layout_exposes_poll_config_and_badges(): void
    {
        $me = User::factory()->create();

        $html = $this->actingAs($me)->get('/')->getContent();

        $this->assertStringContainsString('id="ts-bell"', $html);
        $this->assertStringContainsString('data-noti-poll="'.route('notifications.poll').'"', $html);
        $this->assertStringContainsString('data-noti-read="'.route('notifications.read').'"', $html);
        $this->assertStringContainsString('data-noti-badge="dot"', $html);
        $this->assertStringContainsString('data-noti-badge="nav"', $html);
        $this->assertStringContainsString('id="ts-toasts"', $html);
    }
}
