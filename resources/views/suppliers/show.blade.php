@extends('layouts.app')

@section('title', $supplier->name)

@section('content')
<div class="py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1>{{ $supplier->name }}</h1>
            <p class="text-body-secondary mb-0">Supplier details and linked inventory products.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">Back to Suppliers</a>
            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning">Edit Supplier</a>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white"><h2 class="h5 mb-0">Contact Information</h2></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6"><strong>Contact person</strong><div>{{ $supplier->contact_name ?: 'Not provided' }}</div></div>
                <div class="col-12 col-md-6"><strong>Email</strong><div>{{ $supplier->email ?: 'Not provided' }}</div></div>
                <div class="col-12 col-md-6"><strong>Phone</strong><div>{{ $supplier->phone ?: 'Not provided' }}</div></div>
                <div class="col-12 col-md-6"><strong>Address</strong><div>{{ $supplier->address ?: 'Not provided' }}</div></div>
            </div>
        </div>
    </div>

    <section>
        <h2 class="h4 mb-3">Products from this supplier</h2>
        @if ($supplier->products->isEmpty())
            <div class="alert alert-info">No products are currently assigned to this supplier. You can assign products from their inventory form.</div>
        @else
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead><tr><th>SKU</th><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach ($supplier->products as $product)
                            <tr>
                                <td>{{ $product->sku }}</td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category }}</td>
                                <td>${{ number_format((float) $product->price, 2) }}</td>
                                <td>{{ $product->stock }} {{ $product->unit }}</td>
                                <td><a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary">Edit Product</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection
