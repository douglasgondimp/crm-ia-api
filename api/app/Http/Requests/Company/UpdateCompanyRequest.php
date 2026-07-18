<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'document' => ['nullable', 'string', 'max:20', 'unique:companies,document'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],
            'segment' => ['nullable', 'string', 'max:100'],
            'employees' => ['nullable', 'integer', 'min:0'],
            'annual_revenue' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'zipcode' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'district' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:2'],
            'country' => ['nullable', 'string', 'max:2'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'O nome da empresa não pode ter mais de :max caracteres.',
            'document.unique' => 'Este documento já está cadastrado.',
            'email.email' => 'Informe um e-mail válido.',
            'website.url' => 'Informe uma URL válida.',
            'employees.integer' => 'O número de funcionários deve ser um número inteiro.',
            'employees.min' => 'O número de funcionários não pode ser negativo.',
            'annual_revenue.numeric' => 'A receita anual deve ser um número.',
            'annual_revenue.min' => 'A receita anual não pode ser negativa.',
        ];
    }
}
