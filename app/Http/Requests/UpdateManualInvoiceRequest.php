<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateManualInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

public function rules(): array
{
    return [
        // invoice_number is immutable and no longer accepted on update.
        'invoice_date'       => 'required|date',
        'payment_mode'       => 'required|string|max:50',
        'client_id'          => 'nullable|exists:clients,id',
        'client_name'        => 'nullable|string|max:255',
        'client_address'     => 'nullable|string|max:500',
        'client_phone'       => 'nullable|string|max:50',
        'client_ice'         => 'nullable|string|max:50',
        'remise_percentage'  => 'nullable|numeric|min:0|max:100',
        'items'              => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity'   => 'required|integer|min:1',
        'items.*.unit_price' => 'required|numeric|min:0',
    ];
}
}
