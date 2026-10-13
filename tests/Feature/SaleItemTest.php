<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SaleItemTest extends TestCase
{
    use RefreshDatabase;

    private function productData(array $overrides = []): array
    {
        return array_merge([
            'sku' => 'PROD-'.uniqid(),
            'name' => 'Test Apples',
            'category' => 'Produce',
            'unit' => 'kg',
            'price' => '3.00',
            'stock' => 20,
            'reorder_level' => 5,
        ], $overrides);
    }

    private function makeSale(): Sale
    {
        return Sale::create([
            'customer_name' => 'Walk-in',
            'total' => '0.00',
            'payment_method' => 'cash',
            'sale_date' => now(),
        ]);
    }

    public function test_sale_items_index_and_create_pages_load(): void
    {
        $this->get(route('sale-items.index'))->assertOk()->assertSee('Sale Items');
        $this->get(route('sale-items.create'))->assertOk()->assertSee('Add Sale Line Item');
    }

    public function test_sale_item_can_be_created_and_sale_total_recalculated(): void
    {
        $sale = $this->makeSale();
        $product = Product::create($this->productData());

        $this->post(route('sale-items.store'), [
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 3,
            'unit_price' => '4.50',
        ])->assertRedirect(route('sale-items.show', SaleItem::first()));

        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 3,
            'unit_price' => '4.50',
            'line_total' => '13.50',
        ]);

        $this->assertSame('13.50', (string) $sale->fresh()->total);
        $this->assertSame(17, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'out',
            'quantity' => 3,
        ]);
    }

    public function test_sale_item_requires_valid_sale_quantity_and_price(): void
    {
        $this->from(route('sale-items.create'))
            ->post(route('sale-items.store'), [
                'sale_id' => 999,
                'product_id' => 999,
                'quantity' => 0,
                'unit_price' => '',
            ])
            ->assertRedirect(route('sale-items.create'))
            ->assertSessionHasErrors(['sale_id', 'quantity', 'unit_price']);

        $this->assertSame(0, SaleItem::count());
    }

    public function test_sale_item_cannot_consume_more_stock_than_available(): void
    {
        $sale = $this->makeSale();
        $product = Product::create($this->productData(['stock' => 2]));

        $this->from(route('sale-items.create'))
            ->post(route('sale-items.store'), [
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'quantity' => 3,
                'unit_price' => '2.00',
            ])
            ->assertRedirect(route('sale-items.create'))
            ->assertSessionHasErrors('quantity');

        $this->assertSame(2, $product->fresh()->stock);
        $this->assertSame(0, SaleItem::count());
        $this->assertSame(0, $sale->fresh()->items()->count());
    }

    public function test_sale_item_line_total_cannot_exceed_the_database_decimal_limit(): void
    {
        $sale = $this->makeSale();
        $product = Product::create($this->productData());

        $this->from(route('sale-items.create'))
            ->post(route('sale-items.store'), [
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => '999999.99',
            ])
            ->assertRedirect(route('sale-items.create'))
            ->assertSessionHasErrors('quantity');

        $this->assertSame(20, $product->fresh()->stock);
        $this->assertSame(0, SaleItem::count());
        $this->assertSame(0, StockMovement::count());
    }

    public function test_sale_item_cannot_push_sale_total_above_the_database_decimal_limit(): void
    {
        $sale = $this->makeSale();
        SaleItem::factory()->for($sale)->create([
            'product_id' => null,
            'product_name' => 'Existing item',
            'quantity' => 1,
            'unit_price' => '600000.00',
            'line_total' => '600000.00',
        ]);
        $sale->recomputeTotal();

        $this->from(route('sale-items.create'))
            ->post(route('sale-items.store'), [
                'sale_id' => $sale->id,
                'product_name' => 'Manual item',
                'quantity' => 1,
                'unit_price' => '500000.00',
            ])
            ->assertRedirect(route('sale-items.create'))
            ->assertSessionHasErrors('total');

        $this->assertSame(1, $sale->items()->count());
        $this->assertSame('600000.00', (string) $sale->fresh()->total);
    }

    public function test_sale_item_can_be_moved_to_another_sale_and_both_totals_are_recalculated(): void
    {
        $sale = $this->makeSale();
        $otherSale = $this->makeSale();
        $product = Product::create($this->productData(['stock' => 18]));
        $item = SaleItem::factory()->for($sale)->for($product)->create([
            'quantity' => 2,
            'unit_price' => '5.00',
            'line_total' => '10.00',
        ]);
        $sale->recomputeTotal();

        $this->assertSame('10.00', (string) $sale->fresh()->total);

        $this->put(route('sale-items.update', $item), [
            'sale_id' => $otherSale->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 4,
            'unit_price' => '5.00',
        ])->assertRedirect(route('sale-items.show', $item));

        $this->assertDatabaseHas('sale_items', [
            'id' => $item->id,
            'sale_id' => $otherSale->id,
            'quantity' => 4,
            'line_total' => '20.00',
        ]);

        $this->assertSame('0.00', (string) $sale->fresh()->total);
        $this->assertSame('20.00', (string) $otherSale->fresh()->total);
        $this->assertSame(16, $product->fresh()->stock);
    }

    public function test_sale_item_requires_a_product_or_manual_description(): void
    {
        $sale = $this->makeSale();

        $this->from(route('sale-items.create'))
            ->post(route('sale-items.store'), [
                'sale_id' => $sale->id,
                'quantity' => 1,
                'unit_price' => '2.00',
            ])
            ->assertRedirect(route('sale-items.create'))
            ->assertSessionHasErrors('product_name');

        $this->assertSame(0, SaleItem::count());
    }

    public function test_sale_item_show_displays_details(): void
    {
        $sale = $this->makeSale();
        $product = Product::create($this->productData());
        $item = SaleItem::factory()->for($sale)->for($product)->create([
            'product_name' => $product->name,
            'quantity' => 5,
            'unit_price' => '2.50',
            'line_total' => '12.50',
        ]);

        $this->get(route('sale-items.show', $item))
            ->assertOk()
            ->assertSee('Test Apples')
            ->assertSee('$12.50');
    }

    public function test_sale_item_can_be_deleted_and_total_recalculated(): void
    {
        $sale = $this->makeSale();
        $product = Product::create($this->productData(['stock' => 18]));
        $item = SaleItem::factory()->for($sale)->for($product)->create([
            'quantity' => 2,
            'unit_price' => '5.00',
            'line_total' => '10.00',
        ]);
        $sale->recomputeTotal();

        $this->assertSame('10.00', (string) $sale->fresh()->total);

        $this->delete(route('sale-items.destroy', $item))
            ->assertRedirect(route('sale-items.index'));

        $this->assertDatabaseMissing('sale_items', ['id' => $item->id]);
        $this->assertSame('0.00', (string) $sale->fresh()->total);
        $this->assertSame(20, $product->fresh()->stock);
    }
}
