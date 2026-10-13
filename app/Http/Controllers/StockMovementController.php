<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockMovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $movements = StockMovement::with('product')->latest('recorded_at')->paginate(15);

        return view('stock-movements.index', compact('movements'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::orderBy('name')->get();

        return view('stock-movements.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'movement_type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data) {
            $product = Product::query()->lockForUpdate()->findOrFail($data['product_id']);
            $quantity = (int) $data['quantity'];

            if ($data['movement_type'] === 'out' && $product->stock < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Only {$product->stock} {$product->unit} of {$product->name} are currently in stock.",
                ]);
            }

            if ($data['movement_type'] === 'in') {
                $product->increment('stock', $quantity);
            } else {
                $product->decrement('stock', $quantity);
            }

            $product->stockMovements()->create([
                'movement_type' => $data['movement_type'],
                'quantity' => $quantity,
                'reason' => $data['reason'] ?? null,
                'recorded_at' => now(),
            ]);
        });

        return redirect()->route('stock-movements.index')->with('success', 'Stock movement recorded and inventory updated.');
    }
}
