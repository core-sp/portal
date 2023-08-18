<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CpfCnpj;
use App\Rules\Cnpj;
use App\Rules\RecaptchaRequired;

class UserExternoRequest extends FormRequest
{
    private $regrasPassword;
    private $regrasUnique;

    protected function prepareForValidation()
    {
        $uniqueContabil = ['unique:contabeis,cnpj,NULL,id,deleted_at,NULL,ativo,1'];
        $uniqueComum = ['unique:users_externo,cpf_cnpj,NULL,id,deleted_at,NULL,ativo,1'];
        $uniqueComumNaoAtivo = ['unique:users_externo,cpf_cnpj'];
        $uniqueContabilNaoAtivo = ['unique:contabeis,cnpj'];

        $all = $this->all();
        if(request()->filled('cpf_cnpj'))
            $all['cpf_cnpj'] = apenasNumeros(request()->cpf_cnpj);
        if(request()->filled('nome'))
            $all['nome'] = mb_strtoupper(request()->nome, 'UTF-8');
        if(request()->filled('nome_contato'))
            $all['nome_contato'] = mb_strtoupper(request()->nome_contato, 'UTF-8');
        if(!request()->filled('tipo_conta'))
            $this->merge(['tipo_conta' => 'user_externo']);
        $this->replace($all);

        if(\Route::is('externo.editar'))
            auth()->guard('contabil')->check() ? $this->merge(['tipo_conta' => 'contabil', 'acao' => 'editar']) : 
            $this->merge(['tipo_conta' => 'user_externo', 'acao' => 'editar']);

        if(\Route::is('externo.cadastro.submit'))
        {
            foreach(['tipo_conta', 'password', 'cpf_cnpj', 'email', 'nome', 'aceite'] as $val)
                request()->missing($val) ? $this->merge([$val => null]) : null;

            request()->filled('cpf_cnpj') && (strlen($this->cpf_cnpj) == 14) && ($this->tipo_conta != 'contabil') ? $uniqueComum[1] = $uniqueContabilNaoAtivo[0] : null;
            $this->regrasUnique = $this->tipo_conta == 'contabil' ? array_merge($uniqueContabil, $uniqueComumNaoAtivo) : $uniqueComum;
        }
        else
            $this->regrasUnique = [];
    }

    public function rules()
    {
        $this->regrasPassword = class_basename(\Route::current()->controller) == 'UserExternoLoginController' ? '|max:191' : 
            '|confirmed|min:8|max:191|regex:/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])/u';
        
        return [
            'tipo_conta' => 'required|in:user_externo,contabil',
            'cpf_cnpj' => [
                'sometimes',
                'required',
                $this->tipo_conta == 'contabil' ? new Cnpj : new CpfCnpj,
                isset($this->regrasUnique[0]) ? $this->regrasUnique[0] : null,
                isset($this->regrasUnique[1]) ? $this->regrasUnique[1] : null,
            ],
            'nome' => 'sometimes|required|min:5|max:191|string',
            'email' => 'sometimes|required|email:rfc,filter|max:191',
            'password' => 'sometimes|required'.$this->regrasPassword,
            'password_confirmation' => 'sometimes|required|same:password|max:191',
            'password_atual' => 'sometimes|required',
            'aceite' => 'sometimes|required|accepted',
            'g-recaptcha-response' => \Route::is('externo.cadastro.submit') ? [new RecaptchaRequired, 'recaptcha'] : '',
            'nome_contato' => 'exclude_if:tipo_conta,user_externo,acao,editar|nullable|max:191|min:5|string',
            'telefone' => 'exclude_if:tipo_conta,user_externo,acao,editar|nullable|min:14|max:15|regex:/(\([0-9]{2}\))\s([0-9]{4,5})\-([0-9]{4,5})$/',
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
            'cpf_cnpj.unique' => 'Esse CPF / CNPJ já está cadastrado',
            'g-recaptcha-response.recaptcha' => 'ReCAPTCHA inválido',
            'in' => 'O :attribute possui valor inválido',
            'telefone.regex' => 'O telefone está no formato inválido',
        ];
    }

    public function validated()
    {
        $all = $this->validator->validated();
        unset($all['g-recaptcha-response']);

        return $all;
    }
}
