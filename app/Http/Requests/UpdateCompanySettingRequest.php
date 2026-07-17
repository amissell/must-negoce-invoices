<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanySettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name'                 => ['required', 'string', 'max:255'],
            'logo'                 => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'address'              => ['nullable', 'string', 'max:500'],
            'phone'                => ['nullable', 'string', 'max:50'],
            'email'                => ['nullable', 'email', 'max:255'],
            'website'              => ['nullable', 'string', 'max:255'],
            'ice'                  => ['nullable', 'string', 'max:50'],
            'if_number'            => ['nullable', 'string', 'max:50'],
            'rc'                   => ['nullable', 'string', 'max:50'],
            'patente'              => ['nullable', 'string', 'max:50'],
            'cnss'                 => ['nullable', 'string', 'max:50'],
            'tva_rate'             => ['required', 'numeric', 'min:0', 'max:100'],
            'currency'             => ['required', 'string', 'max:10'],
            'invoice_prefix'       => ['required', 'string', 'max:20'],
            'footer_text'          => ['nullable', 'string', 'max:2000'],
            'legal_mentions'       => ['nullable', 'string', 'max:5000'],
            'default_pdf_template' => ['required', 'in:classic,modern'],
            'remove_logo'          => ['nullable', 'boolean'],
        ];
    }
}
