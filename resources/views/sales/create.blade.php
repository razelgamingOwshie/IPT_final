@extends('layouts.app')

@section('title', 'New Sale')

@section('content')
<div class="py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1>New Sale</h1>
            <p class="text-body-secondary mb-0">Select products and quantities to complete checkout.</p>
        </div>
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">Sales History</a>
    </div>
    @if ($products->isEmpty())
        <div class="alert alert-info">There are no products in stock. <a href="{{ route('products.index') }}">Update your inventory</a> before starting a sale.</div>
    @else
        <form method="POST" action="{{ route('sales.store') }}">
            @csrf
            <div class="row g-4 align-items-start">
                <div class="col-12 col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h2 class="h5 mb-1">Available Products</h2>
                            <p class="small text-body-secondary mb-0">Enter the quantity for each item.</p>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Available</th><th style="min-width: 100px">Quantity</th></tr></thead>
                                <tbody>
                                    @foreach ($products as $product)
                                        <tr class="pos-product" data-name="{{ $product->name }}">
                                            <td>{{ $product->name }}</td>
                                            <td>{{ $product->category }}</td>
                                            <td>${{ number_format((float) $product->price, 2) }}</td>
                                            <td>{{ $product->stock }} {{ $product->unit }}</td>
                                            <td>
                                                <input type="number" name="products[{{ $product->id }}]" value="{{ old('products.'.$product->id, 0) }}" min="0" max="{{ $product->stock }}" step="1" data-price="{{ $product->price }}" class="form-control form-control-sm quantity-input" aria-label="Quantity of {{ $product->name }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h2 class="h5 mb-1">Order Summary</h2>
                            <p class="small text-body-secondary mb-0">Review your basket before checkout.</p>
                        </div>
                        <div class="card-body">
                            <p class="small text-body-secondary" id="checkout-empty">Your basket is empty. Add quantities to the products to get started.</p>
                            <ul class="list-group list-group-flush mb-3" id="checkout-lines" hidden></ul>
                            <label for="customer_name" class="form-label">Customer name <span class="text-body-secondary">(optional)</span></label>
                            <input id="customer_name" name="customer_name" value="{{ old('customer_name') }}" class="form-control mb-3" maxlength="120" autocomplete="name" placeholder="Walk-in customer">
                            <label for="payment_method" class="form-label">Payment method</label>
                            <select id="payment_method" name="payment_method" class="form-select mb-3" required>
                                @foreach ($paymentMethods as $paymentMethod)
                                    <option value="{{ $paymentMethod }}" @selected(old('payment_method', 'cash') === $paymentMethod)>{{ ucfirst($paymentMethod) }}{{ $paymentMethod === 'mobile' ? ' Payment' : '' }}</option>
                                @endforeach
                            </select>
                            <div class="d-flex justify-content-between border-top pt-3 mb-3">
                                <strong>Total</strong>
                                <strong id="checkout-total">$0.00</strong>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Complete Sale</button>
                            <p class="small text-body-secondary text-center mt-2 mb-0">Stock is updated automatically after checkout.</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif
</div>
@endsection
