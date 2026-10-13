<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = SaleItem::query()
            ->with(['sale', 'product'])
            ->latest('sale_id')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('sale-items.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('sale-items.create', [
            'saleItem' => new SaleItem,
            'sales' => Sale::orderByDesc('sale_date')->get(['id', 'customer_name', 'sale_date']),
            'products' => Product::orderBy('name')->get(['id', 'name', 'price', 'unit']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'product_id' => 'nullable|exists:products,id',
            'product_name' => 'nullable|required_without:product_id|string|max:120',
            'quantity' => 'required|integer|min:1|max:99999',
            'unit_price' => 'required|numeric|min:0|max:999999.99',
        ]);

        $this->setLineTotal($data);

        $saleItem = DB::transaction(function () use ($data): SaleItem {
            $product = isset($data['product_id'])
                ? Product::query()->lockForUpdate()->findOrFail($data['product_id'])
                : null;
            $quantity = (int) $data['quantity'];

            if ($product && $product->stock < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Only {$product->stock} {$product->unit} of {$product->name} are currently in stock.",
                ]);
            }

            $data['product_name'] = $product?->name ?? $data['product_name'];
            $saleItem = SaleItem::create($data);

            if ($product) {
                $product->decrement('stock', $quantity);
                $this->recordMovement($product, 'out', $quantity, "Sale item #{$saleItem->id} recorded");
            }

            Sale::query()->lockForUpdate()->findOrFail($data['sale_id'])->recomputeTotal();

            return $saleItem;
        });

        return redirect()->route('sale-items.show', $saleItem)->with('success', 'Sale line item added.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SaleItem $saleItem)
    {
        $saleItem->load(['sale', 'product']);

        return view('sale-items.show', compact('saleItem'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SaleItem $saleItem)
    {
        return view('sale-items.edit', [
            'saleItem' => $saleItem,
            'sales' => Sale::orderByDesc('sale_date')->get(['id', 'customer_name', 'sale_date']),
            'products' => Product::orderBy('name')->get(['id', 'name', 'price', 'unit']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SaleItem $saleItem)
    {
        $data = $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'product_id' => 'nullable|exists:products,id',
            'product_name' => 'nullable|required_without:product_id|string|max:120',
            'quantity' => 'required|integer|min:1|max:99999',
            'unit_price' => 'required|numeric|min:0|max:999999.99',
        ]);

        $this->setLineTotal($data);

        DB::transaction(function () use ($saleItem, $data): void {
            $saleItem = SaleItem::query()->lockForUpdate()->findOrFail($saleItem->id);
            $saleIds = collect([$saleItem->sale_id, (int) $data['sale_id']])->unique()->sort()->values();
            $productIds = collect([$saleItem->product_id, $data['product_id'] ?? null])
                ->filter()
                ->unique()
                ->sort()
                ->values();
            $products = Product::query()->whereIn('id', $productIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $sales = Sale::query()->whereIn('id', $saleIds)->orderBy('id')->lockForUpdate()->get();
            $previousProduct = $saleItem->product_id ? $products->get($saleItem->product_id) : null;
            $product = isset($data['product_id']) ? $products->get($data['product_id']) : null;
            $quantity = (int) $data['quantity'];

            if (isset($data['product_id']) && ! $product) {
                throw ValidationException::withMessages([
                    'product_id' => 'The selected product is no longer available.',
                ]);
            }

            if ($previousProduct && $previousProduct->is($product)) {
                $quantityChange = $quantity - $saleItem->quantity;

                if ($quantityChange > 0 && $product->stock < $quantityChange) {
                    throw ValidationException::withMessages([
                        'quantity' => "Only {$product->stock} additional {$product->unit} of {$product->name} are currently in stock.",
                    ]);
                }

                if ($quantityChange > 0) {
                    $product->decrement('stock', $quantityChange);
                    $this->recordMovement($product, 'out', $quantityChange, "Sale item #{$saleItem->id} quantity increased");
                } elseif ($quantityChange < 0) {
                    $returnedQuantity = abs($quantityChange);
                    $product->increment('stock', $returnedQuantity);
                    $this->recordMovement($product, 'in', $returnedQuantity, "Sale item #{$saleItem->id} quantity reduced");
                }
            } else {
                if ($previousProduct) {
                    $previousProduct->increment('stock', $saleItem->quantity);
                    $this->recordMovement($previousProduct, 'in', $saleItem->quantity, "Sale item #{$saleItem->id} product changed");
                }

                if ($product && $product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'quantity' => "Only {$product->stock} {$product->unit} of {$product->name} are currently in stock.",
                    ]);
                }

                if ($product) {
                    $product->decrement('stock', $quantity);
                    $this->recordMovement($product, 'out', $quantity, "Sale item #{$saleItem->id} product changed");
                }
            }

            $data['product_name'] = $product?->name ?? $data['product_name'];
            $saleItem->update($data);

            $sales->each->recomputeTotal();
        });

        return redirect()->route('sale-items.show', $saleItem)->with('success', 'Sale line item updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SaleItem $saleItem)
    {
        DB::transaction(function () use ($saleItem): void {
            $saleItem = SaleItem::query()->lockForUpdate()->findOrFail($saleItem->id);
            $product = $saleItem->product_id
                ? Product::query()->lockForUpdate()->find($saleItem->product_id)
                : null;
            $sale = Sale::query()->lockForUpdate()->findOrFail($saleItem->sale_id);

            if ($product) {
                $product->increment('stock', $saleItem->quantity);
                $this->recordMovement($product, 'in', $saleItem->quantity, "Sale item #{$saleItem->id} removed");
            }

            $saleItem->delete();
            $sale->recomputeTotal();
        });

        return redirect()->route('sale-items.index')->with('success', 'Sale line item removed.');
    }

    private function recordMovement(Product $product, string $type, int $quantity, string $reason): void
    {
        $product->stockMovements()->create([
            'movement_type' => $type,
            'quantity' => $quantity,
            'reason' => $reason,
            'recorded_at' => now(),
        ]);
    }

    private function setLineTotal(array &$data): void
    {
        $unitPriceCents = (int) round((float) $data['unit_price'] * 100);
        $lineTotalCents = $unitPriceCents * (int) $data['quantity'];

        if ($lineTotalCents > 99_999_999) {
            throw ValidationException::withMessages([
                'quantity' => 'A sale line total cannot exceed $999,999.99.',
            ]);
        }

        $data['line_total'] = number_format($lineTotalCents / 100, 2, '.', '');
    }
}
