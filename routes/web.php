<?php

use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Middleware\RedirectIfStaff;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => redirect()->route('shop.index'));

Route::middleware(['auth', RedirectIfStaff::class])->group(function () {
    Route::get('/tienda', [ShopController::class, 'index'])->name('shop.index');
    Route::get('/dashboard', fn () => redirect()->route('shop.index'))->name('dashboard');

    Route::get('/carrito', fn () => Inertia::render('Cart'))->name('cart');
    Route::get('/checkout', fn () => Inertia::render('Checkout'))->name('checkout');

    Route::post('/pedidos', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/mis-pedidos', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/mis-pedidos/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/facturas/{order}', [InvoiceController::class, 'show'])
    ->middleware('auth')
    ->name('orders.invoice');

require __DIR__.'/auth.php';
