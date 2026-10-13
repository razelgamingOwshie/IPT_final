<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(): View
    {
        return view('sales.index', [
            'sales' => Sale::withCount('items')->latest('sale_date')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('sales.create', [
            'products' => Product::where('stock', '>', 0)->orderBy('name')->get(),
            'paymentMethods' => Sale::PAYMENT_METHODS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:120'],
            'payment_method' => ['required', Rule::in(Sale::PAYMENT_METHODS)],
            'products' => ['required', 'array'],
            'products.*' => ['required', 'integer', 'min:0'],
        ]);

        $quantities = collect($validated['products'])
            ->map(fn ($quantity) => (int) $quantity)
            ->filter(fn ($quantity) => $quantity > 0);

        if ($quantities->isEmpty()) {
            throw ValidationException::withMessages([
                'products' => 'Add at least one item to complete the sale.',
            ]);
        }

        $sale = DB::transaction(function () use ($quantities, $validated) {
            $items = [];
            $totalCents = 0;

            foreach ($quantities as $productId => $quantity) {
                $product = Product::query()->lockForUpdate()->find($productId);

                if (! $product || $product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'products' => "There is not enough stock for ".($product?->name ?? 'one of the selected products').'. Please review the quantities.',
                    ]);
                }

                $unitPriceCents = (int) round((float) $product->price * 100);
                $lineTotalCents = $unitPriceCents * $quantity;

                if ($lineTotalCents > 99_999_999 || $totalCents + $lineTotalCents > 99_999_999) {
                    throw ValidationException::withMessages([
                        'products' => 'The sale total cannot exceed $999,999.99.',
                    ]);
                }

                $totalCents += $lineTotalCents;
                $product->decrement('stock', $quantity);

                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => number_format($unitPriceCents / 100, 2, '.', ''),
                    'line_total' => number_format($lineTotalCents / 100, 2, '.', ''),
                ];
            }

            $sale = Sale::create([
                'customer_name' => $validated['customer_name'] ?? null,
                'total' => number_format($totalCents / 100, 2, '.', ''),
                'payment_method' => $validated['payment_method'],
                'sale_date' => now(),
            ]);

            $saleItems = $sale->items()->createMany($items)->load('product');

            foreach ($saleItems as $saleItem) {
                $saleItem->product?->stockMovements()->create([
                        'movement_type' => 'out',
                        'quantity' => $saleItem->quantity,
                        'reason' => "Sale #{$sale->id}",
                        'recorded_at' => $sale->sale_date,
                    ]);
            }

            return $sale;
        });

        return to_route('sales.show', $sale)->with('success', 'Sale completed and inventory updated.');
    }

    public function show(Sale $sale): View
    {
        return view('sales.show', [
            'sale' => $sale->load('items'),
        ]);
    }
}
