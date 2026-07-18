<?php

namespace App\Http\Requests\Activity;

use Illuminate\Foundation\Http\FormRequest;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'deal_id'      => ['nullable', 'integer', 'exists:deals,id'],
            'company_id'   => ['nullable', 'integer', 'exists:companies,id'],
            'contact_id'   => ['nullable', 'integer', 'exists:contacts,id'],
            'user_id'      => ['nullable', 'integer', 'exists:users,id'],
            'type'         => ['required', 'string', 'max:50'],
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'starts_at'    => ['nullable', 'date'],
            'ends_at'      => ['nullable', 'date', 'after:starts_at'],
            'completed_at' => ['nullable', 'date'],
            'priority'     => ['nullable', 'string', 'in:low,medium,high'],
            'status'       => ['nullable', 'string', 'in:pending,in_progress,completed,cancelled'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'      => 'O tipo da atividade é obrigatório.',
            'type.max'           => 'O tipo da atividade não pode ter mais de :max caracteres.',
            'title.required'     => 'O título da atividade é obrigatório.',
            'title.max'          => 'O título da atividade não pode ter mais de :max caracteres.',
            'deal_id.integer'    => 'O deal selecionado é inválido.',
            'deal_id.exists'     => 'O deal selecionado não existe.',
            'company_id.integer' => 'A empresa selecionada é inválida.',
            'company_id.exists'  => 'A empresa selecionada não existe.',
            'contact_id.integer' => 'O contato selecionado é inválido.',
            'contact_id.exists'  => 'O contato selecionado não existe.',
            'user_id.integer'    => 'O usuário selecionado é inválido.',
            'user_id.exists'     => 'O usuário selecionado não existe.',
            'ends_at.after'      => 'A data de término deve ser posterior à data de início.',
            'priority.in'        => 'A prioridade deve ser: low, medium ou high.',
            'status.in'          => 'O status deve ser: pending, in_progress, completed ou cancelled.',
        ];
    }
}
