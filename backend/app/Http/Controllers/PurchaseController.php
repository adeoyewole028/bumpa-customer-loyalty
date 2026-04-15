<?php

namespace App\Http\Controllers;

use App\Events\PurchaseMade;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    /**
     * POST /api/users/{user}/purchases
     * Simulate a purchase for a user.
     */
    public function store(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate(['amount' => 'required|numeric|min:0']);

        $purchase = Purchase::create([
            'user_id' => $user->id,
            'amount'  => $validated['amount'],
        ]);

        event(new PurchaseMade($user));

        return response()->json(['message' => 'Purchase recorded.', 'purchase' => $purchase], 201);
    }
}
