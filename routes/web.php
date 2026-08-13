<?php
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ManualInvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\CompanyTargetController;
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
use App\Http\Controllers\ExecutiveDashboardController;
use App\Http\Controllers\BusinessAnalyticsController;
use App\Http\Controllers\CommercialAnalyticsController;
use App\Http\Controllers\ProfitabilityAnalyticsController;
use App\Http\Controllers\Admin\ActivityLogController;


Route::middleware('guest')->group(function () {
    Route::get('/login',    [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login',   [LoginController::class, 'login']);
    Route::get('/register', [LoginController::class, 'showRegisterForm'])->name('register');
    Route::post('/register',[LoginController::class, 'register']);
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'approved', 'admin_or_adv'])->group (function () {
    Route::get('/', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.list');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::post('/invoices/generate',         [InvoiceController::class, 'generate'])->name('invoices.generate');
    Route::get('/invoices/{id}/download',     [InvoiceController::class, 'downloadPdf'])->name('invoices.download');
    Route::delete('/invoices/{id}',           [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    Route::get('/invoices/{id}/edit',         [ManualInvoiceController::class, 'edit'])->name('invoices.edit');
    Route::put('/invoices/{id}',              [ManualInvoiceController::class, 'update'])->name('invoices.update');
    Route::patch('/invoices/{id}',            [ManualInvoiceController::class, 'update'])->name('invoices.update.patch');

    Route::get('/products',          [ProductController::class, 'index'])->name('products.index');
    Route::post('/products',         [ProductController::class, 'store'])->name('products.store');
    Route::delete('/products/{id}',  [ProductController::class, 'destroy'])->name('products.destroy');
    Route::prefix('invoices/manual')->name('invoices.manual.')->group(function () {
        Route::get('/create',  [ManualInvoiceController::class, 'create'])->name('create');
        Route::post('/store',  [ManualInvoiceController::class, 'store'])->name('store');
    });
    Route::get('/creances', [CreanceController::class, 'index'])->name('creances.index');
    Route::get('/creances/export/excel', [CreanceController::class, 'exportExcel'])->name('creances.export.excel');
    Route::get('/creances/export/pdf', [CreanceController::class, 'exportPdf'])->name('creances.export.pdf');
    Route::get('/alertes', [AlerteController::class, 'index'])->name('alertes.index');
});
Route::middleware(['auth', 'approved'])->group(function () {
    Route::get('/clients/search', [ClientController::class, 'search'])->name('clients.search');
    Route::resource('clients', ClientController::class);
});
Route::middleware(['auth', 'executive'])->group(function () {
    Route::get('/business-analytics', [BusinessAnalyticsController::class, 'index'])
        ->name('business-analytics.index');
    Route::get('/api/business-analytics', [BusinessAnalyticsController::class, 'data'])
        ->name('api.business-analytics');
    Route::get('/business-analytics/commercial', [CommercialAnalyticsController::class, 'index'])
        ->name('business-analytics.commercial.index');
    Route::get('/api/business-analytics/commercial', [CommercialAnalyticsController::class, 'data'])
        ->name('api.business-analytics.commercial');
    Route::get('/business-analytics/profitability', [ProfitabilityAnalyticsController::class, 'index'])
        ->name('business-analytics.profitability.index');
    Route::get('/api/business-analytics/profitability', [ProfitabilityAnalyticsController::class, 'data'])
        ->name('api.business-analytics.profitability');
    Route::get('/api/business-analytics/profitability/details', [ProfitabilityAnalyticsController::class, 'details'])
        ->name('api.business-analytics.profitability.details');
    Route::get('/api/business-analytics/profitability/details/export', [ProfitabilityAnalyticsController::class, 'detailsExport'])
        ->name('api.business-analytics.profitability.details.export');
    Route::get('/api/business-analytics/profitability/export', [ProfitabilityAnalyticsController::class, 'export'])
        ->name('api.business-analytics.profitability.export');
    Route::get('/dashboard/executive', [ExecutiveDashboardController::class, 'index'])
        ->name('dashboard.executive');
    Route::get('/dashboard/executive/board.pdf', [ExecutiveDashboardController::class, 'boardPdf'])
        ->name('dashboard.executive.board_pdf');
    Route::get('/dashboard/executive/data', [ExecutiveDashboardController::class, 'data'])
        ->name('dashboard.executive.data');
    Route::get('/api/dashboard', [ExecutiveDashboardController::class, 'data'])
        ->name('api.dashboard');
    Route::get('/api/kpis/sales-pulse', [ExecutiveDashboardController::class, 'salesPulse'])
        ->name('api.kpis.sales_pulse');
    Route::get('/api/kpis/ceo-scorecard', [ExecutiveDashboardController::class, 'ceoScorecard'])
        ->name('api.kpis.ceo_scorecard');
    Route::get('/api/kpis/gross-margin', [ExecutiveDashboardController::class, 'grossMargin'])
        ->name('api.kpis.gross_margin');
    Route::get('/api/kpis/tva-liability', [ExecutiveDashboardController::class, 'tvaLiability'])
        ->name('api.kpis.tva_liability');
    Route::get('/api/kpis/margins/details', [ExecutiveDashboardController::class, 'marginDetails'])
        ->name('api.kpis.margins_details');
    Route::get('/api/kpis/margins/export', [ExecutiveDashboardController::class, 'marginExport'])
        ->name('api.kpis.margins_export');
    Route::get('/api/dashboard/margin-export', [ExecutiveDashboardController::class, 'marginExport'])
        ->name('api.dashboard.margin_export');
    Route::get('/api/kpis/tva/details', [ExecutiveDashboardController::class, 'tvaDetails'])
        ->name('api.kpis.tva_details');
    Route::get('/api/kpis/tva/export', [ExecutiveDashboardController::class, 'tvaExport'])
        ->name('api.kpis.tva_export');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/users',                    [UserController::class, 'index'])->name('admin.users');
    Route::put('/admin/users/{user}/role',         [UserController::class, 'updateRole'])->name('admin.users.role');
    Route::delete('/admin/users/{user}',           [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/admin/users/create',  [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users',        [UserController::class, 'store'])->name('admin.users.store');
    Route::post('/admin/users/{user}/approve', [UserController::class, 'approve'])->name('admin.users.approve');
    Route::post('/admin/users/{user}/reject',  [UserController::class, 'reject'])->name('admin.users.reject');
    // Scheduled reports management
    Route::get('/admin/scheduled-reports', [\App\Http\Controllers\ScheduledReportController::class, 'index'])->name('admin.scheduled_reports.index');
    Route::post('/admin/scheduled-reports', [\App\Http\Controllers\ScheduledReportController::class, 'store'])->name('admin.scheduled_reports.store');
    Route::delete('/admin/scheduled-reports/{scheduledReport}', [\App\Http\Controllers\ScheduledReportController::class, 'destroy'])->name('admin.scheduled_reports.destroy');
    Route::post('/admin/scheduled-reports/{scheduledReport}/run', [\App\Http\Controllers\ScheduledReportController::class, 'runNow'])->name('admin.scheduled_reports.run');
    Route::get('/admin/scheduled-reports/runs/{run}/download', [\App\Http\Controllers\ScheduledReportDownloadController::class, 'downloadRun'])->name('admin.scheduled_reports.download');
    Route::get('/admin/company-targets', [CompanyTargetController::class, 'index'])
        ->name('admin.company-targets.index');
    Route::put('/admin/company-targets', [CompanyTargetController::class, 'upsert'])
        ->name('admin.company-targets.upsert');
    Route::delete('/admin/company-targets/{companyTarget}', [CompanyTargetController::class, 'destroy'])
        ->name('admin.company-targets.destroy');
    Route::get('/admin/company-settings',  [CompanySettingController::class, 'edit'])->name('admin.company-settings.edit');
    Route::put('/admin/company-settings',  [CompanySettingController::class, 'update'])->name('admin.company-settings.update');
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

// (development-only test route removed)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('activity-logs/{activity}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
});
