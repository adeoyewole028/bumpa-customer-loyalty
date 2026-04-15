<?php

namespace Database\Seeders;

use App\Models\Purchase;
use App\Models\User;
use App\Services\AchievementService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;

class DemoSeeder extends Seeder
{
    public function run(AchievementService $achievementService): void
    {
        Event::fake(); // Suppress event noise during seeding

        // User 1: Beginner (no purchases)
        User::factory()->create(['name' => 'Alice Beginner', 'email' => 'alice@example.com']);

        // User 2: First Purchase only
        $bob = User::factory()->create(['name' => 'Bob Bronze', 'email' => 'bob@example.com']);
        Purchase::factory()->create(['user_id' => $bob->id]);
        $achievementService->checkAndUnlock($bob);

        // User 3: 5 purchases = Bronze badge
        $carol = User::factory()->create(['name' => 'Carol Silver', 'email' => 'carol@example.com']);
        Purchase::factory()->count(5)->create(['user_id' => $carol->id]);
        $achievementService->checkAndUnlock($carol);

        // User 4: 10 purchases = Silver badge
        $dave = User::factory()->create(['name' => 'Dave Gold', 'email' => 'dave@example.com']);
        Purchase::factory()->count(50)->create(['user_id' => $dave->id]);
        $achievementService->checkAndUnlock($dave);
    }
}
