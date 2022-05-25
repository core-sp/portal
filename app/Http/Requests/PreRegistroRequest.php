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

    private function getRules($externo)
    {
        $rules = [
            'path' => $this->regraPath,
            'nome_contabil' => 'max:191',
            'cnpj_contabil' => ['nullable', new CpfCnpj],
            'email_contabil' => 'max:191|email',
            'nome_contato_contabil' => 'max:191',
            'telefone_contabil' => '',
            'registro_secundario' => '',
            'ramo_atividade' => 'max:191',
            'segmento' => 'in:'.implode(',', segmentos()),
            'idregional' => 'exists:regionais,idregional',
            'cep' => 'max:9',
            'bairro' => 'max:191',
            'logradouro' => 'max:191',
            'numero' => 'max:10',
            'complemento' => 'max:191',
            'cidade' => 'max:191',
            'uf' => 'size:2|in:'.implode(',', array_keys(estados())),
            'tipo_telefone' => 'in:'.implode(',', tipos_contatos()),
            'telefone' => '',
            'tipo_telefone_1' => 'in:'.implode(',', tipos_contatos()),
            'telefone_1' => '',
        ];

        $pessoaFisica = [
            'nome_social' => 'max:191',
            'sexo' => 'size:1|in:M,F',
            'dt_nascimento' => 'date|before_or_equal:'.$this->regraDtNasc,
            'estado_civil' => 'in:'.implode(',', estados_civis()),
            'nacionalidade' => 'in:'.implode(',', nacionalidades()),
            'naturalidade' => 'in:'.implode(',', estados()),
            'nome_mae' => 'max:191',
            'nome_pai' => 'max:191',
            'identidade' => 'max:20',
            'orgao_emissor' => 'max:191',
            'dt_expedicao' => 'date|before_or_equal:today',
        ];

        $pessoaJuridica = [
            'razao_social' => 'max:191',
            'capital_social' => '',
            'nire' => 'max:20',
            'tipo_empresa' => 'in:'.implode(',', tipos_empresa()),
            'dt_inicio_atividade' => 'date|before_or_equal:today',
            'inscricao_municipal' => '',
            'inscricao_estadual' => '',
            'checkEndEmpresa' => '',
            'cep_empresa' => 'max:9',
            'bairro_empresa' => 'max:191',
            'logradouro_empresa' => 'max:191',
            'numero_empresa' => 'max:10',
            'complemento_empresa' => 'max:191',
            'cidade_empresa' => 'max:191',
            'uf_empresa' => 'size:2|in:'.implode(',', array_keys(estados())),
            'nome_rt' => 'max:191',
            'nome_social_rt' => 'max:191',
            'registro' => '',
            'sexo_rt' => 'size:1|in:M,F',
            'dt_nascimento_rt' => 'date|before_or_equal:'.$this->regraDtNasc,
            'cpf_rt' => ['size:11', new CpfCnpj],
            'identidade_rt' => 'max:20',
            'orgao_emissor_rt' => 'max:191',
            'dt_expedicao_rt' => 'date|before_or_equal:today',
            'cep_rt' => 'max:9',
            'bairro_rt' => 'max:191',
            'logradouro_rt' => 'max:191',
            'numero_rt' => 'max:10',
            'complemento_rt' => 'max:191',
            'cidade_rt' => 'max:191',
            'uf_rt' => 'size:2|in:'.implode(',', array_keys(estados())),
            'nome_mae_rt' => 'max:191',
            'nome_pai_rt' => 'max:191',
        ];

        $outrasRules = $externo->isPessoaFisica() ? $pessoaFisica : $pessoaJuridica;

        return array_merge($rules, $outrasRules);
    }

    protected function prepareForValidation()
    {
        $this->externo = auth()->guard('user_externo')->user();
        // Obrigatório salvar os anexos via rota ajax
        $anexosCount = $this->externo->preRegistro->anexos->count();

        $this->regraPath = '';
        $this->regraDtNasc = Carbon::today()->subYears(18)->format('Y-m-d');

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
        ];
    }
}
