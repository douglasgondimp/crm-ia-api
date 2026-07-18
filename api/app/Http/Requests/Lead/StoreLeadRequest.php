<?php

namespace App\Http\Requests\Lead;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'company' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:50'],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'temperature' => ['nullable', 'string', 'in:cold,warm,hot'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'observations' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do lead é obrigatório.',
            'name.max' => 'O nome do lead não pode ter mais de :max caracteres.',
            'email.email' => 'Informe um e-mail válido.',
            'phone.max' => 'O telefone não pode ter mais de :max caracteres.',
            'company.max' => 'A empresa não pode ter mais de :max caracteres.',
            'source.max' => 'A origem não pode ter mais de :max caracteres.',
            'status.max' => 'O status não pode ter mais de :max caracteres.',
            'score.integer' => 'A pontuação deve ser um número inteiro.',
            'score.min' => 'A pontuação mínima é :min.',
            'score.max' => 'A pontuação máxima é :max.',
            'temperature.in' => 'A temperatura deve ser: cold, warm ou hot.',
            'assigned_to.uuid' => 'O usuário atribuído é inválido.',
            'assigned_to.exists' => 'O usuário atribuído não existe.',
        ];
    }
}
