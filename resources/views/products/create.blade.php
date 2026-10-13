@extends('layouts.app')

@section('title', 'Add Product')

@section('content')
<div class="py-4">
    <div class="mb-4">
        <h1>Add New Product</h1>
        <p class="text-body-secondary mb-0">Enter the product details and initial stock level.</p>
    </div>
    @include('products.form')
</div>
@endsection
