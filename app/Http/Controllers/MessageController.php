<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    // FR6: hop thu — danh sach hoi thoai
    public function index(): View
    {
        return view('messages.index', [
            'threads' => $this->threads(),
            'contacts' => $this->contacts(),
            'activeUser' => null,
            'messages' => collect(),
        ]);
    }

    // FR6: hoi thoai 1-1
    public function show(User $user): View
    {
        $me = auth()->user();
        $this->assertMessageable($user);

        // Danh dau da doc nhung tin nguoi kia gui minh
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $me->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('messages.index', [
            'threads' => $this->threads(),
            'contacts' => $this->contacts(),
            'activeUser' => $user,
            'messages' => Message::between($me, $user)->orderBy('created_at')->get(),
        ]);
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        $this->assertMessageable($user);

        $data = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ], [
            'content.required' => 'Vui lòng nhập tin nhắn.',
        ]);

        Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $user->id,
            'content' => $data['content'],
        ]);

        return redirect()->route('messages.show', $user);
    }

    // Chi nhan tin giua creator <-> mentor (va khong tu nhan tin chinh minh)
    private function assertMessageable(User $user): void
    {
        abort_if($user->id === auth()->id(), 404);
        abort_unless(in_array($user->role, ['creator', 'mentor'], true), 404);
    }

    // Gom hoi thoai: moi doi tac 1 dong voi tin moi nhat + so tin chua doc
    private function threads()
    {
        $me = auth()->user();

        $all = Message::where('sender_id', $me->id)
            ->orWhere('receiver_id', $me->id)
            ->latest()
            ->get();

        return $all
            ->groupBy(fn (Message $m) => $m->sender_id === $me->id ? $m->receiver_id : $m->sender_id)
            ->map(function ($messages, $partnerId) use ($me) {
                return (object) [
                    'partner' => User::find($partnerId),
                    'last' => $messages->first(),
                    'unread' => $messages->where('receiver_id', $me->id)->whereNull('read_at')->count(),
                ];
            })
            ->filter(fn ($t) => $t->partner !== null)
            ->sortByDesc(fn ($t) => $t->last->created_at)
            ->values();
    }

    // Nguoi co the bat dau hoi thoai moi
    private function contacts()
    {
        return User::whereIn('role', ['creator', 'mentor'])
            ->whereKeyNot(auth()->id())
            ->orderBy('role')
            ->orderBy('name')
            ->get();
    }
}
