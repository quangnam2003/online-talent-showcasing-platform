<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\User;
use App\Models\Video;
use App\Notifications\CommentReacted;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

// FR4: sua/xoa binh luan cua minh, chu video xoa binh luan nguoi khac,
// bay to cam xuc voi binh luan (toggle) + thong bao cho nguoi viet binh luan.
// Dung DatabaseTransactions (khong migrate) de chay duoc tren DB dev MySQL.
class CommentManagementTest extends TestCase
{
    use DatabaseTransactions;

    private User $owner;

    private User $commenter;

    private User $stranger;

    private Video $video;

    private Comment $comment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->commenter = User::factory()->create();
        $this->stranger = User::factory()->create();

        $slug = 'test-'.uniqid();
        $category = Category::create(['name' => 'Thể loại '.$slug, 'slug' => $slug]);

        $this->video = Video::create([
            'user_id' => $this->owner->id,
            'category_id' => $category->id,
            'title' => 'Video có bình luận',
            'privacy' => 'public',
            'allow_comments' => true,
            'status' => 'approved',
            'file_path' => 'videos/test.mp4',
            'mime_type' => 'video/mp4',
        ]);

        $this->comment = Comment::create([
            'user_id' => $this->commenter->id,
            'video_id' => $this->video->id,
            'content' => 'Bình luận gốc',
        ]);

        Notification::fake();
    }

    /* ---------- Sua ---------- */

    public function test_author_can_edit_own_comment(): void
    {
        $this->actingAs($this->commenter)
            ->put(route('comments.update', $this->comment), ['content' => 'Đã sửa'])
            ->assertSessionHas('success');

        $this->assertSame('Đã sửa', $this->comment->fresh()->content);
    }

    public function test_video_owner_and_stranger_cannot_edit_others_comment(): void
    {
        $this->actingAs($this->owner)
            ->put(route('comments.update', $this->comment), ['content' => 'Chủ video sửa'])
            ->assertForbidden();

        $this->actingAs($this->stranger)
            ->put(route('comments.update', $this->comment), ['content' => 'Người lạ sửa'])
            ->assertForbidden();

        $this->assertSame('Bình luận gốc', $this->comment->fresh()->content);
    }

    /* ---------- Xoa ---------- */

    public function test_author_can_delete_own_comment(): void
    {
        $this->actingAs($this->commenter)->delete(route('comments.destroy', $this->comment));
        $this->assertDatabaseMissing('comments', ['id' => $this->comment->id]);
    }

    public function test_video_owner_can_delete_others_comment_including_replies(): void
    {
        $reply = Comment::create([
            'user_id' => $this->stranger->id,
            'video_id' => $this->video->id,
            'parent_id' => $this->comment->id,
            'content' => 'Trả lời',
        ]);

        $this->actingAs($this->owner)->delete(route('comments.destroy', $reply));
        $this->assertDatabaseMissing('comments', ['id' => $reply->id]);

        $this->actingAs($this->owner)->delete(route('comments.destroy', $this->comment));
        $this->assertDatabaseMissing('comments', ['id' => $this->comment->id]);
    }

    public function test_stranger_cannot_delete_others_comment(): void
    {
        $this->actingAs($this->stranger)
            ->delete(route('comments.destroy', $this->comment))
            ->assertForbidden();

        $this->assertDatabaseHas('comments', ['id' => $this->comment->id]);
    }

    /* ---------- Cam xuc ---------- */

    public function test_react_notifies_comment_author_and_toggle_removes(): void
    {
        $this->actingAs($this->stranger)
            ->post(route('comments.react', $this->comment), ['type' => 'love']);

        $this->assertDatabaseHas('comment_reactions', [
            'comment_id' => $this->comment->id, 'user_id' => $this->stranger->id, 'type' => 'love',
        ]);
        Notification::assertSentTo($this->commenter, CommentReacted::class, fn ($n) => $n->type === 'love');

        // Doi sang haha → cap nhat, khong tao dong moi
        $this->actingAs($this->stranger)
            ->post(route('comments.react', $this->comment), ['type' => 'haha']);
        $this->assertSame(1, $this->comment->reactions()->count());
        $this->assertSame('haha', $this->comment->reactions()->first()->type);

        // Bam lai haha → bo cam xuc
        $this->actingAs($this->stranger)
            ->post(route('comments.react', $this->comment), ['type' => 'haha']);
        $this->assertSame(0, $this->comment->reactions()->count());
    }

    public function test_self_react_does_not_notify(): void
    {
        $this->actingAs($this->commenter)
            ->post(route('comments.react', $this->comment), ['type' => 'like']);

        $this->assertSame(1, $this->comment->reactions()->count());
        Notification::assertNothingSent();
    }

    public function test_invalid_reaction_type_rejected(): void
    {
        $this->actingAs($this->stranger)
            ->from(route('videos.show', $this->video))
            ->post(route('comments.react', $this->comment), ['type' => 'rocket'])
            ->assertSessionHasErrors('type');

        $this->assertSame(0, $this->comment->reactions()->count());
    }

    /* ---------- Giao dien ---------- */

    public function test_ui_shows_edit_only_to_author_and_delete_to_owner(): void
    {
        // Sua va xoa dung chung URL /comments/{id} (khac method) → nhan dien form sua qua class edit-form
        $asCommenter = $this->actingAs($this->commenter)->get(route('videos.show', $this->video))->getContent();
        $this->assertStringContainsString('class="reveal edit-form"', $asCommenter);
        $this->assertStringContainsString(route('comments.destroy', $this->comment), $asCommenter);

        $asOwner = $this->actingAs($this->owner)->get(route('videos.show', $this->video))->getContent();
        $this->assertStringNotContainsString('class="reveal edit-form"', $asOwner);
        $this->assertStringContainsString(route('comments.destroy', $this->comment), $asOwner);
        $this->assertStringContainsString(route('comments.react', $this->comment), $asOwner);
    }

    // Tach rieng vi actingAs() giu nguyen qua cac request trong cung mot test
    public function test_guest_sees_no_comment_action_forms(): void
    {
        $asGuest = $this->get(route('videos.show', $this->video))->getContent();

        $this->assertStringNotContainsString(route('comments.react', $this->comment), $asGuest);
        $this->assertStringNotContainsString('class="reveal edit-form"', $asGuest);
        $this->assertStringNotContainsString(route('comments.destroy', $this->comment), $asGuest);
    }
}
