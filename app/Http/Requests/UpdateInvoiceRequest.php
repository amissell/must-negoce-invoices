<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        // Authorization is handled by InvoicePolicy
        return true;
    }

    /**
     * Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        return [
            'invoice_number' => 'sometimes|string|unique:invoices,invoice_number,' . $this->route('invoice'),
            'invoice_date' => 'sometimes|date',
            'client_id' => 'sometimes|integer|exists:clients,id',
            'client_name' => 'sometimes|string|max:255',
            'client_address' => 'sometimes|string|max:255',
            'client_phone' => 'sometimes|string|max:20',
            'client_email' => 'sometimes|email',
            'client_ice' => 'sometimes|string|max:50',
            'montant_ht' => 'sometimes|numeric|min:0',
            'tva_rate' => 'sometimes|numeric|min:0|max:100',
            'remise_percentage' => 'sometimes|numeric|min:0|max:100',
            'payment_mode' => 'sometimes|string|in:cash,check,transfer,credit',
            'type' => 'sometimes|string|in:manual,automatic',
            'is_locked' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validation rules
     */
    public function messages(): array
    {
        return [
            'invoice_number.unique' => 'This invoice number is already used.',
            'invoice_date.date' => 'Invoice date must be a valid date.',
            'client_id.exists' => 'The selected client does not exist.',
            'client_email.email' => 'Client email must be a valid email address.',
            'montant_ht.numeric' => 'Amount must be a number.',
            'montant_ht.min' => 'Amount must be at least 0.',
            'tva_rate.numeric' => 'VAT rate must be a number.',
            'tva_rate.max' => 'VAT rate cannot exceed 100%.',
            'remise_percentage.numeric' => 'Discount percentage must be a number.',
            'remise_percentage.max' => 'Discount percentage cannot exceed 100%.',
            'type.in' => 'Invoice type must be either manual or automatic.',
        ];
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation(): void
    {
        // Convert tva_rate and remise_percentage to decimal format
        if ($this->has('tva_rate')) {
            $this->merge(['tva_rate' => (float) $this->tva_rate]);
        }

        if ($this->has('remise_percentage')) {
            $this->merge(['remise_percentage' => (float) $this->remise_percentage]);
        }

        if ($this->has('montant_ht')) {
            $this->merge(['montant_ht' => (float) $this->montant_ht]);
        }
    }
}
