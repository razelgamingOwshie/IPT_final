@extends('layouts.app')

@section('title', 'Sale Receipt')

@section('content')
<div class="py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1>Sale Receipt #{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</h1>
            <p class="text-body-secondary mb-0">{{ $sale->sale_date->format('F j, Y \a\t g:i A') }}</p>
            <p class="text-body-secondary mb-0">Customer: {{ $sale->customer_name ?: 'Walk-in customer' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">Sales History</a>
            <a href="{{ route('sales.create') }}" class="btn btn-primary">New Sale</a>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
            <span class="fw-semibold text-success">Payment received</span>
            <span class="badge text-bg-primary">{{ ucfirst($sale->payment_method) }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead><tr><th>Product</th><th>Unit Price</th><th>Quantity</th><th>Subtotal</th></tr></thead>
                <tbody>
                    @foreach ($sale->items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>${{ number_format((float) $item->unit_price, 2) }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>${{ number_format((float) $item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot><tr><th colspan="3" class="text-end">Total paid</th><th>${{ number_format((float) $sale->total, 2) }}</th></tr></tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
