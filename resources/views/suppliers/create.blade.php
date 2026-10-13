@extends('layouts.app')

@section('title', 'Add Supplier')

@section('content')
<div class="py-4">
    <div class="mb-4">
        <h1>Add New Supplier</h1>
        <p class="text-body-secondary mb-0">Save a supplier and contact details for your store.</p>
    </div>
    @include('suppliers.form')
</div>
@endsection
