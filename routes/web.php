<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;

Route::get('/', [InvoiceController::class, 'index'])->name('invoices.index');
Route::post('/invoices/generate', [InvoiceController::class, 'generate'])->name('invoices.generate');
Route::get('/invoices/{id}/download', [InvoiceController::class, 'downloadPdf'])->name('invoices.download');
Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');

Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');