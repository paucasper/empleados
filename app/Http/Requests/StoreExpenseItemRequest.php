<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'expense_type' => [
                'required',
                'string',
                Rule::in([
                    'kilometraje',
                    'otros_gastos',
                    'media_dieta',
                    'dieta_completa',
                ]),
            ],
            'expense_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'numeric', 'min:0.01'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'is_card_payment' => ['nullable', 'boolean'],
            'ticket' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $type = $this->input('expense_type');

            if ($type === 'otros_gastos' && !$this->filled('amount')) {
                $validator->errors()->add('amount', 'El importe es obligatorio para "Otros gastos".');
            }

            if ($type !== 'otros_gastos' && !$this->filled('quantity')) {
                $validator->errors()->add('quantity', 'La cantidad es obligatoria para este tipo de gasto.');
            }
        });
    }
}