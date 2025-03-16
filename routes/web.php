<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TestController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [ProductController::class, 'index'])->name('home');

Route::post('/upload-csv', [ProductController::class, 'uploadCsv'])->name('upload.csv');
Route::get('/download-csv', [ProductController::class, 'downloadCsv'])->name('download.csv');

Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');


Route::get('/test', function () {
    return view('test');
})->name('test');


Route::post('/api/token', [TestController::class, 'getToken']);
