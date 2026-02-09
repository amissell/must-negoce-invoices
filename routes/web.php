<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ManualInvoiceController;

// Dashboard
Route::get('/', [InvoiceController::class, 'index'])->name('invoices.index');

// Invoice Generation
Route::post('/invoices/generate', [InvoiceController::class, 'generate'])->name('invoices.generate');
Route::get('/invoices/{id}/download', [InvoiceController::class, 'downloadPdf'])->name('invoices.download');
Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
// Routes pour éditer les factures
Route::get('/invoices/{id}/edit', [ManualInvoiceController::class, 'edit'])->name('invoices.edit');
Route::put('/invoices/{id}', [ManualInvoiceController::class, 'update'])->name('invoices.update');

// Products - Dedicated Page
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

// Manual Invoice
Route::prefix('invoices/manual')->name('invoices.manual.')->group(function () {
    Route::get('/create', [ManualInvoiceController::class, 'create'])->name('create');
    Route::post('/store', [ManualInvoiceController::class, 'store'])->name('store');
});
