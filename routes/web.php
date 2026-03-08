<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingController;

Route::get('/', DashboardController::class)->name('admin.dashboard');
Route::get('/ayarlar', [SettingController::class, 'index'])->name('setting.index');
Route::post('/ayarlar', [SettingController::class, 'update'])->name('setting.update');
Route::get('/ziyaretler', fn () => view('admin.visit-index'))->name('visit.index');
Route::get('/urun-bilgisi/{share_token}', [ProductController::class, 'gate'])->name('product.gate');
Route::post('/urun-bilgisi/{share_token}/kaydet', [ProductController::class, 'saveVisit'])->name('product.saveVisit');
Route::post('/urun-bilgisi/{share_token}/kaydet-anonim', [ProductController::class, 'saveVisitAnonymous'])->name('product.saveVisitAnonymous');
Route::match(['get', 'post'], '/urun-bilgisi/{share_token}/onayla', [ProductController::class, 'confirmEnter'])->name('product.confirmEnter');
Route::get('/urunler', [ProductController::class, 'index'])->name('product.index');
Route::get('/urunler/yeni', [ProductController::class, 'create'])->name('product.create');
Route::get('/urunler/{product:uuid}/duzenle', [ProductController::class, 'edit'])->name('product.edit');
Route::match(['put', 'patch'], '/urunler/{product:uuid}', [ProductController::class, 'update'])->name('product.update');
Route::delete('/urunler/{product:uuid}', [ProductController::class, 'destroy'])->name('product.destroy');
