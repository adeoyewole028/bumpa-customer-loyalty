<?php

namespace App\Listeners;

use App\Events\PurchaseMade;
use App\Services\AchievementService;
use Illuminate\Contracts\Queue\ShouldQueue;

class CheckAchievementsOnPurchase implements ShouldQueue
{
    public function __construct(private AchievementService $achievementService)
    {
    }

    public function handle(PurchaseMade $event): void
    {
        $this->achievementService->checkAndUnlock($event->user);
    }
}
