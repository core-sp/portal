<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgendamentoUpdateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|max:191',
            'email' => 'required|email|max:191',
            'cpf' => 'required|max:191',
            'celular' => 'required|max:191',
            'tiposervico' => 'required|max:191',
            'idusuario' => 'max:191|required_if:status,==,Compareceu',
            'status' => 'max:191',
        ];
    }

    public function messages() 
    {
        return [
            'nome.required' => 'O Nome é obrigatório',
            'nome.max' => 'O Nome excedeu o limite de caracteres permitido',
            'email.required' => 'O Email é obrigatório',
            'email.max' => 'O Email excedeu o limite de caracteres permitido',
            'email.email' => 'Email inválido',
            'cpf.required' => 'O CPF é obrigatório',
            'cpf.max' => 'O CPF excedeu o limite de caracteres permitido',
            'celular.required' => 'O Celular é obrigatório',
            'celular.max' => 'O Celular excedeu o limite de caracteres permitido',
            'idusuario.required_if' => 'Informe o atendente que realizou o atendimento',
            'idusuario.max' => 'O ID do usuário excedeu o limite de caracteres permitido',
            'status.max' => 'O Status excedeu o limite de caracteres permitido'
        ];
    }

    public function toModel()
    {
        return [
            'nome' => mb_convert_case(mb_strtolower($this->nome), MB_CASE_TITLE),
            'cpf' => $this->cpf,
            'email' => $this->email,
            'celular' => $this->celular,
            'tiposervico' => $this->tiposervico,
            'idusuario' => $this->idusuario,
            'status' => $this->status

        ];
    }
}
