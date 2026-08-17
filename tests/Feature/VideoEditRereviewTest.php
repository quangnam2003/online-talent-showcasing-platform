<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Models\Video;
use App\Notifications\VideoSubmitted;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

// Hoi quy FR8: sua noi dung (tieu de / mo ta / anh bia / the loai) cua video DA DUYET
// phai keo trang thai ve pending + bao admin duyet lai — chan chieu "duyet xong sua thanh vi pham".
// Dung DatabaseTransactions (khong migrate) de chay duoc tren DB dev MySQL.
class VideoEditRereviewTest extends TestCase
{
    use DatabaseTransactions;

    private function makeVideo(User $owner, string $status = 'approved'): Video
    {
        $slug = 'test-'.uniqid();
        $category = Category::create(['name' => 'Thể loại '.$slug, 'slug' => $slug]);

        return Video::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Tiêu đề gốc',
            'description' => 'Mô tả gốc',
            'privacy' => 'public',
            'allow_comments' => true,
            'status' => $status,
            'file_path' => 'videos/test.mp4',
            'mime_type' => 'video/mp4',
        ]);
    }

    private function payload(Video $video, array $overrides = []): array
    {
        return array_merge([
            'title' => $video->title,
            'description' => $video->description,
            'category_id' => $video->category_id,
            'privacy' => $video->privacy,
            'allow_comments' => 1,
        ], $overrides);
    }

    public function test_editing_title_of_approved_video_resets_to_pending_and_notifies_admin(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin'])->fresh();
        $owner = User::factory()->create()->fresh();
        $video = $this->makeVideo($owner);

        $this->actingAs($owner)
            ->put(route('videos.update', $video), $this->payload($video, ['title' => 'Tiêu đề mới']))
            ->assertRedirect(route('videos.mine'));

        $this->assertSame('pending', $video->fresh()->status);
        Notification::assertSentTo($admin, VideoSubmitted::class, fn ($n) => $n->edited === true);
    }

    public function test_editing_only_privacy_keeps_approved_status(): void
    {
        Notification::fake();

        $owner = User::factory()->create()->fresh();
        $video = $this->makeVideo($owner);

        $this->actingAs($owner)
            ->put(route('videos.update', $video), $this->payload($video, ['privacy' => 'private']))
            ->assertRedirect(route('videos.mine'));

        $video->refresh();
        $this->assertSame('approved', $video->status);
        $this->assertSame('private', $video->privacy);
        Notification::assertNothingSent();
    }

    public function test_admin_edit_does_not_reset_status(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin'])->fresh();
        $owner = User::factory()->create()->fresh();
        $video = $this->makeVideo($owner);

        $this->actingAs($admin)
            ->put(route('videos.update', $video), $this->payload($video, ['title' => 'Admin sửa tiêu đề']))
            ->assertRedirect(route('videos.mine'));

        $this->assertSame('approved', $video->fresh()->status);
        Notification::assertNothingSent();
    }

    public function test_editing_pending_video_stays_pending_without_new_notification(): void
    {
        Notification::fake();

        $owner = User::factory()->create()->fresh();
        $video = $this->makeVideo($owner, 'pending');

        $this->actingAs($owner)
            ->put(route('videos.update', $video), $this->payload($video, ['title' => 'Sửa khi đang chờ duyệt']))
            ->assertRedirect(route('videos.mine'));

        $this->assertSame('pending', $video->fresh()->status);
        Notification::assertNothingSent();
    }
}
