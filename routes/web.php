<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', fn () => view('admin.dashboard'))->name('admin.dashboard');
Route::get('/urun-bilgisi/{share_token}', [ProductController::class, 'show'])->name('product.show');
Route::get('/urunler', [ProductController::class, 'index'])->name('product.index');
Route::get('/urunler/yeni', [ProductController::class, 'create'])->name('product.create');
Route::get('/urunler/{product:uuid}/duzenle', [ProductController::class, 'edit'])->name('product.edit');
Route::match(['put', 'patch'], '/urunler/{product:uuid}', [ProductController::class, 'update'])->name('product.update');
Route::delete('/urunler/{product:uuid}', [ProductController::class, 'destroy'])->name('product.destroy');
