<?php

namespace App\Http\Requests;

use App\Agendamento;
use App\Rules\Cpf;
use Illuminate\Foundation\Http\FormRequest;

class AgendamentoUpdateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'nome' => 'sometimes|required|max:191',
            'email' => 'sometimes|required|email|max:191',
            'cpf' => ['sometimes', 'required', 'max:14', new Cpf],
            'celular' => 'sometimes|required|max:17',
            'tiposervico' => 'sometimes|required|max:191',
            'idusuario' => 'sometimes|required_if:status,==,'.Agendamento::STATUS_COMPARECEU,
            'status' => 'sometimes|nullable|in:'.Agendamento::STATUS_COMPARECEU.','.Agendamento::STATUS_NAO_COMPARECEU.','.Agendamento::STATUS_CANCELADO,
            'idagendamento' => 'sometimes|required_without_all:nome,email,cpf,celular,tiposervico,idusuario'
        ];
    }

    public function messages() 
    {
        return [
            'max' => 'O campo :attribute excedeu o limite de :max caracteres',
            'required' => 'O campo :attribute é obrigatório',
            'email' => 'Email inválido',
            'idusuario.required_if' => 'Informe o atendente que realizou o atendimento',
            'status.in' => 'Opção inválida de status'
        ];
    }
}
