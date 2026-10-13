@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')
<div class="py-4">
    <div class="mb-4">
        <h1>Edit Supplier</h1>
        <p class="text-body-secondary mb-0">Update contact information for {{ $supplier->name }}.</p>
    </div>
    @include('suppliers.form')
</div>
@endsection
