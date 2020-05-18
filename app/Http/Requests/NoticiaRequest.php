<?php

namespace App\Http\Requests;

use App\Traits\ControleAcesso;
use Illuminate\Foundation\Http\FormRequest;

class NoticiaRequest extends FormRequest
{
    use ControleAcesso;

    public function authorize()
    {
        return $this->autoriza('NoticiaController', 'create');
    }

    public function rules()
    {
        return [
            'titulo' => 'required|max:191|min:3',
            'img' => 'max:191',
            'conteudo' => 'required|min:100'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'min' => 'O campo :attribute não possui o mínimo de caracteres obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
    }
}
