@extends('layouts.app')

@section('title', 'Edit Line Item')

@section('content')
<div class="py-4">
    <div class="mb-4">
        <h1>Edit Line Item</h1>
        <p class="text-body-secondary mb-0">Update details for line item #{{ $saleItem->id }}.</p>
    </div>
    @include('sale-items.form')
</div>
@endsection
