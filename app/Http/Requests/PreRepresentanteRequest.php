<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CpfCnpj;

class PreRepresentanteRequest extends FormRequest
{
    public function rules()
    {
        return [
            'cpf_cnpj' => [
                'sometimes',
                'required',
                new CpfCnpj,
                'regex:/^\d+$/',
            ],
            'cpf_cnpj_cad' => [
                'sometimes',
                'required', 
                new CpfCnpj, 
                'regex:/^\d+$/',
                'unique:pre_representantes,cpf_cnpj,NULL,id,deleted_at,NULL',
                'unique:representantes,cpf_cnpj,NULL,id,deleted_at,NULL'
            ],
            'nome' => 'sometimes|required|min:5|max:191',
            'email' => 'sometimes|required|email|max:191',
            'password_login' => 'sometimes|required|max:191',
            'password' => 'sometimes|required|confirmed|min:8|max:191|regex:/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])/u',
            'password_confirmation' => 'sometimes|required|same:password|max:191',
            'checkbox-tdu' => 'sometimes|required|accepted',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido',
            'min' => 'O campo possui menos caracteres que o mínimo necessário',
            'password.min' => 'A senha precisa ter, no mínimo, 8 caracteres.',
            'password.confirmed' => 'As senhas precisam ser idênticas entre si.',
            'password.regex' => 'A senha deve ter, no mínimo, 8 caracteres contendo um número, uma letra maiúscula e uma minúscula.',
            'cpf_cnpj.regex' => 'Somente números devem ser inseridos.',
            'cpf_cnpj_cad.regex' => 'Somente números devem ser inseridos.',
            'cpf_cnpj_cad.unique' => 'Já existe esse CPF / CNPJ cadastrado no Pré-registro ou como Representante Comercial.',
        ];
    }
}