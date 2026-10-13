<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\StockMovement>
 */
class StockMovementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'movement_type' => fake()->randomElement(['in', 'out']),
            'quantity' => fake()->numberBetween(1, 50),
            'reason' => fake()->sentence(),
            'recorded_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
