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
            'segmento' => 'required|in:'.implode(',', segmentos()),
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
            'opcional_celular' => 'nullable|array|in:'.implode(',', opcoes_celular()),
            'tipo_telefone_1' => 'required_with:telefone_1|nullable|in:'.implode(',', tipos_contatos()),
            'telefone_1' => 'required_with:tipo_telefone_1|max:20'.$this->regraRegexTel,
            'opcional_celular_1' => 'nullable|array|in:'.implode(',', opcoes_celular()),
            'pergunta' => 'required|max:191|string'
        ];

        $pessoaFisica = [
            'nome_social' => 'nullable|max:191|regex:/^\D*$/',
            'sexo' => 'required|size:1|in:'.implode(',', array_keys(generos())),
            'dt_nascimento' => 'required|date|before_or_equal:'.$this->regraDtNasc,
            'estado_civil' => 'nullable|in:'.implode(',', estados_civis()),
            'nacionalidade' => 'required|in:'.implode(',', nacionalidades()),
            'naturalidade' => 'required_if:nacionalidade,Brasileiro|nullable|in:'.implode(',', estados()),
            'nome_mae' => 'required|max:191|regex:/^\D*$/',
            'nome_pai' => 'nullable|max:191|regex:/^\D*$/',
            'tipo_identidade' => 'required|in:'.implode(',', tipos_identidade()),
            'identidade' => 'required|max:30',
            'orgao_emissor' => 'required|max:191',
            'dt_expedicao' => 'required|date|before_or_equal:today',
        ];
        
        $pessoaJuridica = [
            'razao_social' => 'required|max:191|regex:/^\D*$/',
            'capital_social' => 'required|max:16|regex:/([0-9.]{1,13}),([0-9]{2})/',
            'nire' => 'nullable|max:20',
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
            'sexo_rt' => 'required|size:1|in:'.implode(',', array_keys(generos())),
            'dt_nascimento_rt' => 'required|date|before_or_equal:'.$this->regraDtNasc,
            'cpf_rt' => ['required', new CpfCnpj],
            'tipo_identidade_rt' => 'required|in:'.implode(',', tipos_identidade()),
            'identidade_rt' => 'required|max:30',
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
        {
            if(!isset(request()->checkEndEmpresa))
                $this->merge([
                    'checkEndEmpresa' => "off",
                ]);

            $this->merge([
                'cpf_rt' => apenasNumeros(request()->cpf_rt),
                'identidade_rt' => strtoupper(apenasNumerosLetras(request()->identidade_rt))
            ]);
        }
        
        if($this->externo->isPessoaFisica())
        {
            if(request()->nacionalidade != "Brasileiro")
                $this->merge([
                    'naturalidade' => null
                ]);
            $this->merge([
                'identidade' => strtoupper(apenasNumerosLetras(request()->identidade))
            ]);
        }

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
        $attr = ' no item :attribute';

        return [
            'max' => 'Limite de :max caracteres' . $attr,
            'in' => 'Valor não é aceito' . $attr,
            'required' => 'Campo obrigatório' . $attr,
            'required_if' => 'Campo obrigatório' . $attr,
            'mimetypes' => 'O arquivo não possue extensão permitida ou está com erro' . $attr,
            'file' => 'Deve ser um arquivo' . $attr,
            'size' => 'Deve ter :size caracteres' . $attr,
            'exists' => 'Esta regional não existe' . $attr,
            'date' => 'Deve ser tipo data' . $attr,
            'dt_expedicao_rt.before_or_equal' => 'Data deve ser igual ou anterior a hoje' . $attr,
            'dt_expedicao.before_or_equal' => 'Data deve ser igual ou anterior a hoje' . $attr,
            'dt_nascimento.before_or_equal' => 'Deve ter 18 anos completos ou mais' . $attr,
            'dt_nascimento_rt.before_or_equal' => 'Deve ter 18 anos completos ou mais' . $attr,
            'email' => 'Deve ser no formato de email teste@teste.com' . $attr,
            'checkEndEmpresa.present' => 'Campo opção "Mesmo Endereço" deve estar presente' . $attr,
            'nome_contabil.required_with' => 'Campo obrigatório se possui o CNPJ' . $attr,
            'nome_contato_contabil.required_with' => 'Campo obrigatório se possui o CNPJ' . $attr,
            'telefone_contabil.required_with' => 'Campo obrigatório se possui o CNPJ' . $attr,
            'email_contabil.required_with' => 'Campo obrigatório se possui o CNPJ' . $attr,
            'telefone_1.required_with' => 'Campo obrigatório se possui o tipo de telefone opcional' . $attr,
            'tipo_telefone_1.required_with' => 'Campo obrigatório se possui o telefone opcional' . $attr,
            'regex' => 'Formato inválido' . $attr,
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        $rules = [
            'path' => '"Anexo"',
            'cnpj_contabil' => '"CNPJ da contabilidade"',
            'nome_contabil' => '"Nome da contabilidade"',
            'email_contabil' => '"E-mail da contabilidade"',
            'nome_contato_contabil' => '"Nome de contato da contabilidade"',
            'telefone_contabil' => '"Telefone da contabilidade"',
            'segmento' => '"Segmento"',
            'idregional' => '"Regional"',
            'cep' => '"Cep de correspondência"',
            'bairro' => '"Bairro de correspondência"',
            'logradouro' => '"Logradouro de correspondência"',
            'numero' => '"Número de correspondência"',
            'complemento' => '"Complemento de correspondência"',
            'cidade' => '"Município de correspondência"',
            'uf' => '"Estado de correspondência"',
            'tipo_telefone' => '"Tipo do telefone"',
            'telefone' => '"Número de telefone"',
            'tipo_telefone_1' => '"Tipo de telefone opcional"',
            'telefone_1' => '"Número de telefone opcional"',
            'opcional_celular' => '"Opcional Tipo Celular"',
            'opcional_celular_1' => '"Opcional Tipo Celular opcional"',
            'pergunta' => '"Pergunta"',
        ];

        $pessoaFisica = [
            'nome_social' => '"Nome social"',
            'sexo' => '"Gênero"',
            'dt_nascimento' => '"Data de nascimento"',
            'estado_civil' => '"Estado civil"',
            'nacionalidade' => '"Nacionalidade"',
            'naturalidade' => '"Naturalidade"',
            'nome_mae' => '"Nome da mãe"',
            'nome_pai' => '"Nome do pai"',
            'tipo_identidade' => '"Tipo do documento de identidade"',
            'identidade' => '"Número de identidade"',
            'orgao_emissor' => '"Órgão emissor"',
            'dt_expedicao' => '"Data de expedição"',
        ];

        $pessoaJuridica = [
            'razao_social' => '"Razão social"',
            'capital_social' => '"Capital social"',
            'nire' => '"Nire"',
            'tipo_empresa' => '"Tipo da empresa"',
            'dt_inicio_atividade' => '"Data de início das atividades"',
            'inscricao_municipal' => '"Inscrição municipal"',
            'inscricao_estadual' => '"Inscrição estadual"',
            'checkEndEmpresa' => '"Opção Mesmo endereço"',
            'cep_empresa' => '"Cep da empresa"',
            'bairro_empresa' => '"Bairro da empresa"',
            'logradouro_empresa' => '"Logradouro da empresa"',
            'numero_empresa' => '"Número da empresa"',
            'complemento_empresa' => '"Complemento da empresa"',
            'cidade_empresa' => '"Município da empresa"',
            'uf_empresa' => '"Estado da empresa"',
            'nome_rt' => '"Nome do responsável técnico"',
            'nome_social_rt' => '"Nome social do responsável técnico"',
            'sexo_rt' => '"Gênero do responsável técnico"',
            'dt_nascimento_rt' => '"Data de nascimento do responsável técnico"',
            'cpf_rt' => '"CPF do responsável técnico"',
            'tipo_identidade_rt' => '"Tipo do documento de identidade do responsável técnico"',
            'identidade_rt' => '"Número de identidade do responsável técnico"',
            'orgao_emissor_rt' => '"Órgão emissor do responsável técnico"',
            'dt_expedicao_rt' => '"Data de expedição do responsável técnico"',
            'cep_rt' => '"Cep do responsável técnico"',
            'bairro_rt' => '"Bairro do responsável técnico"',
            'logradouro_rt' => '"Logradouro do responsável técnico"',
            'numero_rt' => '"Número do responsável técnico"',
            'complemento_rt' => '"Complemento do responsável técnico"',
            'cidade_rt' => '"Município do responsável técnico"',
            'uf_rt' => '"Estado do responsável técnico"',
            'nome_mae_rt' => '"Nome da mãe do responsável técnico"',
            'nome_pai_rt' => '"Nome do pai do responsável técnico"',
        ];

        $outrasRules = $this->externo->isPessoaFisica() ? $pessoaFisica : $pessoaJuridica;

        return array_merge($rules, $outrasRules);
    }
}
