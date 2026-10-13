<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = Supplier::factory(50)->create();

        $products = Product::factory(50)->create([
            'supplier_id' => fn () => $suppliers->random()->id,
        ]);

        Sale::factory(50)->create()->each(function (Sale $sale) use ($products) {
            $product = $products->random();
            $quantity = rand(1, 5);

            $sale->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'line_total' => number_format($quantity * (float) $product->price, 2, '.', ''),
            ]);

            $sale->recomputeTotal();
        });

        StockMovement::factory(50)->create([
            'product_id' => fn () => $products->random()->id,
        ]);
    }
}
