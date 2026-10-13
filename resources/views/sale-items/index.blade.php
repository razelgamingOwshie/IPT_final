@extends('layouts.app')

@section('title', 'Sale Items')

@section('content')
<div class="py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1>Sale Items</h1>
            <p class="text-body-secondary mb-0">Line items across all sales. Edit quantities, prices, or remove items.</p>
        </div>
        <a href="{{ route('sale-items.create') }}" class="btn btn-primary">Add Line Item</a>
    </div>
    @if ($items->isEmpty())
        <div class="alert alert-info">No sale items have been recorded. <a href="{{ route('sale-items.create') }}">Add a line item</a>.</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead><tr><th>Sale</th><th>Product</th><th>Quantity</th><th>Unit Price</th><th>Line Total</th><th>Recorded</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>#{{ str_pad($item->sale_id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>${{ number_format((float) $item->unit_price, 2) }}</td>
                            <td>${{ number_format((float) $item->line_total, 2) }}</td>
                            <td>{{ $item->sale?->sale_date?->format('M j, Y') ?: '—' }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('sale-items.show', $item) }}" class="btn btn-sm btn-outline-primary">View</a>
                                <a href="{{ route('sale-items.edit', $item) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('sale-items.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this line item from sale #{{ str_pad($item->sale_id, 5, '0', STR_PAD_LEFT) }}?')">
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
            <span class="small text-body-secondary">Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ $items->total() }} items</span>
            {{ $items->links() }}
        </div>
    @endif
</div>
@endsection
