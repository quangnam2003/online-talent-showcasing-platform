<?php

namespace App\Console\Commands;

use App\Models\Contest;
use App\Notifications\ContestResult;
use Illuminate\Console\Command;

/**
 * FR7 — actor "Scheduler" cua usecase "Announce winner".
 * Chay dinh ky (moi gio, khai bao trong routes/console.php): tim cuoc thi da qua
 * end_at nhung chua cong bo (announced_at null) → gui thong bao cho nguoi thang
 * va moi thi sinh, roi dong dau announced_at de khong gui lai.
 */
class AnnounceContests extends Command
{
    protected $signature = 'contests:announce';

    protected $description = 'Công bố kết quả các cuộc thi vừa kết thúc (thông báo người thắng + thí sinh)';

    public function handle(): int
    {
        $due = Contest::whereNull('announced_at')->where('end_at', '<=', now())->get();

        foreach ($due as $contest) {
            $winnerEntryIds = $contest->winners()->pluck('id');

            $entries = $contest->entries()->with('user')->get();
            foreach ($entries as $entry) {
                if (! $entry->user || ! $entry->user->is_active) {
                    continue; // tai khoan bi khoa: khong gui
                }
                $entry->user->notify(new ContestResult(
                    $contest,
                    isWinner: $winnerEntryIds->contains($entry->id),
                    votes: (int) $entry->votes_count,
                ));
            }

            $contest->forceFill(['announced_at' => now()])->save();

            $this->info(sprintf(
                '"%s": %d thí sinh, %s.',
                $contest->title,
                $entries->count(),
                $winnerEntryIds->isEmpty() ? 'không có quán quân (0 phiếu)' : $winnerEntryIds->count().' quán quân'
            ));
        }

        if ($due->isEmpty()) {
            $this->info('Không có cuộc thi nào chờ công bố.');
        }

        return self::SUCCESS;
    }
}
