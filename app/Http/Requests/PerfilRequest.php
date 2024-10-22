<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PerfilRequest extends FormRequest
{
    private $regras;

    public function authorize()
    {
        return auth()->user()->can('onlyAdmin', auth()->user());
    }

    protected function prepareForValidation()
    {
        if(\Route::is('perfis.permissoes.put'))
            $this->regras = $this->id == 24 ? 'nullable' : 'required';
    }

    public function rules()
    {
        if(\Route::is('perfis.permissoes.put'))
            return [
                'permissoes' => $this->regras . '|array',
                'permissoes.*' => 'exists:permissoes,idpermissao|distinct'
            ];

        return [
            'nome' => 'required|unique:perfis,nome|min:4|max:191',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Campo :attribute é obrigatório',
            'permissoes.required' => 'É obrigatório incluir ao menos uma permissão',
            'unique' => 'Este perfil já existe, seja em uso ou excluído',
            'max' => 'O campo :attribute excedeu o limite de :max caracteres',
            'min' => 'O campo :attribute tem menos de :min caracteres obrigatórios',
            'permissoes.*.distinct' => 'Não pode incluir permissões repetidas',
            'permissoes.*.exists' => 'Não pode incluir permissão que não existe',
            'permissoes.array' => 'Formato errado de envio das permissões',
        ];
    }
}
