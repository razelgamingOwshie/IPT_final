@extends('layouts.app')

@section('title', 'Sale Item')

@section('content')
<div class="py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1>Line Item #{{ str_pad($saleItem->id, 5, '0', STR_PAD_LEFT) }}</h1>
            <p class="text-body-secondary mb-0">Sale #{{ str_pad($saleItem->sale_id, 5, '0', STR_PAD_LEFT) }} · {{ $saleItem->sale?->sale_date?->format('M j, Y g:i A') ?: '—' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sale-items.index') }}" class="btn btn-outline-secondary">All Items</a>
            <a href="{{ route('sale-items.edit', $saleItem) }}" class="btn btn-warning">Edit</a>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white"><h2 class="h5 mb-0">Line Item Details</h2></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6"><strong>Sale ID</strong><div>#{{ str_pad($saleItem->sale_id, 5, '0', STR_PAD_LEFT) }}</div></div>
                <div class="col-12 col-md-6"><strong>Customer</strong><div>{{ $saleItem->sale?->customer_name ?: 'Walk-in customer' }}</div></div>
                <div class="col-12 col-md-6"><strong>Product</strong><div>{{ $saleItem->product_name }}</div></div>
                <div class="col-12 col-md-6"><strong>Unit price</strong><div>${{ number_format((float) $saleItem->unit_price, 2) }}</div></div>
                <div class="col-12 col-md-6"><strong>Quantity</strong><div>{{ $saleItem->quantity }} {{ $saleItem->product?->unit ?: '' }}</div></div>
                <div class="col-12 col-md-6"><strong>Line total</strong><div class="fw-semibold text-success">${{ number_format((float) $saleItem->line_total, 2) }}</div></div>
                @if ($saleItem->product)
                    <div class="col-12"><strong>Linked product</strong><div>{{ $saleItem->product->name }} ({{ $saleItem->product->category }})</div></div>
                @endif
            </div>
        </div>
    </div>

    <form action="{{ route('sale-items.destroy', $saleItem) }}" method="POST" onsubmit="return confirm('Remove line item #{{ str_pad($saleItem->id, 5, '0', STR_PAD_LEFT) }} from sale #{{ str_pad($saleItem->sale_id, 5, '0', STR_PAD_LEFT) }}? The sale total will be recalculated.')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Remove Line Item</button>
    </form>
</div>
@endsection
