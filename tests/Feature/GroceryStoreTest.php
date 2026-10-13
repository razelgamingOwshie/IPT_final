<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GroceryStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_and_inventory_pages_load(): void
    {
        Product::create($this->productData());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Grocery Store Management')
            ->assertSee('Products')
            ->assertSee('Suppliers')
            ->assertSee('Stock Movements')
            ->assertSee('Sale Items')
            ->assertSee('col-12 col-sm-6 col-lg');
        $this->get(route('products.index'))->assertOk()->assertSee('Apples');
    }

    public function test_paginated_tables_use_bootstrap_pagination_without_oversized_arrows(): void
    {
        Product::factory()->count(13)->create();
        Supplier::factory()->count(13)->create();

        foreach ([route('products.index'), route('suppliers.index')] as $url) {
            $response = $this->get($url)->assertOk();
            $response->assertSee('class="page-item', false);
            $response->assertSee('class="page-link', false);
            $response->assertDontSee('<svg', false);
        }
    }

    public function test_listing_pages_do_not_render_search_bars(): void
    {
        Supplier::factory()->create();
        Product::factory()->create();

        foreach ([
            route('products.index'),
            route('suppliers.index'),
            route('sale-items.index'),
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertDontSee('type="search"', false)
                ->assertDontSee('placeholder="Search', false);
        }
    }

    public function test_sale_has_exactly_five_attributes_including_sale_date(): void
    {
        $columns = Schema::getColumnListing('sales');
        sort($columns);

        $this->assertSame(['customer_name', 'id', 'payment_method', 'sale_date', 'total'], $columns);
    }

    public function test_sale_model_sets_sale_date_when_created_without_one(): void
    {
        $sale = Sale::create([
            'customer_name' => 'Walk-in',
            'total' => '4.50',
            'payment_method' => 'cash',
        ]);

        $this->assertNotNull($sale->sale_date);
        $this->assertSame('4.50', $sale->total);
        $this->assertSame(Sale::PAYMENT_METHODS, ['cash', 'card', 'mobile']);
        $this->assertSame(1, Sale::today()->count());
    }

    public function test_supplier_and_stock_movement_migrations_create_their_tables(): void
    {
        $groceryTables = ['products', 'suppliers', 'sales', 'sale_items', 'stock_movements'];
        $this->assertSame(5, count(array_filter($groceryTables, fn ($table) => Schema::hasTable($table))));
        $this->assertSame([], array_values(array_intersect(
            ['users', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'],
            Schema::getTableListing(),
        )));

        $this->assertTrue(Schema::hasColumns('suppliers', [
            'id',
            'name',
            'contact_name',
            'email',
            'phone',
            'address',
            'created_at',
            'updated_at',
        ]));

        $this->assertTrue(Schema::hasColumns('stock_movements', [
            'id',
            'product_id',
            'movement_type',
            'quantity',
            'reason',
            'recorded_at',
        ]));
    }

    public function test_product_can_be_created_with_valid_inventory_values(): void
    {
        $this->post(route('products.store'), $this->productData())
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'sku' => 'PROD-001',
            'name' => 'Apples',
            'stock' => 12,
        ]);
    }

    public function test_product_price_cannot_exceed_the_database_decimal_limit(): void
    {
        $this->from(route('products.create'))
            ->post(route('products.store'), $this->productData(['price' => '1000000.00']))
            ->assertRedirect(route('products.create'))
            ->assertSessionHasErrors('price');

        $this->assertSame(0, Product::count());
    }

    public function test_supplier_can_be_created_updated_viewed_and_deleted(): void
    {
        $this->get(route('suppliers.create'))->assertOk()->assertSee('Supplier name');
        $this->post(route('suppliers.store'), [
            'name' => 'Fresh Farms',
            'contact_name' => 'Taylor Green',
            'email' => 'orders@freshfarms.test',
            'phone' => '555-0100',
            'address' => '12 Market Road',
        ])->assertRedirect(route('suppliers.index'));

        $supplier = Supplier::where('email', 'orders@freshfarms.test')->firstOrFail();
        $product = Product::create($this->productData(['supplier_id' => $supplier->id]));

        $this->get(route('suppliers.index'))->assertOk()->assertSee('Fresh Farms');
        $this->get(route('suppliers.show', $supplier))
            ->assertOk()
            ->assertSee('Taylor Green')
            ->assertSee('Apples');
        $this->get(route('suppliers.edit', $supplier))->assertOk()->assertSee('Save Changes');

        $this->put(route('suppliers.update', $supplier), [
            'name' => 'Fresh Farms Co.',
            'contact_name' => 'Taylor Green',
            'email' => 'orders@freshfarms.test',
            'phone' => '555-0101',
            'address' => '12 Market Road',
        ])->assertRedirect(route('suppliers.show', $supplier));

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Fresh Farms Co.',
            'phone' => '555-0101',
        ]);
        $this->assertSame($supplier->id, $product->fresh()->supplier_id);

        $this->delete(route('suppliers.destroy', $supplier))
            ->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
        $this->assertNull($product->fresh()->supplier_id);
    }

    public function test_supplier_requires_a_name_and_valid_email(): void
    {
        $this->from(route('suppliers.create'))
            ->post(route('suppliers.store'), [
                'name' => '',
                'email' => 'not-an-email',
            ])
            ->assertRedirect(route('suppliers.create'))
            ->assertSessionHasErrors(['name', 'email']);
    }

    public function test_product_can_be_assigned_to_a_supplier(): void
    {
        $supplier = Supplier::create(['name' => 'Fresh Farms']);

        $this->post(route('products.store'), $this->productData([
            'supplier_id' => $supplier->id,
        ]))->assertRedirect(route('products.index'));

        $product = Product::where('sku', 'PROD-001')->firstOrFail();

        $this->assertSame($supplier->id, $product->supplier->id);
        $this->assertTrue($supplier->products->contains($product));
        $this->get(route('products.index'))->assertSee('Fresh Farms');
    }

    public function test_model_factories_create_related_grocery_records(): void
    {
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->for($supplier)->create();
        $sale = Sale::factory()->create();
        $saleItem = SaleItem::factory()->for($sale)->for($product)->create();
        $movement = StockMovement::factory()->for($product)->create();

        $this->assertSame($supplier->id, $product->supplier->id);
        $this->assertSame($sale->id, $saleItem->sale->id);
        $this->assertSame($product->id, $saleItem->product->id);
        $this->assertSame($product->id, $movement->product->id);
    }

    public function test_database_seeder_populates_50_records_per_table(): void
    {
        Artisan::call('db:seed', ['--force' => true]);

        $this->assertSame(50, Supplier::count());
        $this->assertSame(50, Product::count());
        $this->assertSame(50, Sale::count());
        $this->assertSame(50, SaleItem::count());
        $this->assertSame(50, StockMovement::count());

        $this->assertNotNull(Product::inRandomOrder()->first()->supplier_id);
        $this->assertNotNull(SaleItem::inRandomOrder()->first()->sale_id);
        $this->assertNotNull(SaleItem::inRandomOrder()->first()->product_id);
    }

    public function test_product_and_stock_movement_forms_load(): void
    {
        Supplier::create(['name' => 'Fresh Farms']);
        Product::create($this->productData());

        $this->get(route('products.create'))->assertOk()->assertSee('Fresh Farms');
        $this->get(route('stock-movements.create'))->assertOk()->assertSee('Stock received (add)');
    }

    public function test_stock_in_movement_increases_inventory_and_is_listed(): void
    {
        $product = Product::create($this->productData(['stock' => 8]));

        $this->post(route('stock-movements.store'), [
            'product_id' => $product->id,
            'movement_type' => 'in',
            'quantity' => 5,
            'reason' => 'Supplier delivery',
        ])->assertRedirect(route('stock-movements.index'));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 13]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'in',
            'quantity' => 5,
            'reason' => 'Supplier delivery',
        ]);

        $this->get(route('stock-movements.index'))
            ->assertOk()
            ->assertSee('Supplier delivery')
            ->assertSee('Apples');
    }

    public function test_stock_out_movement_cannot_remove_more_than_available(): void
    {
        $product = Product::create($this->productData(['stock' => 2]));

        $this->from(route('stock-movements.create'))
            ->post(route('stock-movements.store'), [
                'product_id' => $product->id,
                'movement_type' => 'out',
                'quantity' => 3,
                'reason' => 'Damaged goods',
            ])
            ->assertRedirect(route('stock-movements.create'))
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 2]);
        $this->assertSame(0, StockMovement::count());
    }

    public function test_product_can_be_updated_and_deleted(): void
    {
        $product = Product::create($this->productData());

        $this->put(route('products.update', $product), $this->productData([
            'name' => 'Green apples',
            'price' => '3.50',
        ]))->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Green apples',
            'price' => '3.50',
        ]);

        $this->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_checkout_records_sale_and_decreases_stock(): void
    {
        $product = Product::create($this->productData());

        $response = $this->post(route('sales.store'), [
            'customer_name' => 'Jamie Shopper',
            'payment_method' => 'cash',
            'products' => [$product->id => 3],
        ])->assertRedirect();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 9]);
        $this->assertDatabaseHas('sales', ['total' => '9.00', 'payment_method' => 'cash']);
        $this->assertDatabaseHas('sales', ['customer_name' => 'Jamie Shopper']);
        $this->assertNotNull(Sale::firstOrFail()->sale_date);
        $this->assertDatabaseHas('sale_items', [
            'product_id' => $product->id,
            'product_name' => 'Apples',
            'quantity' => 3,
            'unit_price' => '3.00',
            'line_total' => '9.00',
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'out',
            'quantity' => 3,
            'reason' => 'Sale #1',
        ]);

        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('Apples')
            ->assertSee('Jamie Shopper')
            ->assertSee('$9.00');
    }

    public function test_checkout_rejects_quantities_above_available_stock(): void
    {
        $product = Product::create($this->productData(['stock' => 2]));

        $this->from(route('sales.create'))
            ->post(route('sales.store'), [
                'payment_method' => 'card',
                'products' => [$product->id => 3],
            ])
            ->assertRedirect(route('sales.create'))
            ->assertSessionHasErrors('products');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 2]);
        $this->assertSame(0, Sale::count());
    }

    public function test_checkout_rejects_a_total_above_the_database_decimal_limit(): void
    {
        $product = Product::create($this->productData([
            'price' => '999999.99',
            'stock' => 2,
        ]));

        $this->from(route('sales.create'))
            ->post(route('sales.store'), [
                'payment_method' => 'cash',
                'products' => [$product->id => 2],
            ])
            ->assertRedirect(route('sales.create'))
            ->assertSessionHasErrors('products');

        $this->assertSame(2, $product->fresh()->stock);
        $this->assertSame(0, Sale::count());
        $this->assertSame(0, StockMovement::count());
    }

    public function test_checkout_requires_at_least_one_item(): void
    {
        $product = Product::create($this->productData());

        $this->from(route('sales.create'))
            ->post(route('sales.store'), [
                'payment_method' => 'cash',
                'products' => [$product->id => 0],
            ])
            ->assertRedirect(route('sales.create'))
            ->assertSessionHasErrors('products');
    }

    private function productData(array $overrides = []): array
    {
        return array_merge([
            'sku' => 'PROD-001',
            'supplier_id' => null,
            'name' => 'Apples',
            'category' => 'Produce',
            'unit' => 'kg',
            'price' => '3.00',
            'stock' => 12,
            'reorder_level' => 4,
        ], $overrides);
    }
}
