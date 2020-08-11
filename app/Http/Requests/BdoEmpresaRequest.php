<?php

namespace App\Http\Requests;

use App\Rules\Cnpj;
use Illuminate\Foundation\Http\FormRequest;

class BdoEmpresaRequest extends FormRequest
{
    public function rules()
    {
        return [
            'segmento' => 'max:191',
            'cnpj' => ['required', 'unique:bdo_empresas,cnpj,' . $this->id . ',idempresa', 'max:191', new Cnpj],
            'razaosocial' => 'required|max:191',
            'capitalsocial' => 'max:191',
            'endereco' => 'required|max:191',
            'descricao' => 'required',
            'email' => 'required|email|max:191',
            'telefone' => 'required|max:191',
            'site' => 'max:191',
            'contatonome' => 'max:191',
            'contatotelefone' => 'max:191',
            'contatoemail' => 'email|max:191|nullable'
        ];
    }

    public function messages()
    {
        return [
            'cnpj.unique' => 'Já existe uma empresa cadastrada com este CNPJ',
            'cnpj.max' => 'O CNPJ excedeu o limite de caracteres permitido',
            'cnpj.required' => 'O CNPJ é obrigatório',
            'razaosocial.max' => 'A Razão Social excedeu o limite de caracteres permitido',
            'razaosocial.required' => 'A Razão Social é obrigatória',
            'descricao.required' => 'A Descrição é obrigatória',
            'email.max' => 'O Email excedeu o limite de caracteres permitido',
            'email.required' => 'O Email é obrigatório',
            'telefone.required' => 'O Telefone é obrigatório',
            'telefone.max' => 'O Telefone excedeu o limite de caracteres permitido',
            'site.max' => 'O Site excedeu o limite de caracteres permitido',
            'endereco.required' => 'O Endereço é obrigatório',
            'endereco.max' => 'O Endereço excedeu o limite de caracteres permitido',
            'contatonome.max' => 'O Nome excedeu o limite de caracteres permitido',
            'contatotelefone.max' => 'O Telefone excedeu o limite de caracteres permitido',
            'contatoemail.max' => 'O Email excedeu o limite de caracteres permitido',
            'required' => ':attribute é obrigatório',
            'max' => ':attribute excedeu o limite de caracteres permitido',
            'email' => 'Email inválido'       
        ];
    }

    public function toModel()
    {
        return [
            'segmento' => $this->segmento, 
            'cnpj' => $this->cnpj, 
            'razaosocial' => $this->razaosocial, 
            'fantasia' => $this->fantasia, 
            'descricao' => $this->descricao, 
            'capitalsocial' => $this->capitalsocial,
            'endereco' => $this->endereco, 
            'site' => $this->site, 
            'email' => $this->email, 
            'telefone' => $this->telefone, 
            'contatonome' => $this->contatonome, 
            'contatotelefone' => $this->contatotelefone, 
            'contatoemail' => $this->contatoemail, 
            'idusuario' => $this->idusuario
        ];
    }
}
