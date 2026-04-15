<?php

namespace App\Listeners;

use App\Events\BadgeUnlocked;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ProcessCashbackOnBadge implements ShouldQueue
{
    public function handle(BadgeUnlocked $event): void
    {
        // Mock payment provider: log a 300 Naira cashback payment
        Log::info('Cashback payment triggered', [
            'user_id'    => $event->user->id,
            'user_name'  => $event->user->name,
            'badge'      => $event->badgeName,
            'amount_ngn' => 300,
            'message'    => "300 Naira cashback issued to {$event->user->name} for unlocking {$event->badgeName} badge.",
        ]);
    }
}
