<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;

class GerarTextoRequest extends FormRequest
{
    private $niveis;
    private $comNumero;
    private $service;
    private $validarConteudo;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service->getService('GerarTexto');
    }

    public function authorize()
    {
        if(\Route::is('carta-servicos-buscar'))
            return true;
        
        $user = auth()->user();
        return $user->can('gerarTextoUpdate', $user);
    }

    protected function prepareForValidation()
    {
        if(\Route::is('textos.create'))
            return;

        if(\Route::is('textos.delete'))
        {
            $this->merge(['excluir_ids' => explode(',', $this->excluir_ids)]);
            return;
        }

        $this->validarConteudo = '';

        $this->niveis = '0,1,2,3';
        if(request()->filled('tipo'))
            $this->niveis = $this->tipo == 'Título' ? '0' : '1,2,3';
        if(request()->filled('tipo'))
            $this->comNumero = $this->tipo == 'Subtítulo' ? '1' : '0,1';

        // Somente url
        if($this->tipo_doc == 'prestacao-contas')
        {
            $conteudo = trim(str_replace('&nbsp;', '', strip_tags($this->conteudo)));
            $this->merge(['conteudo' => $conteudo == "" ? null : $conteudo]);
            $this->validarConteudo = '|starts_with:https://';
        }
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
                'conteudo' => 'nullable' . $this->validarConteudo,
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

        if(\Route::is('textos.delete'))
            return [
                'excluir_ids' => 'required',
            ];

        if(\Route::is('textos.create'))
            return [
                'n_vezes' => 'nullable|integer|min:2|max:' . $this->service->limiteCriarTextos(),
            ];
    }

    public function messages()
    {
        return [
            'required' => 'Campo :attribute é obrigatório',
            'min' => 'O campo :attribute deve conter pelo menos :min caracteres',
            'max' => 'O campo :attribute excedeu o limite de :max caracteres',
            'in' => 'O campo :attribute possui valor inválido',
            'boolean' => 'O campo :attribute possui valor inválido',
            'n_vezes.min' => 'Para criar vários textos o valor deve ser maior ou igual a :min',
            'n_vezes.max' => 'Para criar vários textos o valor deve ser menor ou igual a :max',
            'n_vezes.integer' => 'Para criar vários textos o valor deve ser um número inteiro',
            'conteudo.starts_with' => 'Deve conter somente uma url e que comece com https://'
        ];
    }
}
