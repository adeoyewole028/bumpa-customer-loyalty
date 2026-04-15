<?php

namespace App\Services;

use App\Events\AchievementUnlocked;
use App\Events\BadgeUnlocked;
use App\Models\User;
use App\Models\UserAchievement;

class AchievementService
{
    /**
     * All achievements ordered by purchase threshold.
     * [name => purchase_count_required]
     */
    public static function achievements(): array
    {
        return [
            'First Purchase' => 1,
            '5 Purchases'    => 5,
            '10 Purchases'   => 10,
            '25 Purchases'   => 25,
            '50 Purchases'   => 50,
        ];
    }

    /**
     * All badges ordered by minimum achievements required.
     * [name => achievements_count_required]
     */
    public static function badges(): array
    {
        return [
            'Beginner' => 0,
            'Bronze'   => 2,
            'Silver'   => 4,
            'Gold'     => 5,
        ];
    }

    /**
     * Check and unlock any new achievements/badges after a purchase.
     */
    public function checkAndUnlock(User $user): void
    {
        $purchaseCount  = $user->purchases()->count();
        $unlockedBefore = $user->userAchievements()->pluck('achievement_name')->toArray();

        // Record badge the user held BEFORE any new achievements
        $previousBadge = $this->badgeForCount(count($unlockedBefore));

        // Unlock new achievements
        foreach (self::achievements() as $name => $required) {
            if ($purchaseCount >= $required && ! in_array($name, $unlockedBefore)) {
                UserAchievement::create([
                    'user_id'          => $user->id,
                    'achievement_name' => $name,
                ]);

                event(new AchievementUnlocked($name, $user));
            }
        }

        // Determine new badge after all achievements are unlocked
        $newUnlockedCount = $user->userAchievements()->count();
        $newBadge         = $this->badgeForCount($newUnlockedCount);

        if ($newBadge !== $previousBadge) {
            event(new BadgeUnlocked($newBadge, $user));
        }
    }

    /**
     * The badge name for a given achievement count.
     */
    public function badgeForCount(int $count): string
    {
        $badges = self::badges();
        arsort($badges); // highest first

        foreach ($badges as $name => $required) {
            if ($count >= $required) {
                return $name;
            }
        }

        return 'Beginner';
    }

    /**
     * The badge the user currently holds.
     */
    public function currentBadgeForUser(User $user): string
    {
        return $this->badgeForCount($user->userAchievements()->count());
    }

    /**
     * Build the achievements API response payload.
     */
    public function achievementsPayload(User $user): array
    {
        $purchaseCount   = $user->purchases()->count();
        $unlocked        = $user->userAchievements()->pluck('achievement_name')->toArray();
        $allAchievements = self::achievements();

        // Next available: immediately next achievement not yet unlocked
        $nextAvailable = [];
        foreach ($allAchievements as $name => $required) {
            if (! in_array($name, $unlocked)) {
                $nextAvailable[] = $name;
                break;
            }
        }

        $badges       = self::badges();
        $badgeNames   = array_keys($badges);
        $currentBadge = $this->currentBadgeForUser($user);
        $currentIdx   = array_search($currentBadge, $badgeNames);
        $nextBadge    = $badgeNames[$currentIdx + 1] ?? null;

        $remainingToNextBadge = 0;
        if ($nextBadge !== null) {
            $remainingToNextBadge = max(0, $badges[$nextBadge] - count($unlocked));
        }

        return [
            'unlocked_achievements'          => $unlocked,
            'next_available_achievements'    => $nextAvailable,
            'current_badge'                  => $currentBadge,
            'next_badge'                     => $nextBadge,
            'remaining_to_unlock_next_badge' => $remainingToNextBadge,
        ];
    }
}
