<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoicePdfController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/generate', [DashboardController::class, 'generate'])->name('invoices.generate');
Route::get('/invoices/{invoice}', [DashboardController::class, 'show'])->name('invoices.show');

// PDF Routes
Route::get('/invoices/{invoice}/pdf', [InvoicePdfController::class, 'download'])->name('invoices.pdf.download');
Route::get('/invoices/{invoice}/pdf/view', [InvoicePdfController::class, 'view'])->name('invoices.pdf.view');

// Products CRUD
Route::resource('products', ProductController::class);
