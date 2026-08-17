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

    // FR6: chi nhan tin giua hai vai tro DOI DIEN creator <-> mentor
    // (chan creator-creator, mentor-mentor, admin va tu nhan tin chinh minh)
    private function assertMessageable(User $user): void
    {
        abort_if($user->id === auth()->id(), 404);
        abort_unless($this->isMessageablePair(auth()->user(), $user), 404);
    }

    private function isMessageablePair(User $a, User $b): bool
    {
        return ($a->role === 'creator' && $b->role === 'mentor')
            || ($a->role === 'mentor' && $b->role === 'creator');
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
            // Loai hoi thoai sai dac ta FR6 (du lieu cu giua hai nguoi cung vai tro) —
            // neu hien se dan toi trang 404 vi assertMessageable da chan
            ->filter(fn ($t) => $t->partner !== null && $this->isMessageablePair($me, $t->partner))
            ->sortByDesc(fn ($t) => $t->last->created_at)
            ->values();
    }

    // Nguoi co the bat dau hoi thoai moi — chi vai tro doi dien (FR6)
    private function contacts()
    {
        $me = auth()->user();

        if (! in_array($me->role, ['creator', 'mentor'], true)) {
            return collect(); // admin khong nhan tin
        }

        return User::where('role', $me->role === 'creator' ? 'mentor' : 'creator')
            ->orderBy('name')
            ->get();
    }
}
