<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\Group;
use App\Models\User;
use App\Models\Video;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                ['n' => User::count(), 'k' => 'Người dùng'],
                ['n' => Video::count(), 'k' => 'Video'],
                ['n' => Video::where('status', 'pending')->count(), 'k' => 'Chờ duyệt'],
                ['n' => Group::count(), 'k' => 'Nhóm'],
                ['n' => Contest::count(), 'k' => 'Cuộc thi'],
            ],
            'roleCounts' => User::selectRaw('role, count(*) as n')->groupBy('role')->pluck('n', 'role'),
            'statusCounts' => Video::selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status'),
            'pendingLatest' => Video::where('status', 'pending')->with(['user', 'category'])->latest()->take(5)->get(),
        ]);
    }
}
