<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sku' => fake()->unique()->bothify('SKU-#####'),
            'name' => fake()->unique()->words(2, true),
            'category' => fake()->randomElement(['Produce', 'Dairy', 'Bakery', 'Pantry', 'Drinks']),
            'unit' => fake()->randomElement(['each', 'kg', '1 L', '500 g']),
            'price' => fake()->randomFloat(2, 0.5, 99.99),
            'stock' => fake()->numberBetween(0, 100),
            'reorder_level' => fake()->numberBetween(5, 20),
            'supplier_id' => null,
        ];
    }

    public function withSupplier(): static
    {
        return $this->state(fn () => ['supplier_id' => Supplier::factory()]);
    }
}
