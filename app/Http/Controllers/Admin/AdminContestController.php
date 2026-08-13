<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminContestController extends Controller
{
    public function index(): View
    {
        return view('admin.contests.index', [
            'contests' => Contest::withCount('entries')->orderByDesc('start_at')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.contests.form', ['contest' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        Contest::create($this->validated($request));

        return redirect()->route('admin.contests.index')->with('success', 'Đã tạo cuộc thi.');
    }

    public function edit(Contest $contest): View
    {
        return view('admin.contests.form', compact('contest'));
    }

    public function update(Request $request, Contest $contest): RedirectResponse
    {
        $contest->update($this->validated($request));

        return redirect()->route('admin.contests.index')->with('success', 'Đã cập nhật cuộc thi.');
    }

    public function destroy(Contest $contest): RedirectResponse
    {
        $contest->delete(); // cascade xoa entries + votes

        return back()->with('success', 'Đã xóa cuộc thi "'.$contest->title.'".');
    }

    // 3 moc thoi gian phai theo thu tu: start < submission_deadline < end (FR7)
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'start_at' => ['required', 'date'],
            'submission_deadline' => ['required', 'date', 'after:start_at'],
            'end_at' => ['required', 'date', 'after:submission_deadline'],
        ], [
            'title.required' => 'Vui lòng nhập tên cuộc thi.',
            'start_at.required' => 'Chọn thời điểm mở nộp bài.',
            'submission_deadline.after' => 'Hạn nộp bài phải sau thời điểm bắt đầu.',
            'end_at.after' => 'Thời điểm kết thúc phải sau hạn nộp bài.',
        ]);
    }
}
