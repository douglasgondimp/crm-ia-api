<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => ['nullable', 'uuid', 'exists:companies,uuid'],
            'name' => ['sometimes', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'birthday' => ['nullable', 'date'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'decision_maker' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'O nome do contato não pode ter mais de :max caracteres.',
            'company_id.uuid' => 'A empresa selecionada é inválida.',
            'company_id.exists' => 'A empresa selecionada não existe.',
            'email.email' => 'Informe um e-mail válido.',
            'linkedin.url' => 'Informe uma URL válida para o LinkedIn.',
            'instagram.url' => 'Informe uma URL válida para o Instagram.',
        ];
    }
}
