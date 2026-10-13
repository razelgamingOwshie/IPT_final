<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleItemController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::resource('products', ProductController::class)->except('show');
Route::resource('suppliers', SupplierController::class);
Route::get('/stock-movements', [StockMovementController::class, 'index'])->name('stock-movements.index');
Route::get('/stock-movements/create', [StockMovementController::class, 'create'])->name('stock-movements.create');
Route::post('/stock-movements', [StockMovementController::class, 'store'])->name('stock-movements.store');

Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');

Route::resource('sale-items', SaleItemController::class);
