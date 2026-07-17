<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCompanySettingRequest;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CompanySettingController extends Controller
{
    public function edit()
    {
        $company = CompanySetting::current();

        return view('pages.admin.company-settings.edit', compact('company'));
    }

    public function update(UpdateCompanySettingRequest $request)
    {
        $company = CompanySetting::current();
        $data    = $request->validated();

        if ($request->boolean('remove_logo') && $company->logo_path) {
            Storage::disk('public')->delete($company->logo_path);
            $data['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('company', 'public');
        }

        unset($data['logo'], $data['remove_logo']);

        $company->fill($data)->save();
        Cache::forget(CompanySetting::CACHE_KEY);

        return redirect()
            ->route('admin.company-settings.edit')
            ->with('success', 'Paramètres de la société mis à jour avec succès.');
    }
}
