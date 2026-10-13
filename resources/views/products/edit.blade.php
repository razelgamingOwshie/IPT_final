@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="py-4">
    <div class="mb-4">
        <h1>Edit Product</h1>
        <p class="text-body-secondary mb-0">Update details for {{ $product->name }}.</p>
    </div>
    @include('products.form')
</div>
@endsection
