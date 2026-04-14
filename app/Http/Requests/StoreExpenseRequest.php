<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'signer_pernr' => ['required', 'string', 'max:20'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'admin_pernr' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'signer_pernr.required' => 'El firmante es obligatorio.',
        ];
    }
}