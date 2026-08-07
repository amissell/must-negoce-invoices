<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        // Authorization is handled by VendorPolicy
        return true;
    }

    /**
     * Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
            'phone' => 'sometimes|string|max:20|regex:/^[0-9\s\-\+\.]+$/',
            'address' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:100',
            'tax_id' => 'sometimes|string|unique:vendeurs,tax_id,' . $this->route('vendor'),
            'bank_account' => 'sometimes|string|max:50',
            'payment_terms_days' => 'sometimes|integer|min:0|max:365',
            'status' => 'sometimes|string|in:active,inactive',
        ];
    }

    /**
     * Get custom messages for validation rules
     */
    public function messages(): array
    {
        return [
            'name.string' => 'Vendor name must be a string.',
            'name.max' => 'Vendor name cannot exceed 255 characters.',
            'email.email' => 'Email must be a valid email address.',
            'phone.regex' => 'Phone number format is invalid.',
            'tax_id.unique' => 'This tax ID is already registered.',
            'payment_terms_days.integer' => 'Payment terms must be a number.',
            'payment_terms_days.max' => 'Payment terms cannot exceed 365 days.',
            'status.in' => 'Status must be either active or inactive.',
        ];
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('payment_terms_days')) {
            $this->merge(['payment_terms_days' => (int) $this->payment_terms_days]);
        }
    }
}
