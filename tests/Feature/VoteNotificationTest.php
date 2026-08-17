<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contest;
use App\Models\ContestEntry;
use App\Models\User;
use App\Models\Video;
use App\Notifications\NewVote;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

// FR7: chu bai du thi nhan thong bao khi co nguoi binh chon.
// Dung DatabaseTransactions (khong migrate) de chay duoc tren DB dev MySQL.
class VoteNotificationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_entry_owner_notified_when_voted(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $voter = User::factory()->create();

        $slug = 'test-'.uniqid();
        $category = Category::create(['name' => 'Thể loại '.$slug, 'slug' => $slug]);
        $video = Video::create([
            'user_id' => $owner->id, 'category_id' => $category->id, 'title' => 'Bài dự thi',
            'privacy' => 'public', 'allow_comments' => true, 'status' => 'approved',
            'file_path' => 'videos/test.mp4', 'mime_type' => 'video/mp4',
        ]);

        // Cuoc thi dang o giai doan binh chon
        $contest = Contest::create([
            'title' => 'Cuộc thi test', 'description' => null,
            'start_at' => now()->subDays(10), 'submission_deadline' => now()->subDay(), 'end_at' => now()->addDays(5),
        ]);
        $entry = ContestEntry::create(['contest_id' => $contest->id, 'video_id' => $video->id, 'user_id' => $owner->id]);

        $this->actingAs($voter)
            ->post(route('entries.vote', $entry))
            ->assertSessionHas('success');

        $this->assertSame(1, $entry->fresh()->votes_count);
        Notification::assertSentTo($owner, NewVote::class, function ($n) use ($voter, $entry) {
            $data = $n->toDatabase($entry->user);

            return $n->voter->is($voter)
                && $data['kind'] === 'vote'
                && str_contains($data['message'], 'Bài dự thi')
                && str_contains($data['message'], 'Cuộc thi test')
                && $data['url'] === '/contests/'.$entry->contest_id;
        });
    }
}
