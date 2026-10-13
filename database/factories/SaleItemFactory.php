<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\SaleItem>
 */
class SaleItemFactory extends Factory
{
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 10);
        $unitPrice = fake()->randomFloat(2, 0.5, 50);

        return [
            'sale_id' => Sale::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->words(2, true),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => number_format($quantity * $unitPrice, 2, '.', ''),
        ];
    }
}
