<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CpfCnpj;

class PreRepresentanteRequest extends FormRequest
{
    public function rules()
    {
        return [
            'cpfCnpj' => ['required', new CpfCnpj, 'unique:pre_representantes,cpf_cnpj,NULL,id,deleted_at,NULL'],
            'nome' => 'sometimes|min:5|max:191',
            'email' => 'sometimes|email|max:191',
            'password' => 'sometimes|confirmed|min:8|regex:/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])/u',
            'password_confirmation' => 'sometimes|same:password',
            'checkbox-tdu' => 'accepted'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'sometimes' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido',
            'min' => 'O campo possui menos caracteres que o mínimo necessário',
            'password.min' => 'A senha precisa ter, no mínimo, 8 caracteres.',
            'password.confirmed' => 'As senhas precisam ser idênticas entre si.',
            'password.regex' => 'A senha deve conter um número, uma letra maiúscula e uma minúscula.'
        ];
    }
}
