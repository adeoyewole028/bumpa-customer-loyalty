<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AchievementService;
use Illuminate\Http\JsonResponse;

class UserAchievementController extends Controller
{
    public function __construct(private AchievementService $achievementService)
    {
    }

    /**
     * GET /api/users/{user}/achievements
     */
    public function show(User $user): JsonResponse
    {
        return response()->json($this->achievementService->achievementsPayload($user));
    }
}
