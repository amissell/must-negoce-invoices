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
    public function messages(): array
    {
        return [
            'type.in' => 'Invoice type must be manual.',
        ];
    }

    public function rules(): array
    {
        return [
            'invoice_number'     => 'sometimes|required|string|max:255',
            'invoice_date'       => 'sometimes|required|date',
            'payment_mode'       => 'sometimes|required|in:CH,ESP,LCN,VIR',
            'client_id'          => 'sometimes|nullable|exists:clients,id',
            'client_name'        => 'sometimes|nullable|string|max:255',
            'client_address'     => 'sometimes|nullable|string|max:500',
            'client_phone'       => 'sometimes|nullable|string|max:50',
            'client_ice'         => 'sometimes|nullable|string|max:50',
            'montant_ht'         => 'sometimes|numeric|min:0',
            'tva_rate'           => 'sometimes|numeric|min:0|max:100',
            'remise_percentage'  => 'sometimes|numeric|min:0|max:100',
            'items'              => 'sometimes|required|array|min:1',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity'   => 'required_with:items|integer|min:1',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
            'type'               => 'sometimes|in:manual',
        ];
    }
}
