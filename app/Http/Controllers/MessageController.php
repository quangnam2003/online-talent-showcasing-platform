<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    // FR6: hop thu — danh sach hoi thoai
    public function index(): View
    {
        $threads = $this->threads();

        return view('messages.index', [
            'threads' => $threads,
            'contacts' => $this->contacts($threads),
            'activeUser' => null,
            'messages' => collect(),
        ]);
    }

    // FR6: hoi thoai 1-1
    public function show(User $user): View
    {
        $me = auth()->user();
        $this->assertMessageable($user);

        // Danh dau da doc nhung tin nguoi kia gui minh + thong bao "tin nhan moi" tuong ung
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $me->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        $this->markMessageNotificationsRead($user);

        $threads = $this->threads();

        return view('messages.index', [
            'threads' => $threads,
            'contacts' => $this->contacts($threads),
            'activeUser' => $user,
            // Chi tai 50 tin gan nhat (dao lai cho dung thu tu thoi gian) — hoi thoai dai
            // khong keo ca lich su ve; tin cu hon van nam trong DB, khong hien o khung chat
            'messages' => Message::between($me, $user)->latest('id')->take(50)->get()->reverse()->values(),
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

        // Bao chuong CHI o tin dau cua mot "dot": neu nguoi nhan van con tin chua doc
        // tu minh thi ho da co thong bao roi — gui moi tin mot thong bao se spam chuong.
        $shouldNotify = ! Message::where('sender_id', auth()->id())
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->exists();

        Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $user->id,
            'content' => $data['content'],
        ]);

        if ($shouldNotify) {
            $user->notify(new NewMessage(auth()->user(), $data['content']));
        }

        return redirect()->route('messages.show', $user);
    }

    /**
     * FR6: polling nhe cho trang chat — JS goi vai giay/lan voi ?since=<id tin cuoi>.
     * Tra tin moi hon (ca 2 chieu, phong khi mo nhieu tab); vi nguoi dung DANG mo hoi
     * thoai nen tin nguoi kia gui duoc danh dau da doc luon (badge/chuong khong keu nua).
     */
    public function poll(Request $request, User $user): JsonResponse
    {
        $me = auth()->user();
        $this->assertMessageable($user);

        $sinceId = max(0, (int) $request->query('since', 0));

        $items = Message::between($me, $user)
            ->where('id', '>', $sinceId)
            ->orderBy('id')
            ->take(50)
            ->get();

        if ($items->isNotEmpty()) {
            Message::where('sender_id', $user->id)
                ->where('receiver_id', $me->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
            $this->markMessageNotificationsRead($user);
        }

        return response()->json([
            'items' => $items->map(fn (Message $m) => [
                'id' => $m->id,
                'mine' => $m->sender_id === $me->id,
                'content' => $m->content,
                'at' => $m->created_at->format('d/m H:i'),
            ])->values(),
        ]);
    }

    // Da doc hoi thoai voi $partner → cac thong bao "tin nhan moi" cua nguoi do coi nhu da doc
    private function markMessageNotificationsRead(User $partner): void
    {
        auth()->user()->unreadNotifications()
            ->where('type', NewMessage::class)
            ->get()
            ->filter(fn ($n) => ($n->data['url'] ?? '') === '/messages/'.$partner->id)
            ->each->markAsRead();
    }

    // FR6: chi nhan tin giua hai vai tro DOI DIEN creator <-> mentor
    // (chan creator-creator, mentor-mentor, admin va tu nhan tin chinh minh)
    private function assertMessageable(User $user): void
    {
        abort_if($user->id === auth()->id(), 404);
        abort_unless($user->is_active, 404); // nguoi bi khoa: khong hien, khong nhan tin
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

        // latest('id'): nhieu tin gui trong cung mot giay thi created_at bang nhau —
        // id tang don dieu nen preview "tin cuoi" luon dung tin moi nhat
        $all = Message::where('sender_id', $me->id)
            ->orWhere('receiver_id', $me->id)
            ->latest('id')
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
            // Loai hoi thoai sai dac ta FR6 (du lieu cu giua hai nguoi cung vai tro) va
            // hoi thoai voi tai khoan bi khoa — neu hien, bam vao se 404 (assertMessageable chan)
            ->filter(fn ($t) => $t->partner !== null && $t->partner->is_active && $this->isMessageablePair($me, $t->partner))
            ->sortByDesc(fn ($t) => $t->last->id)
            ->values();
    }

    // Nguoi co the BAT DAU hoi thoai moi — vai tro doi dien (FR6), dang hoat dong,
    // va chua co hoi thoai (nguoi da chat nam ben danh sach threads roi, khong lap lai)
    private function contacts($threads)
    {
        $me = auth()->user();

        if (! in_array($me->role, ['creator', 'mentor'], true)) {
            return collect(); // admin khong nhan tin
        }

        return User::where('role', $me->role === 'creator' ? 'mentor' : 'creator')
            ->where('is_active', true)
            ->whereNotIn('id', $threads->pluck('partner.id')->filter())
            ->orderBy('name')
            ->get();
    }
}
