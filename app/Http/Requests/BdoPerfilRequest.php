<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

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
        if(\Route::is('bdorepresentantes.update'))
            return;
        // session gerenti admin

        $this->gerenti_emails = isset(session('dados_bdo')['emails']) ? implode(',', session('dados_bdo')['emails']) : '';
        $this->gerenti_telefones = isset(session('dados_bdo')['telefones']) ? implode(',', session('dados_bdo')['telefones']) : '';
        $this->gerenti_endereco = isset(session('dados_bdo')['endereco']) ? session('dados_bdo')['endereco'] : '';
        $this->merge(['endereco' => $this->gerenti_endereco]);

        $this->mun = $this->service->temp_municipios()['json'];
    }

    public function rules()
    {
        if(\Route::is('bdorepresentantes.update'))
            return [
                'descricao' => [
                    Rule::requiredIf((request()->setor == 'final') && (request()->status == 'aceito')),
                    'max:700',
                ],
                'status' => 'required|in:aceito,recusado',
                'setor' => 'required|in:atendimento,financeiro,final',
                'justificativa' => 'required_if:status,recusado|max:600',
                'campos_recusados' => [ 
                    Rule::requiredIf((request()->setor == 'atendimento') && (request()->status == 'recusado')),
                    'array',
                    'max:2',
                ],
                'campos_recusados.*' => 'distinct|in:SEGMENTO,REGIONAL',
            ];

        // Área Restrita RC 
        $geral = [
            'email' => 'required|email|in:' . $this->gerenti_emails,
            'telefone' => 'required|in:' . $this->gerenti_telefones,
            'endereco' => 'required',
            'regioes_municipios' => 'nullable|array|max:20',
            'regioes_municipios.*' => 'distinct|in:' . implode(',', Arr::flatten(json_decode($this->mun, true))),
        ];

        if($this->isMethod('post'))
        {
            return array_merge([
                'descricao' => 'required|max:700',
                'segmento' => 'required|in:' . collect(segmentos())->map(function ($name) {
                    return mb_strtoupper($name);
                })->implode(','),
                'regioes_seccional' => 'required|in:' . collect(session('dados_bdo')['regionais'])->pluck('regional')->map(function ($name) {
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
            "required" => ":attribute é um campo obrigatório",
            "email" => 'Deve ser um e-mail válido',
            'in' => ':attribute não aceita o valor inserido',
            'accepted' => 'Você deve concordar com o Termo de Consentimento',
            "regioes_municipios.max" => "Limite de :max municípios adicionados",
            "regioes_municipios.array" => "Formato inválido",
            "regioes_municipios.*.distinct" => "Não pode repetir município",
            "descricao.max" => ":attribute ultrapassou o limite de :max caracteres",
            "justificativa.required_if" => ":attribute é um campo obrigatório quando o status é para recusar",
            "campos_recusados.required_if" => "Selecionar um item a ser recusado é obrigatório quando o setor de atendimento quer recusar",
            "campos_recusados.array" => "Itens a serem recusados não estão num formato válido",
            "campos_recusados.*.distinct" => "Não pode repetir campo a ser recusado",
            "campos_recusados.max" => "Somente até :max itens a serem recusados",
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if(!\Route::is('bdorepresentantes.update'))
            request()->session()->flash('dados_bdo', session('dados_bdo'));
    
        throw (new ValidationException($validator))
                    ->errorBag($this->errorBag)
                    ->redirectTo($this->getRedirectUrl());
    }

    public function validated()
    {
        if(\Route::is('bdorepresentantes.update'))
        {
            $this->merge([
                'justificativa' => $this->justificativa,
                'campos_recusados' => isset($this->campos_recusados) ? $this->campos_recusados : [],
            ]);

            return Arr::except($this->all(), ['_token', '_method']);
        }
        
        $this->merge([
            'regioes->municipios' => isset($this->regioes_municipios) ? $this->regioes_municipios : array(),
            'regioes->seccional' => $this->regioes_seccional,
            'segmento_gerenti' => isset(session('dados_bdo')['segmento']) ? session('dados_bdo')['segmento'] : '',
            'seccional_gerenti' => isset(session('dados_bdo')['seccional']) ? session('dados_bdo')['seccional'] : '',
            'em_dia_gerenti' => isset(session('dados_bdo')['em_dia']) ? session('dados_bdo')['em_dia'] : false,
        ]);

        $except = [
            '_token', '_method', 'checkbox-tdu', 'regioes_municipios', 'regioes_seccional'
        ];

        if($this->isMethod('patch'))
            array_push($except, 'descricao', 'segmento', 'regioes->seccional', 'segmento_gerenti', 'seccional_gerenti', 'em_dia_gerenti');

        return Arr::except($this->all(), $except);
        // return $this->validator->validated();
    }
}
