@extends('layouts.app')

@section('title', 'Record Stock Movement')

@section('content')
<div class="py-4">
    <div class="mb-4">
        <h1>Record Stock Movement</h1>
        <p class="text-body-secondary mb-0">Add received stock or record stock removed from inventory.</p>
    </div>
    @if ($products->isEmpty())
        <div class="alert alert-info">Add a product to your inventory before recording a stock movement.</div>
    @else
        <form method="POST" action="{{ route('stock-movements.store') }}" class="card shadow-sm">
            @csrf
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="product_id" class="form-label">Product</label>
                        <select id="product_id" name="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                            <option value="">Choose a product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected((string) old('product_id') === (string) $product->id)>{{ $product->name }} ({{ $product->stock }} {{ $product->unit }} in stock)</option>
                            @endforeach
                        </select>
                        @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="movement_type" class="form-label">Movement type</label>
                        <select id="movement_type" name="movement_type" class="form-select @error('movement_type') is-invalid @enderror" required>
                            <option value="in" @selected(old('movement_type', 'in') === 'in')>Stock received (add)</option>
                            <option value="out" @selected(old('movement_type') === 'out')>Stock removed (subtract)</option>
                        </select>
                        @error('movement_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input id="quantity" type="number" name="quantity" value="{{ old('quantity') }}" min="1" step="1" class="form-control @error('quantity') is-invalid @enderror" required>
                        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="reason" class="form-label">Reason <span class="text-body-secondary">(optional)</span></label>
                        <input id="reason" name="reason" value="{{ old('reason') }}" maxlength="255" class="form-control @error('reason') is-invalid @enderror" placeholder="e.g. Supplier delivery">
                        @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="{{ route('stock-movements.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Record Movement</button>
            </div>
        </form>
    @endif
</div>
@endsection
