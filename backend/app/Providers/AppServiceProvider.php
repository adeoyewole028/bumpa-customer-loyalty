<?php

namespace App\Providers;

use App\Events\AchievementUnlocked;
use App\Events\BadgeUnlocked;
use App\Events\PurchaseMade;
use App\Listeners\CheckAchievementsOnPurchase;
use App\Listeners\ProcessCashbackOnBadge;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(PurchaseMade::class, CheckAchievementsOnPurchase::class);
        Event::listen(BadgeUnlocked::class, ProcessCashbackOnBadge::class);
    }
}
