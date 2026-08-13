<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroupController extends Controller
{
    // FR5: danh sach nhom (cot trai mockup); chua chon nhom nao
    public function index(Request $request): View
    {
        return view('groups.index', [
            'groups' => $this->groupList($request),
            'activeGroup' => null,
            'q' => $request->query('q'),
        ]);
    }

    // FR5: trang nhom — bang thao luan chi thanh vien thay
    public function show(Request $request, Group $group): View
    {
        $isMember = $group->hasMember(auth()->user());

        return view('groups.index', [
            'groups' => $this->groupList($request),
            'activeGroup' => $group->load('owner')->loadCount('members'),
            'isMember' => $isMember,
            'posts' => $isMember
                ? $group->posts()->with('user')->latest()->take(30)->get()
                : collect(),
            'q' => $request->query('q'),
        ]);
    }

    public function create(): View
    {
        return view('groups.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Vui lòng đặt tên nhóm.',
        ]);

        $group = Group::create([...$data, 'owner_id' => auth()->id()]);
        $group->members()->attach(auth()->id()); // nguoi tao tu dong la thanh vien

        return redirect()->route('groups.show', $group)
            ->with('success', 'Đã tạo nhóm "'.$group->name.'".');
    }

    public function join(Group $group): RedirectResponse
    {
        $group->members()->syncWithoutDetaching([auth()->id()]);

        return redirect()->route('groups.show', $group)
            ->with('success', 'Bạn đã tham gia nhóm "'.$group->name.'".');
    }

    public function leave(Group $group): RedirectResponse
    {
        if ($group->owner_id === auth()->id()) {
            return back()->with('error', 'Chủ nhóm không thể rời nhóm của mình.');
        }

        $group->members()->detach(auth()->id());

        return redirect()->route('groups.index')
            ->with('success', 'Đã rời nhóm "'.$group->name.'".');
    }

    private function groupList(Request $request)
    {
        return Group::withCount('members')
            ->when(trim((string) $request->query('q')), fn ($query, $q) => $query->where('name', 'like', "%{$q}%"))
            ->orderByDesc('members_count')
            ->get();
    }
}
