<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount'  => $this->faker->randomFloat(2, 100, 10000),
        ];
    }
}
