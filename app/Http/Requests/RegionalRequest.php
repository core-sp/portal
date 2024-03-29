<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegionalRequest extends FormRequest
{
    public function rules()
    {
        return [
            'regional' => 'required|max:191',
            'email' => 'required|email|max:191',
            'endereco' => 'required|max:191',
            'bairro' => 'required|max:191',
            'numero' => 'required|max:10',
            'complemento' => 'max:50',
            'cep' => 'required|max:20',
            'telefone' => 'required|max:30',
            'fax' => 'max:30',
            'funcionamento' => 'required|max:191',
            'responsavel' => 'max:191',
            'ageporhorario' => 'required|regex:/^[1-9]+$/',
            'horariosage' => 'array',
            'horariosage.*' => 'distinct',
            'descricao' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O campo :attribute é obrigatório',
            'email' => 'Formato de email inválido',
            'max' => 'O campo :attribute deve ter no máximo :max caracteres',
            'ageporhorario.regex' => 'O valor deve ser maior que 0',
            'array' => 'Os horários não vieram da forma correta',
            'distinct' => 'Existe hora repetida'
        ];
    }
}
