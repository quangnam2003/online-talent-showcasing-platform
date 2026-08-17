<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

// Hoi quy FR4: chu video khong duoc tu like / tu cham sao video cua chinh minh —
// tranh tu keo avg_rating, likes_count, trending_score lam lech Explore.
// Dung DatabaseTransactions (khong migrate) de chay duoc tren DB dev MySQL.
class ReactionSelfVoteTest extends TestCase
{
    use DatabaseTransactions;

    private function makeVideo(User $owner): Video
    {
        $slug = 'test-'.uniqid();
        $category = Category::create(['name' => 'Thể loại '.$slug, 'slug' => $slug]);

        return Video::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Video của chính chủ',
            'privacy' => 'public',
            'allow_comments' => true,
            'status' => 'approved',
            'file_path' => 'videos/test.mp4',
            'mime_type' => 'video/mp4',
        ]);
    }

    public function test_owner_cannot_like_own_video(): void
    {
        $owner = User::factory()->create();
        $video = $this->makeVideo($owner);

        $this->actingAs($owner)
            ->from(route('videos.show', $video))
            ->post(route('reactions.store', $video), ['action' => 'like'])
            ->assertRedirect(route('videos.show', $video))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('reactions', ['video_id' => $video->id, 'user_id' => $owner->id]);
        $this->assertSame(0, $video->fresh()->likes_count);
    }

    public function test_owner_cannot_rate_own_video(): void
    {
        $owner = User::factory()->create();
        $video = $this->makeVideo($owner);

        $this->actingAs($owner)
            ->post(route('reactions.store', $video), ['action' => 'rate', 'stars' => 5])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('reactions', ['video_id' => $video->id, 'user_id' => $owner->id]);
        $this->assertSame(0.0, $video->fresh()->avg_rating);
    }

    public function test_other_user_can_like_and_rate(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $video = $this->makeVideo($owner);

        $this->actingAs($viewer)->post(route('reactions.store', $video), ['action' => 'like']);
        $this->actingAs($viewer)->post(route('reactions.store', $video), ['action' => 'rate', 'stars' => 4]);

        $video->refresh();
        $this->assertSame(1, $video->likes_count);
        $this->assertSame(4.0, $video->avg_rating);
    }

    public function test_owner_sees_static_reaction_ui(): void
    {
        $owner = User::factory()->create();
        $video = $this->makeVideo($owner);

        $html = $this->actingAs($owner)->get(route('videos.show', $video))->getContent();

        $this->assertStringNotContainsString('name="action" value="like"', $html);
        $this->assertStringNotContainsString('name="action" value="rate"', $html);
    }
}
