@extends('layouts.app')

@section('title', 'Sales')

@section('content')
<div class="py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1>Sales</h1>
            <p class="text-body-secondary mb-0">Browse completed transactions and payment details.</p>
        </div>
        <a href="{{ route('sales.create') }}" class="btn btn-primary">New Sale</a>
    </div>
    @if ($sales->isEmpty())
        <div class="alert alert-info">No sales recorded yet. <a href="{{ route('sales.create') }}">Start a new sale</a>.</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead><tr><th>Sale ID</th><th>Sale Date</th><th>Customer Name</th><th>Payment Method</th><th>Total</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach ($sales as $sale)
                        <tr>
                            <td>#{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $sale->sale_date->format('M j, Y g:i A') }}</td>
                            <td>{{ $sale->customer_name ?: 'Walk-in customer' }}</td>
                            <td>{{ ucfirst($sale->payment_method) }}</td>
                            <td>${{ number_format((float) $sale->total, 2) }}</td>
                            <td><a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-primary">Receipt</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="small text-body-secondary">Showing {{ $sales->firstItem() }}–{{ $sales->lastItem() }} of {{ $sales->total() }} sales</span>
            {{ $sales->links() }}
        </div>
    @endif
</div>
@endsection
