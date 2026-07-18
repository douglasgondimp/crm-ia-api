<?php

namespace App\Http\Requests\Deal;

use Illuminate\Foundation\Http\FormRequest;

class StoreDealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'contact_id' => ['nullable', 'integer', 'exists:contacts,id'],
            'pipeline_stage_id' => ['nullable', 'integer', 'exists:pipeline_stages,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'expected_close_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:open,won,lost'],
            'won_at' => ['nullable', 'date'],
            'lost_reason' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título do deal é obrigatório.',
            'title.max' => 'O título do deal não pode ter mais de :max caracteres.',
            'company_id.integer' => 'A empresa selecionada é inválida.',
            'company_id.exists' => 'A empresa selecionada não existe.',
            'contact_id.integer' => 'O contato selecionado é inválido.',
            'contact_id.exists' => 'O contato selecionado não existe.',
            'pipeline_stage_id.integer' => 'O estágio do pipeline selecionado é inválido.',
            'pipeline_stage_id.exists' => 'O estágio do pipeline selecionado não existe.',
            'assigned_to.integer' => 'O usuário atribuído é inválido.',
            'assigned_to.exists' => 'O usuário atribuído não existe.',
            'value.numeric' => 'O valor deve ser um número.',
            'value.min' => 'O valor não pode ser negativo.',
            'status.in' => 'O status deve ser: open, won ou lost.',
        ];
    }
}
