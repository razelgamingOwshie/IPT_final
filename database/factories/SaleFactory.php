<?php

namespace Database\Factories;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_name' => fake()->optional()->name(),
            'total' => fake()->randomFloat(2, 1, 500),
            'payment_method' => fake()->randomElement(Sale::PAYMENT_METHODS),
            'sale_date' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
