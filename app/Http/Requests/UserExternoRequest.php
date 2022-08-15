<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CpfCnpj;

class UserExternoRequest extends FormRequest
{
    private $regrasPassword;

    protected function prepareForValidation()
    {
        $all = $this->all();
        if(request()->filled('cpf_cnpj'))
            $all['cpf_cnpj'] = apenasNumeros(request()->cpf_cnpj);
        if(request()->filled('nome'))
            $all['nome'] = mb_strtoupper(request()->nome, 'UTF-8');
        $this->replace($all);

        if(\Route::is('externo.cadastro.submit'))
            $this->merge([
                'verify_token' => str_random(32)
            ]);
    }

    public function rules()
    {
        $this->regrasPassword = class_basename(\Route::current()->controller) == 'UserExternoLoginController' ? '|max:191' : 
            '|confirmed|min:8|max:191|regex:/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])/u';

        return [
            'cpf_cnpj' => [
                'sometimes',
                'required',
                new CpfCnpj,
                \Route::is('externo.cadastro.submit') ? 'unique:users_externo,cpf_cnpj,NULL,id,deleted_at,NULL,ativo,1' : '',
            ],
            'nome' => 'sometimes|required|min:5|max:191|string',
            'email' => 'sometimes|required|email:rfc,filter|max:191',
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
            'string' => 'Deve ser no formato texto',
            'password_confirmation.same' => 'As senhas precisam ser idênticas entre si.',
            'email' => 'Deve ser um email válido',
            'sometimes' => 'Campo obrigatório',
            'cpf_cnpj.unique' => 'Esse CPF / CNPJ já está cadastrado e ativo no Login Externo',
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
