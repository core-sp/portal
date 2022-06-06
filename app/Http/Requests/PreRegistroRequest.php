<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CpfCnpj;
use Carbon\Carbon;

class PreRegistroRequest extends FormRequest
{
    private $regraDtNasc;
    private $regraPath;
    private $externo;
    private $regraRegexTel;

    private function getRules($externo)
    {
        $rules = [
            'path' => $this->regraPath,
            'cnpj_contabil' => ['nullable', new CpfCnpj],
            'nome_contabil' => 'required_with:cnpj_contabil|max:191',
            'email_contabil' => 'required_with:cnpj_contabil|max:191|email',
            'nome_contato_contabil' => 'required_with:cnpj_contabil|max:191|regex:/^\D*$/',
            'telefone_contabil' => 'required_with:cnpj_contabil|max:20|regex:/(\([0-9]{2}\))\s([0-9]{4,5})\-([0-9]{4,5})/',
            'registro_secundario' => 'nullable',
            'ramo_atividade' => 'required|max:191|regex:/^\D*$/',
            'segmento' => 'nullable|in:'.implode(',', segmentos()),
            'idregional' => 'required|exists:regionais,idregional',
            'cep' => 'required|max:9',
            'bairro' => 'required|max:191',
            'logradouro' => 'required|max:191',
            'numero' => 'required|max:10',
            'complemento' => 'nullable|max:191',
            'cidade' => 'required|max:191|regex:/^\D*$/',
            'uf' => 'required|size:2|in:'.implode(',', array_keys(estados())),
            'tipo_telefone' => 'required|in:'.implode(',', tipos_contatos()),
            'telefone' => 'required|max:20|regex:/(\([0-9]{2}\))\s([0-9]{4,5})\-([0-9]{4,5})/',
            'tipo_telefone_1' => 'required_with:telefone_1|nullable|in:'.implode(',', tipos_contatos()),
            'telefone_1' => 'required_with:tipo_telefone_1|max:20'.$this->regraRegexTel,
        ];

        $pessoaFisica = [
            'nome_social' => 'nullable|max:191|regex:/^\D*$/',
            'sexo' => 'required|size:1|in:M,F',
            'dt_nascimento' => 'required|date|before_or_equal:'.$this->regraDtNasc,
            'estado_civil' => 'nullable|in:'.implode(',', estados_civis()),
            'nacionalidade' => 'required|in:'.implode(',', nacionalidades()),
            'naturalidade' => 'required|in:'.implode(',', estados()),
            'nome_mae' => 'required|max:191|regex:/^\D*$/',
            'nome_pai' => 'nullable|max:191|regex:/^\D*$/',
            'identidade' => 'required|max:20',
            'orgao_emissor' => 'required|max:191',
            'dt_expedicao' => 'required|date|before_or_equal:today',
        ];

        $pessoaJuridica = [
            'razao_social' => 'required|max:191|regex:/^\D*$/',
            'capital_social' => 'required|max:16|regex:/([0-9.]{1,13}),([0-9]{2})/',
            'nire' => 'required|max:20',
            'tipo_empresa' => 'required|in:'.implode(',', tipos_empresa()),
            'dt_inicio_atividade' => 'required|date|before_or_equal:today',
            'inscricao_municipal' => 'required|max:30',
            'inscricao_estadual' => 'required|max:30',
            'checkEndEmpresa' => 'present',
            'cep_empresa' => 'required_if:checkEndEmpresa,off|max:9',
            'bairro_empresa' => 'required_if:checkEndEmpresa,off|max:191',
            'logradouro_empresa' => 'required_if:checkEndEmpresa,off|max:191',
            'numero_empresa' => 'required_if:checkEndEmpresa,off|max:10',
            'complemento_empresa' => 'nullable|max:191',
            'cidade_empresa' => 'required_if:checkEndEmpresa,off|max:191|regex:/^\D*$/',
            'uf_empresa' => 'required_if:checkEndEmpresa,off|size:2|in:'.implode(',', array_keys(estados())),
            'nome_rt' => 'required|max:191|regex:/^\D*$/',
            'nome_social_rt' => 'nullable|max:191|regex:/^\D*$/',
            'registro' => 'nullable|max:20',
            'sexo_rt' => 'required|size:1|in:M,F',
            'dt_nascimento_rt' => 'required|date|before_or_equal:'.$this->regraDtNasc,
            'cpf_rt' => ['required', new CpfCnpj],
            'identidade_rt' => 'required|max:20',
            'orgao_emissor_rt' => 'required|max:191',
            'dt_expedicao_rt' => 'required|date|before_or_equal:today',
            'cep_rt' => 'required|max:9',
            'bairro_rt' => 'required|max:191',
            'logradouro_rt' => 'required|max:191',
            'numero_rt' => 'required|max:10',
            'complemento_rt' => 'nullable|max:191',
            'cidade_rt' => 'required|max:191',
            'uf_rt' => 'required|size:2|in:'.implode(',', array_keys(estados())),
            'nome_mae_rt' => 'required|max:191|regex:/^\D*$/',
            'nome_pai_rt' => 'nullable|max:191|regex:/^\D*$/',
        ];

        $outrasRules = $externo->isPessoaFisica() ? $pessoaFisica : $pessoaJuridica;

        return array_merge($rules, $outrasRules);
    }

    protected function prepareForValidation()
    {
        $this->externo = auth()->guard('user_externo')->user();
        $preRegistro = $this->externo->preRegistro;
        // Obrigatório salvar os anexos via rota ajax
        $anexosCount = isset($preRegistro) ? $preRegistro->anexos->count() : 0;

        $this->regraPath = '';
        $this->regraDtNasc = Carbon::today()->subYears(18)->format('Y-m-d');
        $this->regraRegexTel = isset(request()->tipo_telefone_1) ? '|regex:/(\([0-9]{2}\))\s([0-9]{4,5})\-([0-9]{4,5})/' : '';

        if($anexosCount == 0)
        {
            $this->regraPath = 'required';
            $this->merge([
                'path' => ''
            ]);
        }else
            $this->merge([
                'path' => $anexosCount
            ]);
        
        
        if(!$this->externo->isPessoaFisica())
            $this->merge([
                'cpf_rt' => apenasNumeros(request()->cpf_rt)
            ]);

        if(isset(request()->cnpj_contabil))
            $this->merge([
                'cnpj_contabil' => apenasNumeros(request()->cnpj_contabil),
            ]);

        if(isset(request()->registro))
            $this->merge([
                'registro' => str_replace("/", "", request()->registro),
            ]);
        
        if(!isset(request()->checkEndEmpresa))
            $this->merge([
                'checkEndEmpresa' => "off",
            ]);
    }

    public function rules()
    {
        return $this->getRules($this->externo);
    }

    public function messages()
    {
        return [
            'max' => 'Limite de :max caracteres',
            'in' => 'Valor não é aceito',
            'required' => 'Campo obrigatório',
            'required_if' => 'Campo obrigatório',
            'mimetypes' => 'O arquivo não possue extensão permitida ou está com erro',
            'file' => 'Deve ser um arquivo',
            'size' => 'Deve ter :size caracteres',
            'exists' => 'Esta regional não existe',
            'date' => 'Deve ser tipo data',
            'dt_expedicao_rt.before_or_equal' => 'Data deve ser igual ou anterior a hoje',
            'dt_expedicao.before_or_equal' => 'Data deve ser igual ou anterior a hoje',
            'dt_nascimento.before_or_equal' => 'Deve ter 18 anos completos ou mais',
            'dt_nascimento_rt.before_or_equal' => 'Deve ter 18 anos completos ou mais',
            'email' => 'Deve ser no formato de email teste@teste.com',
            'checkEndEmpresa.present' => 'Campo opção "Mesmo Endereço" deve estar presente',
            'nome_contabil.required_with' => 'Campo obrigatório se possui o CNPJ',
            'nome_contato_contabil.required_with' => 'Campo obrigatório se possui o CNPJ',
            'telefone_contabil.required_with' => 'Campo obrigatório se possui o CNPJ',
            'email_contabil.required_with' => 'Campo obrigatório se possui o CNPJ',
            'telefone_1.required_with' => 'Campo obrigatório se possui o tipo de telefone opcional',
            'tipo_telefone_1.required_with' => 'Campo obrigatório se possui o telefone opcional',
            'regex' => 'Formato inválido',
        ];
    }
}
