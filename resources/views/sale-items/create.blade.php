@extends('layouts.app')

@section('title', 'Add Line Item')

@section('content')
<div class="py-4">
    <div class="mb-4">
        <h1>Add Sale Line Item</h1>
        <p class="text-body-secondary mb-0">Add an item to an existing sale. The sale total is recalculated automatically.</p>
    </div>
    @include('sale-items.form')
</div>
@endsection
