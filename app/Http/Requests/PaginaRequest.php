<?php

namespace App\Http\Requests;

use App\Traits\ControleAcesso;
use Illuminate\Foundation\Http\FormRequest;

class PaginaRequest extends FormRequest
{
    use ControleAcesso;
    
    public function authorize()
    {
        return $this->autoriza('PaginaController', 'create');
    }

    public function rules()
    {
        return [
            'titulo' => 'required|max:191',
            'subtitulo' => 'max:191',
            'img' => 'max:191',
            'conteudo' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
    }
}
