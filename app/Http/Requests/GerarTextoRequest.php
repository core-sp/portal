<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GerarTextoRequest extends FormRequest
{
    private $niveis;
    private $comNumero;

    protected function prepareForValidation()
    {
        $this->niveis = '0,1,2,3';
        if(request()->filled('tipo'))
            $this->niveis = $this->tipo == 'Título' ? '0' : '1,2,3';
        if(request()->filled('tipo'))
            $this->comNumero = $this->tipo == 'Subtítulo' ? '1' : '0,1';
    }

    public function rules()
    {
        if(\Route::is('textos.update.campos'))
        {
            return [
                'tipo' => 'required|in:Título,Subtítulo',
                'texto_tipo' => 'required|max:191',
                'com_numeracao' => 'required|in:'.$this->comNumero,
                'nivel' => 'required|in:'.$this->niveis,
                'conteudo' => 'nullable',
            ];
        }

        if(\Route::is('textos.publicar'))
        {
            return [
                'publicar' => 'required|boolean',
            ];
        }

        if(\Route::is('carta-servicos-buscar'))
        {
            return [
                'buscaTexto' => 'required|min:3|max:191',
            ];
        }
    }

    public function messages()
    {
        return [
            'required' => 'Campo :attribute é obrigatório',
            'min' => 'O campo :attribute deve conter pelo menos :min caracteres',
            'max' => 'O campo :attribute excedeu o limite de :max caracteres',
            'in' => 'O campo :attribute possui valor inválido',
            'boolean' => 'O campo :attribute possui valor inválido',
        ];
    }
}
