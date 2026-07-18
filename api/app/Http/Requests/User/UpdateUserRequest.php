<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['sometimes', 'string', 'max:255'],
            'email'    => ['sometimes', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'avatar'   => ['nullable', 'url', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'role'     => ['nullable', 'string', 'in:admin,manager,seller'],
            'status'   => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.max'           => 'O nome não pode ter mais de :max caracteres.',
            'email.email'        => 'Informe um e-mail válido.',
            'email.unique'       => 'Este e-mail já está cadastrado.',
            'password.min'       => 'A senha deve ter no mínimo :min caracteres.',
            'password.confirmed' => 'A confirmação de senha não confere.',
            'role.in'            => 'O papel deve ser: admin, manager ou seller.',
        ];
    }
}
