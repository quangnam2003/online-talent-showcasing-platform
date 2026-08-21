<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\ContestEntry;
use App\Notifications\EntryDisqualified;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContestEntryController extends Controller
{
    // FR7: nop bai du thi — 1 bai / creator / cuoc thi, video phai da duyet
    public function store(Request $request, Contest $contest): RedirectResponse
    {
        $me = auth()->user();

        abort_unless($me->isCreator(), 403, 'Chỉ creator mới được nộp bài dự thi.');

        if (! $contest->isAcceptingSubmissions()) {
            return back()->with('error', 'Cuộc thi không trong giai đoạn nhận bài.');
        }

        if ($contest->entries()->where('user_id', $me->id)->exists()) {
            return back()->with('error', 'Bạn đã nộp bài cho cuộc thi này rồi.');
        }

        $data = $request->validate(['video_id' => ['required', 'exists:videos,id']]);

        $video = $me->videos()->visible()->find($data['video_id']);
        if (! $video) {
            return back()->with('error', 'Video phải là của bạn, đã được duyệt và ở chế độ công khai.');
        }

        if ($contest->entries()->where('video_id', $video->id)->exists()) {
            return back()->with('error', 'Video này đã được nộp cho cuộc thi.');
        }

        // Unique (contest_id, user_id) o DB la chot chan cuoi: 2 request song song cung
        // qua duoc exists() o tren thi chi 1 ban ghi duoc tao, ban con lai vao catch
        try {
            ContestEntry::create([
                'contest_id' => $contest->id,
                'video_id' => $video->id,
                'user_id' => $me->id,
            ]);
        } catch (UniqueConstraintViolationException) {
            return back()->with('error', 'Bạn đã nộp bài cho cuộc thi này rồi.');
        }

        return back()->with('success', 'Đã nộp "'.$video->title.'" dự thi "'.$contest->title.'".');
    }

    // FR7 "Withdraw entry": creator rut bai cua minh (truoc khi ket qua duoc chot)
    public function withdraw(Contest $contest): RedirectResponse
    {
        $entry = $contest->entries()->where('user_id', auth()->id())->first();

        if (! $entry) {
            return back()->with('error', 'Bạn chưa nộp bài cho cuộc thi này.');
        }
        if ($contest->status === 'ended') {
            return back()->with('error', 'Cuộc thi đã kết thúc — không thể rút bài khi kết quả đã chốt.');
        }

        $entry->delete(); // phieu da nhan bi xoa theo (cascade) — nguoi vote duoc tra lai luot

        return back()->with('success', 'Đã rút bài khỏi cuộc thi "'.$contest->title.'". Phiếu đã nhận bị hủy.');
    }

    // FR7 "Disqualify entry": ban to chuc loai bai vi pham the le (+ bao chu bai)
    public function disqualify(ContestEntry $entry): RedirectResponse
    {
        $entry->load(['contest', 'video', 'user']);

        if ($entry->contest->status === 'ended') {
            return back()->with('error', 'Cuộc thi đã kết thúc — kết quả đã chốt, không thể loại bài.');
        }

        $entry->delete();
        $entry->user->notify(new EntryDisqualified($entry->video, $entry->contest));

        return back()->with('success', 'Đã loại bài "'.$entry->video->title.'" khỏi cuộc thi và thông báo cho chủ bài.');
    }
}
