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
        return view('admin.contests.form', [
            'contest' => $contest->loadCount('entries'),
            'votesCount' => $contest->entries()->sum('votes_count'),
        ]);
    }

    public function update(Request $request, Contest $contest): RedirectResponse
    {
        // Ket qua da cong bo (da qua end_at) → khong sua nua, tranh "viet lai lich su"
        if ($contest->status === 'ended') {
            return back()->with('error', 'Cuộc thi đã kết thúc và kết quả đã công bố — không thể sửa mốc thời gian. Hãy tạo cuộc thi mới.');
        }

        $data = $this->validated($request);

        // Da co bai du thi: khong duoc doi "mo nop bai" ra SAU bai som nhat
        // (neu khong, cuoc thi thanh "Sap dien ra" trong khi van mang bai du thi)
        $earliestEntry = $contest->entries()->min('created_at');
        if ($earliestEntry && \Illuminate\Support\Carbon::parse($data['start_at'])->gt($earliestEntry)) {
            return back()->withInput()->with('error',
                'Không thể dời "mở nộp bài" ra sau '.\Illuminate\Support\Carbon::parse($earliestEntry)->format('d/m/Y H:i').' — cuộc thi đã có bài dự thi nộp từ thời điểm đó.');
        }

        $contest->update($data);

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
