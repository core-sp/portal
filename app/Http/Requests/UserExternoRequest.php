<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CpfCnpj;

class UserExternoRequest extends FormRequest
{
    private $unique;
    private $regrasPassword;

    public function rules()
    {
        $this->unique = \Route::is('externo.cadastro.submit') ? 'unique:users_externo,cpf_cnpj,NULL,id,deleted_at,NULL' : '';
        $this->regrasPassword = class_basename(\Route::current()->controller) == 'UserExternoLoginController' ? '|max:191' : 
            '|confirmed|min:8|max:191|regex:/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])/u';

        return [
            'cpf_cnpj' => [
                'sometimes',
                'required',
                new CpfCnpj,
                $this->unique
            ],
            'nome' => 'sometimes|required|min:5|max:191|string',
            'email' => 'sometimes|required|email|max:191',
            'password' => 'sometimes|required'.$this->regrasPassword,
            'password_confirmation' => 'sometimes|required|same:password|max:191',
            'password_atual' => 'sometimes|required',
            'aceite' => 'sometimes|required|accepted',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido',
            'min' => 'O campo possui menos caracteres que o mínimo necessário',
            'password.min' => 'A senha precisa ter, no mínimo, 8 caracteres.',
            'password.confirmed' => 'É necessário confirmar o password',
            'password.regex' => 'A senha deve ter, no mínimo, 8 caracteres contendo um número, uma letra maiúscula e uma minúscula.',
            'unique' => 'Já existe esse CPF / CNPJ cadastrado no Login Externo.',
            'string' => 'Deve ser texto',
            'password_confirmation.same' => 'As senhas precisam ser idênticas entre si.'
        ];
    }
}
