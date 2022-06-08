<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CpfCnpj;

class UserExternoRequest extends FormRequest
{
    private $unique;
    private $regrasPassword;

    private function formataDados()
    {
        $all = $this->all();
        $all['cpf_cnpj'] = apenasNumeros(request()->cpf_cnpj);
        if(request()->filled('nome'))
            $all['nome'] = mb_strtoupper(request()->nome, 'UTF-8');

        $this->replace($all);
    }

    protected function prepareForValidation()
    {
        if(\Route::is('externo.cadastro.submit'))
            $this->merge([
                'verify_token' => str_random(32)
            ]);
    }

    public function rules()
    {
        $this->formataDados();
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
            'password.confirmed' => 'É necessário confirmar a senha',
            'password.regex' => 'A senha deve ter, no mínimo, 8 caracteres contendo um número, uma letra maiúscula e uma minúscula.',
            'unique' => 'CPF / CNPJ já cadastrado no Login Externo.',
            'string' => 'Deve ser texto',
            'password_confirmation.same' => 'As senhas precisam ser idênticas entre si.'
        ];
    }

    /**
     * Get the validated data from the request.
     *
     * @return array
     */
    public function validated()
    {
        $array = $this->validator->validated();
        if(isset($array))
            return $this->all();
    }
}
