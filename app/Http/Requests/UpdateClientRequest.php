<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        // Authorization is handled by ClientPolicy
        return true;
    }

    /**
     * Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        return [
            'nom' => 'sometimes|required|string|max:255',
            'ice' => [
                'sometimes',
                'nullable',
                'string',
                Rule::unique('clients', 'ice')->ignore($this->route('client')),
            ],
            'if_number' => 'sometimes|nullable|string|max:50',
            'rc' => 'sometimes|nullable|string|max:50',
            'cnss' => 'sometimes|nullable|string|max:50',
            'telephone' => 'sometimes|nullable|string|max:20|regex:/^[0-9\s\-\+\.]+$/',
            'email' => 'sometimes|nullable|email|max:255',
            'adresse' => 'sometimes|nullable|string|max:255',
            'ville' => 'sometimes|nullable|string|max:100',
            'credit_limit' => 'sometimes|nullable|numeric|min:0',
            'payment_terms_days' => 'sometimes|nullable|integer|min:0|max:365',
        ];
    }

    /**
     * Get custom messages for validation rules
     */
    public function messages(): array
    {
        return [
            'nom.string' => 'Client name must be a string.',
            'nom.max' => 'Client name cannot exceed 255 characters.',
            'ice.unique' => 'This ICE number is already registered.',
            'telephone.regex' => 'Telephone number format is invalid.',
            'email.email' => 'Email must be a valid email address.',
            'credit_limit.numeric' => 'Credit limit must be a number.',
            'credit_limit.min' => 'Credit limit cannot be negative.',
            'payment_terms_days.integer' => 'Payment terms must be a number of days.',
            'payment_terms_days.max' => 'Payment terms cannot exceed 365 days.',
        ];
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('credit_limit')) {
            $this->merge(['credit_limit' => (float) $this->credit_limit]);
        }

        if ($this->filled('payment_terms_days')) {
            $this->merge(['payment_terms_days' => (int) $this->payment_terms_days]);
        }
    }
}
