<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <title>@yield('title', 'Grocery Store Management')</title>
</head>
<body class="d-flex min-vh-100 flex-column">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <div class="collapse navbar-collapse" id="main-navigation">
                <div class="navbar-nav flex-row flex-wrap justify-content-center w-100">
                    <a class="nav-link px-3 {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                    <a class="nav-link px-3 {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">Inventory</a>
                    <a class="nav-link px-3 {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">Suppliers</a>
                    <a class="nav-link px-3 {{ request()->routeIs('stock-movements.*') ? 'active' : '' }}" href="{{ route('stock-movements.index') }}">Stock Movements</a>
                    <a class="nav-link px-3 {{ request()->routeIs('sales.*') || request()->routeIs('sale-items.*') ? 'active' : '' }}" href="{{ route('sales.index') }}">Sales</a>
                    <a class="nav-link px-3 {{ request()->routeIs('sale-items.*') ? 'active' : '' }}" href="{{ route('sale-items.index') }}">Sale Items</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="container flex-grow-1">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger mt-3" role="alert">
                <p class="fw-bold mb-1">Please correct the following:</p>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    @stack('scripts')
</body>
</html>
