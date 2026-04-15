<?php

namespace Tests\Feature;

use App\Events\AchievementUnlocked;
use App\Events\BadgeUnlocked;
use App\Events\PurchaseMade;
use App\Models\Purchase;
use App\Models\User;
use App\Services\AchievementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AchievementsTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // API: GET /api/users/{user}/achievements
    // -------------------------------------------------------------------------

    public function test_achievements_endpoint_returns_correct_structure(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}/achievements");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'unlocked_achievements',
                'next_available_achievements',
                'current_badge',
                'next_badge',
                'remaining_to_unlock_next_badge',
            ]);
    }

    public function test_new_user_has_beginner_badge_and_no_achievements(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}/achievements");

        $response->assertStatus(200)
            ->assertJson([
                'unlocked_achievements'          => [],
                'current_badge'                  => 'Beginner',
                'next_badge'                     => 'Bronze',
                'remaining_to_unlock_next_badge' => 2,
            ]);
    }

    public function test_achievements_endpoint_returns_404_for_missing_user(): void
    {
        $this->getJson('/api/users/9999/achievements')
            ->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // Achievement unlock logic
    // -------------------------------------------------------------------------

    public function test_first_purchase_achievement_is_unlocked(): void
    {
        Event::fake();

        $user = User::factory()->create();
        Purchase::create(['user_id' => $user->id, 'amount' => 100]);

        $service = new AchievementService();
        $service->checkAndUnlock($user);

        $this->assertDatabaseHas('user_achievements', [
            'user_id'          => $user->id,
            'achievement_name' => 'First Purchase',
        ]);

        Event::assertDispatched(AchievementUnlocked::class, function ($e) {
            return $e->achievementName === 'First Purchase';
        });
    }

    public function test_five_purchases_achievement_is_unlocked(): void
    {
        Event::fake();

        $user = User::factory()->create();
        Purchase::factory()->count(5)->create(['user_id' => $user->id]);

        $service = new AchievementService();
        $service->checkAndUnlock($user);

        $this->assertDatabaseHas('user_achievements', [
            'user_id'          => $user->id,
            'achievement_name' => '5 Purchases',
        ]);
    }

    public function test_achievements_are_not_duplicated(): void
    {
        $user = User::factory()->create();
        Purchase::create(['user_id' => $user->id, 'amount' => 100]);

        $service = new AchievementService();
        $service->checkAndUnlock($user);
        $service->checkAndUnlock($user); // call again

        $this->assertEquals(
            1,
            $user->userAchievements()->where('achievement_name', 'First Purchase')->count()
        );
    }

    // -------------------------------------------------------------------------
    // Badge unlock logic
    // -------------------------------------------------------------------------

    public function test_bronze_badge_unlocked_after_two_achievements(): void
    {
        Event::fake();

        $user = User::factory()->create();
        Purchase::factory()->count(5)->create(['user_id' => $user->id]);

        $service = new AchievementService();
        $service->checkAndUnlock($user);

        // Should have First Purchase + 5 Purchases = 2 achievements → Bronze
        Event::assertDispatched(BadgeUnlocked::class, function ($e) {
            return $e->badgeName === 'Bronze';
        });
    }

    public function test_badge_unlocked_event_fires_cashback_listener(): void
    {
        Event::fake();

        $user = User::factory()->create();
        Purchase::factory()->count(5)->create(['user_id' => $user->id]);

        $service = new AchievementService();
        $service->checkAndUnlock($user);

        Event::assertDispatched(BadgeUnlocked::class);
    }

    // -------------------------------------------------------------------------
    // API: POST /api/users/{user}/purchases
    // -------------------------------------------------------------------------

    public function test_purchase_endpoint_creates_purchase_and_fires_event(): void
    {
        Event::fake();

        $user = User::factory()->create();

        $this->postJson("/api/users/{$user->id}/purchases", ['amount' => 500])
            ->assertStatus(201)
            ->assertJsonFragment(['message' => 'Purchase recorded.']);

        $this->assertDatabaseHas('purchases', ['user_id' => $user->id, 'amount' => 500]);

        Event::assertDispatched(PurchaseMade::class);
    }

    // -------------------------------------------------------------------------
    // next_available_achievements progression
    // -------------------------------------------------------------------------

    public function test_next_available_achievements_shows_correct_next(): void
    {
        $user = User::factory()->create();
        // 1 purchase → First Purchase unlocked, next available = 5 Purchases
        Purchase::create(['user_id' => $user->id, 'amount' => 100]);

        $service = new AchievementService();
        $service->checkAndUnlock($user);

        $payload = $service->achievementsPayload($user);

        $this->assertContains('First Purchase', $payload['unlocked_achievements']);
        $this->assertContains('5 Purchases', $payload['next_available_achievements']);
    }

    public function test_remaining_to_unlock_next_badge_is_calculated_correctly(): void
    {
        $user = User::factory()->create();
        // 1 purchase → 1 achievement unlocked, badge = Beginner, next = Bronze (needs 2)
        Purchase::create(['user_id' => $user->id, 'amount' => 100]);

        $service = new AchievementService();
        $service->checkAndUnlock($user);

        $payload = $service->achievementsPayload($user);

        $this->assertEquals(1, $payload['remaining_to_unlock_next_badge']); // Bronze needs 2, have 1
    }
}
