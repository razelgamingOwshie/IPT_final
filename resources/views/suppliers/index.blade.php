@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')
<div class="py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1>Suppliers</h1>
            <p class="text-body-secondary mb-0">Manage supplier contacts and the products they provide.</p>
        </div>
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary">Add New Supplier</a>
    </div>
    @if ($suppliers->isEmpty())
        <div class="alert alert-info">No suppliers have been added. <a href="{{ route('suppliers.create') }}">Add a supplier</a> to get started.</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead><tr><th>Supplier</th><th>Contact Person</th><th>Email</th><th>Phone</th><th>Products</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach ($suppliers as $supplier)
                        <tr>
                            <td><a href="{{ route('suppliers.show', $supplier) }}">{{ $supplier->name }}</a></td>
                            <td>{{ $supplier->contact_name ?: '—' }}</td>
                            <td>{{ $supplier->email ?: '—' }}</td>
                            <td>{{ $supplier->phone ?: '—' }}</td>
                            <td>{{ $supplier->products_count }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete {{ addslashes($supplier->name) }}? Assigned products will remain in inventory without a supplier.')">
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
            <span class="small text-body-secondary">Showing {{ $suppliers->firstItem() }}–{{ $suppliers->lastItem() }} of {{ $suppliers->total() }} suppliers</span>
            {{ $suppliers->links() }}
        </div>
    @endif
</div>
@endsection
