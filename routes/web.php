<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;

// Dashboard - الصفحة الرئيسية
Route::get('/', [InvoiceController::class, 'index'])->name('invoices.index');


// Generate invoices - هنا كيتولدو الفواتير (المهم!)
Route::post('/invoices/generate', [InvoiceController::class, 'generate'])->name('invoices.generate');

// Download PDF
Route::get('/invoices/{id}/download', [InvoiceController::class, 'downloadPdf'])->name('invoices.download');

// Show single invoice
Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');

// Products
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');