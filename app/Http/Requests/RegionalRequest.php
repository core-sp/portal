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
            'regional' => 'required|',
            'email' => 'required|',
            'endereco' => 'required|',
            'bairro' => 'required|',
            'numero' => 'required|',
            'cep' => 'required|',
            'telefone' => 'required|',
            'funcionamento' => 'required|',
            'ageporhorario' => 'required|regex:/^[1-9]+$/',
            'descricao' => 'required'
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
