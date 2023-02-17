<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;

class AvisoRequest extends FormRequest
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service->getService('Aviso');
    }

    protected function prepareForValidation()
    {
        $aviso = $this->service->getById($this->id);
        if(isset($aviso) && $aviso->isComponenteSimples())
        {
            // A tag <p> difere na página aberta comparada a fechada
            $temp = str_replace('<p>', '<div>', $this->conteudo);
            $temp = str_replace('</p>', '</div>', $temp);
            $this->merge([
                'titulo' => '------------',
                'conteudo' => $temp
            ]);
        }
    }

    public function rules()
    {
        return [
            'cor_fundo_titulo' => 'required|in:' . implode(',', $this->service->cores()),
            'titulo' => 'required|max:191',
            'conteudo' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido',
            'in' => 'Cor escolhida não disponível'
        ];
    }
}
