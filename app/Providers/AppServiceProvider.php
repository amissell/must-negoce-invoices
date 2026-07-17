<?php

namespace App\Providers;

use App\View\Composers\CompanySettingComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer(
            [
                'pages.invoices.pdf',
                'pages.invoices.pdf-manual',
                'pages.invoices.templates.*',
                'pages.admin.company-settings.*',
                'layouts.app',
            ],
            CompanySettingComposer::class
        );
    }
}
