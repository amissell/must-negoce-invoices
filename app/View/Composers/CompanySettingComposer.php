<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Models\CompanySetting;
use Illuminate\View\View;

class CompanySettingComposer
{
    public function compose(View $view): void
    {
        $view->with('company', CompanySetting::current());
    }
}
