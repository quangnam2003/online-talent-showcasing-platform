<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.users', [
            'users' => User::withCount('videos')
                ->when(trim((string) $request->query('q')), fn ($query, $q) => $query->where(
                    fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")
                ))
                ->orderBy('id')
                ->paginate(15)
                ->withQueryString(),
            'q' => $request->query('q'),
        ]);
    }

    // Khoa / mo tai khoan (is_active) — tai khoan bi khoa khong dang nhap duoc
    public function toggleActive(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Bạn không thể tự khóa tài khoản của mình.');
        }

        // forceFill: is_active la cot he thong quan ly, khong nam trong $fillable
        $user->forceFill(['is_active' => ! $user->is_active])->save();

        return back()->with('success', ($user->is_active ? 'Đã mở khóa ' : 'Đã khóa ').$user->name.'.');
    }
}
