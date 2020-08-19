<?php

namespace App\Http\Requests;

use App\Rules\Cpf;
use Illuminate\Foundation\Http\FormRequest;

class AgendamentoSiteRequest extends FormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|max:191',
            'cpf' => ['required', 'max:191', new Cpf],
            'email' => 'required|email|max:191',
            'celular' => 'max:191|min:14',
            'dia' => 'required',
            'hora' => 'required|max:191',
        ];
    }

    public function messages() 
    {
        return [
            'nome.required' => 'O Nome é obrigatório',
            'nome.max' => 'O Nome excedeu o limite de caracteres permitido',
            'cpf.required' => 'O CPF é obrigatório',
            'cpf.max' => 'O CPF excedeu o limite de caracteres permitido',
            'email.required' => 'O Email é obrigatório',
            'email.max' => 'O Email excedeu o limite de caracteres permitido',
            'email.email' => 'Email inválido',
            'celular.min' => 'Número inválido',
            'celular.max' => 'O Celular excedeu o limite de caracteres permitido',
            'dia.required' => 'Informe o dia do atendimento',
            'hora.required' => 'Informe o horário do atendimento',
            'hora.max' => 'O Horário excedeu o limite de caracteres permitido',
        ];
    }
    
    public function toModel()
    {
        return [
            'nome' => mb_convert_case(mb_strtolower($this->nome), MB_CASE_TITLE),
            'cpf' => $this->cpf,
            'email' => $this->email,
            'celular' => $this->celular,
            'dia' => date('Y-m-d', strtotime(str_replace('/', '-', $this->dia))), 
            'hora' => $this->hora,
            'tiposervico' => $this->servico . ' para ' . $this->pessoa,
            'idregional' => $this->idregional,
            'protocolo' => $this->protocolo
        ];
    }
}
