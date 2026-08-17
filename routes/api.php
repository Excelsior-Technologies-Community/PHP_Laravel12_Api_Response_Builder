<?php

use App\Http\Controllers\ProductController;
use App\Http\Middleware\ApiRateLimit;
use Illuminate\Support\Facades\Route;


Route::get(
    'products/export/csv',
    [ProductController::class, 'exportCsv']
)->middleware(ApiRateLimit::class);

/*
|--------------------------------------------------------------------------
| Product CRUD + search/filter/pagination
|--------------------------------------------------------------------------
*/
Route::apiResource(
    'products',
    ProductController::class
)->middleware(ApiRateLimit::class);
