<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Supplier;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('dashboard', [
            'counts' => [
                'products' => Product::count(),
                'suppliers' => Supplier::count(),
                'sales' => Sale::count(),
                'sale-items' => SaleItem::count(),
                'stock-movements' => StockMovement::count(),
            ],
        ]);
    }
}
