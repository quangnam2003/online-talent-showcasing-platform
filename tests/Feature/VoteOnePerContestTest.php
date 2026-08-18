<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contest;
use App\Models\ContestEntry;
use App\Models\User;
use App\Models\Video;
use App\Models\Vote;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

// FR7: 1 phieu / user / cuoc thi — chan o tang ung dung VA o DB (unique user_id+contest_id)
// de 2 request song song cung khong lot 2 phieu. Dung DatabaseTransactions (khong migrate).
class VoteOnePerContestTest extends TestCase
{
    use DatabaseTransactions;

    /** @return array{0: Contest, 1: ContestEntry, 2: ContestEntry} */
    private function makeContestWithTwoEntries(): array
    {
        $slug = 'test-'.uniqid();
        $category = Category::create(['name' => 'Thể loại '.$slug, 'slug' => $slug]);
        $contest = Contest::create([
            'title' => 'Cuộc thi test '.$slug, 'description' => null,
            'start_at' => now()->subDays(10), 'submission_deadline' => now()->subDay(), 'end_at' => now()->addDays(5),
        ]);

        $entries = [];
        foreach ([1, 2] as $i) {
            $owner = User::factory()->create();
            $video = Video::create([
                'user_id' => $owner->id, 'category_id' => $category->id, 'title' => 'Bài dự thi '.$i,
                'privacy' => 'public', 'allow_comments' => true, 'status' => 'approved',
                'file_path' => 'videos/test.mp4', 'mime_type' => 'video/mp4',
            ]);
            $entries[] = ContestEntry::create(['contest_id' => $contest->id, 'video_id' => $video->id, 'user_id' => $owner->id]);
        }

        return [$contest, $entries[0], $entries[1]];
    }

    public function test_second_vote_in_same_contest_is_rejected(): void
    {
        Notification::fake();
        [$contest, $a, $b] = $this->makeContestWithTwoEntries();
        $voter = User::factory()->create();

        $this->actingAs($voter)->post(route('entries.vote', $a))->assertSessionHas('success');
        $this->actingAs($voter)->post(route('entries.vote', $b))->assertSessionHas('error');

        $this->assertSame(1, Vote::where('user_id', $voter->id)->where('contest_id', $contest->id)->count());
        $this->assertSame(1, $a->fresh()->votes_count);
        $this->assertSame(0, $b->fresh()->votes_count);
    }

    public function test_database_rejects_duplicate_vote_per_contest(): void
    {
        [$contest, $a, $b] = $this->makeContestWithTwoEntries();
        $voter = User::factory()->create();

        Vote::create(['user_id' => $voter->id, 'entry_id' => $a->id, 'contest_id' => $contest->id]);

        // Gia lap request song song "lot" qua exists(): insert thang vao DB → unique phai chan
        $this->expectException(UniqueConstraintViolationException::class);
        Vote::create(['user_id' => $voter->id, 'entry_id' => $b->id, 'contest_id' => $contest->id]);
    }
}
