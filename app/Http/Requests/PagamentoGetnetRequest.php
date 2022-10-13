<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PagamentoGetnetRequest extends FormRequest
{
    private $tiposAceitos;

    protected function prepareForValidation()
    {
        // if(request()->filled('mes') || request()->filled('ano'))
        //     $this->tiposAceitos = 'interno,externo';
        // else
        //     $this->tiposAceitos = 'erros,interno,externo';
    }

    public function rules()
    {
        return [
            'amount' => 'required',
            'expiration' => 'required',
            'security_code' => 'required',
            'document_number' => 'required',
            'cardholder_name' => 'required',
            'card_number' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Campo obrigatório',
        ];
    }
}
