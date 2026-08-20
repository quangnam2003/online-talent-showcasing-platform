<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use App\Notifications\GroupJoined;
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

    // FR5: trang nhom — bang thao luan chi thanh vien thay (admin xem duoc de kiem duyet)
    public function show(Request $request, Group $group): View
    {
        $me = auth()->user();
        $isMember = $group->hasMember($me);
        $canManage = $this->canManage($group);

        return view('groups.index', [
            'groups' => $this->groupList($request),
            'activeGroup' => $group->load('owner')->loadCount('members'),
            'isMember' => $isMember,
            'canManage' => $canManage,
            'members' => $group->members()->orderByPivot('created_at')->take(60)->get(),
            'posts' => ($isMember || $canManage)
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
            'name' => ['required', 'string', 'max:100', 'unique:groups,name'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Vui lòng đặt tên nhóm.',
            'name.unique' => 'Đã có nhóm mang tên này — hãy chọn tên khác.',
        ]);

        $group = Group::create([...$data, 'owner_id' => auth()->id()]);
        $group->members()->attach(auth()->id()); // nguoi tao tu dong la thanh vien

        return redirect()->route('groups.show', $group)
            ->with('success', 'Đã tạo nhóm "'.$group->name.'".');
    }

    // FR5: chu nhom (hoac admin) sua ten / mo ta nhom
    public function update(Request $request, Group $group): RedirectResponse
    {
        abort_unless($this->canManage($group), 403, 'Chỉ chủ nhóm mới được sửa nhóm.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:groups,name,'.$group->id],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Vui lòng đặt tên nhóm.',
            'name.unique' => 'Đã có nhóm mang tên này — hãy chọn tên khác.',
        ]);

        $group->update($data);

        return redirect()->route('groups.show', $group)->with('success', 'Đã cập nhật nhóm.');
    }

    // FR5: xoa nhom — thanh vien va bai dang xoa theo (FK cascade)
    public function destroy(Group $group): RedirectResponse
    {
        abort_unless($this->canManage($group), 403, 'Chỉ chủ nhóm mới được xóa nhóm.');

        $name = $group->name;
        $group->delete();

        return redirect()->route('groups.index')->with('success', 'Đã xóa nhóm "'.$name.'".');
    }

    public function join(Group $group): RedirectResponse
    {
        $result = $group->members()->syncWithoutDetaching([auth()->id()]);

        // Chi bao chu nhom khi thuc su co thanh vien MOI (bam join lai khong bao trung)
        if (! empty($result['attached']) && $group->owner_id !== auth()->id()) {
            $group->owner->notify(new GroupJoined(auth()->user(), $group));
        }

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

    // FR5 "Manage group members → Remove member": chu nhom (hoac admin) xoa thanh vien
    public function removeMember(Group $group, User $user): RedirectResponse
    {
        abort_unless($this->canManage($group), 403, 'Chỉ chủ nhóm mới được xóa thành viên.');

        if ($user->id === $group->owner_id) {
            return back()->with('error', 'Không thể xóa chủ nhóm khỏi nhóm.');
        }

        $group->members()->detach($user->id);

        return back()->with('success', 'Đã xóa '.$user->name.' khỏi nhóm.');
    }

    // Chu nhom hoac admin (kiem duyet noi dung vi pham)
    private function canManage(Group $group): bool
    {
        $me = auth()->user();

        return $me !== null && ($me->id === $group->owner_id || $me->isAdmin());
    }

    private function groupList(Request $request)
    {
        $me = auth()->user();

        return Group::withCount('members')
            // danh dau nhom minh da tham gia (cham ✓ o cot trai)
            ->when($me, fn ($query) => $query->withExists([
                'members as is_member' => fn ($m) => $m->whereKey($me->id),
            ]))
            // addcslashes: %/_ trong tu khoa la ky tu literal, khong phai wildcard cua LIKE
            ->when(trim((string) $request->query('q')), fn ($query, $q) => $query
                ->where('name', 'like', '%'.addcslashes($q, '%_\\').'%'))
            ->orderByDesc('members_count')
            ->get();
    }
}
