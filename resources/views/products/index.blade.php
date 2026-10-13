@extends('layouts.app')

@section('title', 'Inventory')

@section('content')
<div class="py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1>Products</h1>
            <p class="text-body-secondary mb-0">Manage grocery products, prices, and stock levels.</p>
        </div>
        <a href="{{ route('products.create') }}" class="btn btn-primary">Add New Product</a>
    </div>
    @if ($products->isEmpty())
        <div class="alert alert-info">No products in inventory. <a href="{{ route('products.create') }}">Add a product</a> to get started.</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr><th>SKU</th><th>Product Name</th><th>Category</th><th>Supplier</th><th>Unit</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category }}</td>
                            <td>{{ $product->supplier?->name ?? '—' }}</td>
                            <td>{{ $product->unit }}</td>
                            <td>${{ number_format((float) $product->price, 2) }}</td>
                            <td>{{ $product->stock }}</td>
                            <td>
                                @if ($product->isLowStock())
                                    <span class="badge text-bg-warning">Low stock</span>
                                @else
                                    <span class="badge text-bg-success">In stock</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove {{ addslashes($product->name) }} from inventory?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="small text-body-secondary">Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }} products</span>
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
