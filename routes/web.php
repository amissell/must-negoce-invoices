<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ManualInvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CreanceController;
use App\Http\Controllers\AlerteController;
use App\Http\Controllers\ObjectifVendeurController;
use App\Http\Controllers\VendeurController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\DashboardVendeursController;
use App\Http\Controllers\DashboardCategoriesController;
use App\Http\Controllers\DashboardVendeurCategorieController;


Route::middleware('guest')->group(function () {
    Route::get('/login',    [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login',   [LoginController::class, 'login']);
    Route::get('/register', [LoginController::class, 'showRegisterForm'])->name('register');
    Route::post('/register',[LoginController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'admin_or_adv'])->group (function () {
    Route::get('/', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::post('/invoices/generate',         [InvoiceController::class, 'generate'])->name('invoices.generate');
    Route::get('/invoices/{id}/download',     [InvoiceController::class, 'downloadPdf'])->name('invoices.download');
    Route::delete('/invoices/{id}',           [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    Route::get('/invoices/{id}/edit',         [ManualInvoiceController::class, 'edit'])->name('invoices.edit');
    Route::put('/invoices/{id}',              [ManualInvoiceController::class, 'update'])->name('invoices.update');

    Route::get('/products',          [ProductController::class, 'index'])->name('products.index');
    Route::post('/products',         [ProductController::class, 'store'])->name('products.store');
    Route::delete('/products/{id}',  [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/clients/search', [ClientController::class, 'search'])->name('clients.search');
    Route::resource('clients', ClientController::class);

    Route::prefix('invoices/manual')->name('invoices.manual.')->group(function () {
        Route::get('/create',  [ManualInvoiceController::class, 'create'])->name('create');
        Route::post('/store',  [ManualInvoiceController::class, 'store'])->name('store');
    });
    Route::get('/creances', [CreanceController::class, 'index'])->name('creances.index');
    Route::get('/creances/export/excel', [CreanceController::class, 'exportExcel'])->name('creances.export.excel');
    Route::get('/creances/export/pdf', [CreanceController::class, 'exportPdf'])->name('creances.export.pdf');
    Route::get('/alertes', [AlerteController::class, 'index'])->name('alertes.index');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/users',                    [UserController::class, 'index'])->name('admin.users');
    Route::put('/admin/users/{user}/role',         [UserController::class, 'updateRole'])->name('admin.users.role'); Route::delete('/admin/users/{user}',           [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/admin/users/create',  [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users',        [UserController::class, 'store'])->name('admin.users.store');
    Route::post('/admin/users/{user}/approve', [UserController::class, 'approve'])->name('admin.users.approve');
    Route::post('/admin/users/{user}/reject',  [UserController::class, 'reject'])->name('admin.users.reject');
});


Route::middleware(['auth', 'admin'])->prefix('objectifs')->name('objectifs.')->group(function () {
    Route::get('/', [ObjectifVendeurController::class, 'index'])->name('index');
    Route::get('/create', [ObjectifVendeurController::class, 'create'])->name('create');
    Route::post('/', [ObjectifVendeurController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [ObjectifVendeurController::class, 'edit'])->name('edit');
    Route::put('/{id}', [ObjectifVendeurController::class, 'update'])->name('update');
    Route::delete('/{id}', [ObjectifVendeurController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('vendeurs',   VendeurController::class)->except(['show']);
    Route::resource('categories', CategorieController::class)->except(['show']);
});
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard-vendeurs', [DashboardVendeursController::class, 'index'])
        ->name('dashboard.vendeurs');
    Route::get('/dashboard-categories', [DashboardCategoriesController::class, 'index'])
        ->name('dashboard.categories');
    Route::get('/dashboard-vendeur-categorie', [DashboardVendeurCategorieController::class, 'index'])
        ->name('dashboard.vendeur-categorie');
});
