@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="py-5">
    <div class="text-center mb-5">
        <h1>Grocery Store Management</h1>
    </div>
    <div class="row g-4">
        @foreach ([
            'products' => ['Products', 'products.index'],
            'suppliers' => ['Suppliers', 'suppliers.index'],
            'sales' => ['Sales', 'sales.index'],
            'sale-items' => ['Sale Items', 'sale-items.index'],
            'stock-movements' => ['Stock Movements', 'stock-movements.index'],
        ] as $key => [$label, $route])
            <div class="col-12 col-sm-6 col-lg">
                <a href="{{ route($route) }}" class="card h-100 text-decoration-none text-dark shadow-sm">
                    <div class="card-body text-center">
                        <h2 class="display-6">{{ $counts[$key] }}</h2>
                        <p class="card-text mb-0">{{ $label }}</p>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection
