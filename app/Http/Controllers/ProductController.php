<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::query()
            ->with('supplier')
            ->orderBy('name')
            ->paginate(12);

        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create', [
            'product' => new Product,
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'sku' => 'required|string|max:50|unique:products,sku',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'name' => 'required|string|max:120',
            'category' => 'required|string|max:80',
            'unit' => 'required|string|max:30',
            'price' => 'required|numeric|min:0.01|max:999999.99',
            'stock' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
        ]);

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Product added to inventory.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return view('products.edit', [
            'product' => $product,
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'sku' => 'required|string|max:50|unique:products,sku,' . $product->id,
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'name' => 'required|string|max:120',
            'category' => 'required|string|max:80',
            'unit' => 'required|string|max:30',
            'price' => 'required|numeric|min:0.01|max:999999.99',
            'stock' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
        ]);

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Product details updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        if ($product->saleItems()->exists()) {
            return redirect()->route('products.index')->with('error', 'This product is part of a sale and cannot be deleted.');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product removed from inventory.');
    }
}
