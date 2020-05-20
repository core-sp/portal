<?php

namespace App\Http\Requests;

use App\Traits\ControleAcesso;
use Illuminate\Foundation\Http\FormRequest;

class RegionalRequest extends FormRequest
{
    use ControleAcesso;

    public function authorize()
    {
        return $this->autoriza('RegionalController', 'edit');
    }

    public function rules()
    {
        return [
            'regional' => 'required|max:191',
            'email' => 'required|max:191',
            'endereco' => 'required|max:191',
            'bairro' => 'required|max:191',
            'numero' => 'required|max:191',
            'cep' => 'required|max:191',
            'telefone' => 'required|max:191',
            'funcionamento' => 'required|max:191',
            'ageporhorario' => 'required|regex:/^[1-9]+$/',
            'descricao' => 'required',
            'complemento' => 'max:191',
            'fax' => 'max:191',
            'responsavel' => 'max:191'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido',
            'ageporhorario.regex' => 'O valor deve ser maior que 0'
        ];
    }
}
