<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;
use Illuminate\Support\Arr;

class BdoPerfilRequest extends FormRequest
{
    private $service;
    private $gerenti_emails;
    private $gerenti_telefones;
    private $gerenti_endereco;
    private $mun;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service->getService('Bdo');
    }

    protected function prepareForValidation()
    {
        $this->gerenti_emails = isset(session('dados')['emails']) ? implode(',', session('dados')['emails']) : '';
        $this->gerenti_telefones = isset(session('dados')['telefones']) ? implode(',', session('dados')['telefones']) : '';
        $this->gerenti_endereco = isset(session('dados')['endereco']) ? session('dados')['endereco'] : '';
        $this->merge(['endereco' => $this->gerenti_endereco]);

        $this->mun = $this->service->temp_municipios()['json'];
    }

    public function rules()
    {
        $geral = [
            'email' => 'required|email|in:' . $this->gerenti_emails,
            'telefone' => 'required|in:' . $this->gerenti_telefones,
            'endereco' => 'required',
            'regioes_municipios' => 'nullable|array|max:20',
            'regioes_municipios.*' => 'distinct|in:' . implode(',', Arr::flatten(json_decode($this->mun, true))),
        ];

        if($this->_method == 'POST'){
            return array_merge([
                'descricao' => 'required|max:700',
                'segmento' => 'required|in:' . collect(segmentos())->map(function ($name) {
                    return mb_strtoupper($name);
                })->implode(','),
                'regioes_seccional' => 'required|in:' . collect(session('dados')['regionais'])->pluck('regional')->map(function ($name) {
                    return mb_strtoupper($name);
                })->implode(','),
                'checkbox-tdu' => 'required|accepted',
            ], $geral);
        }

        return $geral;
        
    }

    public function messages()
    {
        return [
            "required" => "Campo obrigatório",
            "email" => 'Deve ser um e-mail válido',
            "regioes_municipios.max" => "Limite de :max municípios adicionados",
            "regioes_municipios.array" => "Formato inválido",
            "regioes_municipios.*.distinct" => "Não pode repetir município",
            'in' => 'Valor não aceito',
            "descricao.max" => "Ultrapassou o limite de :max caracteres",
            'accepted' => 'Você deve concordar com o Termo de Consentimento',
        ];
    }

    public function validated()
    {
        $this->merge([
            'regioes->municipios' => isset($this->regioes_municipios) ? $this->regioes_municipios : array(),
            'regioes->seccional' => $this->regioes_seccional,
            'segmento_gerenti' => isset(session('dados')['segmento']) ? session('dados')['segmento'] : '',
            'seccional_gerenti' => isset(session('dados')['seccional']) ? session('dados')['seccional'] : '',
        ]);

        $except = [
            '_token', '_method', 'checkbox-tdu', 'regioes_municipios', 'regioes_seccional'
        ];

        if($this->_method == 'PATCH')
            array_push($except, 'descricao', 'segmento', 'regioes->seccional', 'segmento_gerenti', 'seccional_gerenti');

        return Arr::except($this->all(), $except);
        // return $this->validator->validated();
    }
}
