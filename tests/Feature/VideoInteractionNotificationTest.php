<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Models\Video;
use App\Notifications\VideoInteraction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

// FR4: chu video nhan thong bao khi co nguoi binh luan / tha tim / cham sao.
// Dung DatabaseTransactions (khong migrate) de chay duoc tren DB dev MySQL.
class VideoInteractionNotificationTest extends TestCase
{
    use DatabaseTransactions;

    private User $owner;

    private User $viewer;

    private Video $video;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->viewer = User::factory()->create();

        $slug = 'test-'.uniqid();
        $category = Category::create(['name' => 'Thể loại '.$slug, 'slug' => $slug]);

        $this->video = Video::create([
            'user_id' => $this->owner->id,
            'category_id' => $category->id,
            'title' => 'Video nhận tương tác',
            'privacy' => 'public',
            'allow_comments' => true,
            'status' => 'approved',
            'file_path' => 'videos/test.mp4',
            'mime_type' => 'video/mp4',
        ]);

        Notification::fake();
    }

    public function test_owner_notified_when_someone_comments(): void
    {
        $this->actingAs($this->viewer)
            ->post(route('comments.store', $this->video), ['content' => 'Hay quá!']);

        Notification::assertSentTo($this->owner, VideoInteraction::class, fn ($n) => $n->type === 'comment');
    }

    public function test_owner_not_notified_for_own_comment(): void
    {
        $this->actingAs($this->owner)
            ->post(route('comments.store', $this->video), ['content' => 'Cảm ơn mọi người!']);

        Notification::assertNothingSent();
    }

    public function test_owner_notified_on_like_but_not_on_unlike(): void
    {
        $this->actingAs($this->viewer)->post(route('reactions.store', $this->video), ['action' => 'like']);
        Notification::assertSentTo($this->owner, VideoInteraction::class, fn ($n) => $n->type === 'like');

        // Bam lan 2 = bo tim → khong gui them thong bao
        $this->actingAs($this->viewer)->post(route('reactions.store', $this->video), ['action' => 'like']);
        Notification::assertSentToTimes($this->owner, VideoInteraction::class, 1);
    }

    public function test_owner_notified_on_rate_only_when_stars_change(): void
    {
        $this->actingAs($this->viewer)->post(route('reactions.store', $this->video), ['action' => 'rate', 'stars' => 5]);
        Notification::assertSentTo(
            $this->owner,
            VideoInteraction::class,
            fn ($n) => $n->type === 'rate' && $n->stars === 5
        );

        // Cham lai dung 5 sao → khong spam
        $this->actingAs($this->viewer)->post(route('reactions.store', $this->video), ['action' => 'rate', 'stars' => 5]);
        Notification::assertSentToTimes($this->owner, VideoInteraction::class, 1);

        // Doi sang 3 sao → bao lai
        $this->actingAs($this->viewer)->post(route('reactions.store', $this->video), ['action' => 'rate', 'stars' => 3]);
        Notification::assertSentToTimes($this->owner, VideoInteraction::class, 2);
    }
}
