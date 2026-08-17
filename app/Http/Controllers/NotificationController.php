<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $me = auth()->user();
        $notifications = $me->notifications()->paginate(15);

        // Mo trang la coi nhu da doc het
        $me->unreadNotifications->markAsRead();

        return view('notifications.index', compact('notifications'));
    }

    // Danh dau MOT thong bao da doc (JS goi khi nguoi dung bam toast) → badge giam ngay
    public function read(Request $request): JsonResponse
    {
        $data = $request->validate(['id' => ['required', 'string', 'max:64']]);

        $me = auth()->user();
        $me->unreadNotifications()->whereKey($data['id'])->update(['read_at' => now()]);

        return response()->json(['ok' => true, 'unread' => $me->unreadNotifications()->count()]);
    }

    /**
     * Polling nhe cho thong bao "gan thoi gian thuc": JS trong layout goi moi vai giay.
     * Tra ve so chua doc + cac thong bao chua doc tao sau moc `since` (ISO 8601) de hien toast.
     */
    public function poll(Request $request): JsonResponse
    {
        $me = auth()->user();

        $since = null;
        if ($request->filled('since')) {
            try {
                $since = Carbon::parse($request->query('since'));
            } catch (\Throwable) {
                $since = null;
            }
        }

        // Lay moc "now" TRUOC khi truy van: thong bao tao trong luc dang xu ly se duoc
        // tra ve o lan poll sau (client dung `now` lam `since` ke tiep; trung id thi client bo qua)
        $now = now();

        $items = $me->unreadNotifications()
            ->when($since, fn ($q) => $q->where('created_at', '>=', $since))
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'message' => $n->data['message'] ?? '',
                'url' => url($n->data['url'] ?? '/notifications'),
                'kind' => $n->data['kind'] ?? '',
                'created_at' => $n->created_at->toIso8601String(),
            ])
            ->values();

        return response()->json([
            'unread' => $me->unreadNotifications()->count(),
            'items' => $items,
            'now' => $now->toIso8601String(),
        ]);
    }
}
