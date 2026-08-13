<?php

namespace App\Http\Controllers;

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
}
