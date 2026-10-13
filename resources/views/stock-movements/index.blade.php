@extends('layouts.app')

@section('title', 'Stock Movements')

@section('content')
<div class="py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1>Stock Movements</h1>
            <p class="text-body-secondary mb-0">Review inventory additions and removals.</p>
        </div>
        <a href="{{ route('stock-movements.create') }}" class="btn btn-primary">Record Stock Movement</a>
    </div>
    @if ($movements->isEmpty())
        <div class="alert alert-info">No stock movements recorded yet. <a href="{{ route('stock-movements.create') }}">Record your first movement</a>.</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead><tr><th>Date &amp; Time</th><th>Product</th><th>Movement</th><th>Quantity</th><th>Reason</th><th>Stock now</th></tr></thead>
                <tbody>
                    @foreach ($movements as $movement)
                        <tr>
                            <td>{{ $movement->recorded_at->format('M j, Y g:i A') }}</td>
                            <td>{{ $movement->product?->name ?? 'Product removed' }}</td>
                            <td><span class="badge {{ $movement->movement_type === 'in' ? 'text-bg-success' : 'text-bg-warning' }}">{{ $movement->movement_type === 'in' ? 'Stock in' : 'Stock out' }}</span></td>
                            <td>{{ $movement->quantity }} {{ $movement->product?->unit }}</td>
                            <td>{{ $movement->reason ?: '—' }}</td>
                            <td>{{ $movement->product?->stock ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="small text-body-secondary">Showing {{ $movements->firstItem() }}–{{ $movements->lastItem() }} of {{ $movements->total() }} movements</span>
            {{ $movements->links() }}
        </div>
    @endif
</div>
@endsection
