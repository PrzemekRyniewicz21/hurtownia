<?php

use App\Http\Controllers\ProductsController;
use App\Http\Middleware\HurtowniaApiKey;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::put('/update/{product}', [ProductsController::class, 'update'])->name('update-products');

Route::middleware('myApiKey')->group(function () {

    Route::get('/products', [ProductsController::class, 'index'])->name('get-products');
    Route::put('/update', [ProductsController::class, 'update'])->name('update-products');
    Route::get('/show', [ProductsController::class, 'show'])->name('show-products');
});
