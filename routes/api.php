<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\MutasiController;
require __DIR__.'/auth.php';

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::resource('produks', ProdukController::class);
    Route::resource('lokasis', LokasiController::class);
    Route::resource('mutasis', MutasiController::class);
    Route::get('produks/{produk}/history-mutasi', [ProdukController::class, 'historyMutasi']);
    Route::get('users/{user}/history-mutasi', [MutasiController::class, 'historyMutasiByUser']);
    Route::post('produks/{produk}/stok', [ProdukController::class, 'setStok']);
    Route::resource('users', App\Http\Controllers\UserController::class)->except(['store', 'destroy']); // User register di /api/register
});
