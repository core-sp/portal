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
        $contabilCriarPR = [
            'cpf_cnpj' => ['required', new CpfCnpj],
            'nome' => 'required|min:5|max:191',
            'email' => 'required|email:rfc,filter|min:10|max:191',
        ];

        $rules = [
            'path' => $this->regraPath,
            'cnpj_contabil' => ['nullable', new CpfCnpj],
            'nome_contabil' => 'required_with:cnpj_contabil|nullable|min:5|max:191',
            'email_contabil' => 'required_with:cnpj_contabil|nullable|email:rfc,filter|min:10|max:191',
            'nome_contato_contabil' => 'required_with:cnpj_contabil|nullable|min:5|max:191|regex:/^\D*$/',
            'telefone_contabil' => 'required_with:cnpj_contabil|nullable|min:14|max:15|regex:/(\([0-9]{2}\))\s([0-9]{5})\-([0-9]{3,4})/',
            'segmento' => 'nullable|in:'.implode(',', segmentos()),
            'idregional' => 'required|exists:regionais,idregional',
            'cep' => 'required|size:9|regex:/([0-9]{5})\-([0-9]{3})/',
            'bairro' => 'required|min:4|max:191',
            'logradouro' => 'required|min:4|max:191',
            'numero' => 'required|min:1|max:10',
            'complemento' => 'nullable|max:50',
            'cidade' => 'required|min:4|max:191|regex:/^\D*$/',
            'uf' => 'required|in:'.implode(',', array_keys(estados())),
            'tipo_telefone' => 'required|in:'.implode(',', tipos_contatos()),
            'telefone' => 'required|min:14|max:15|regex:/(\([0-9]{2}\))\s([0-9]{5})\-([0-9]{3,4})/',
            'opcional_celular' => 'nullable|array|in:'.implode(',', opcoes_celular()),
            'opcional_celular.*' => 'distinct',
            'tipo_telefone_1' => 'required_with:telefone_1|nullable|in:'.implode(',', tipos_contatos()),
            'telefone_1' => 'required_with:tipo_telefone_1|nullable|min:14|max:15|regex:/(\([0-9]{2}\))\s([0-9]{5})\-([0-9]{3,4})/',
            'opcional_celular_1' => 'nullable|array|in:'.implode(',', opcoes_celular()),
            'opcional_celular_1.*' => 'distinct',
            'pergunta' => 'nullable|min:2|max:191'
        ];

        $pessoaFisica = [
            'nome_social' => 'nullable|min:5|max:191|regex:/^\D*$/',
            'sexo' => 'required|in:'.implode(',', array_keys(generos())),
            'dt_nascimento' => 'required|date_format:Y-m-d|before_or_equal:'.$this->regraDtNasc,
            'estado_civil' => 'nullable|in:'.implode(',', estados_civis()),
            'nacionalidade' => 'required|in:'.implode(',', nacionalidades()),
            'naturalidade_cidade' => 'required_if:nacionalidade,Brasileira|nullable|string|min:4|max:191',
            'naturalidade_estado' => 'required_if:nacionalidade,Brasileira|nullable|in:'.implode(',', array_keys(estados())),
            'nome_mae' => 'required|min:5|max:191|regex:/^\D*$/',
            'nome_pai' => 'nullable|min:5|max:191|regex:/^\D*$/',
            'tipo_identidade' => 'required|in:'.implode(',', tipos_identidade()),
            'identidade' => 'required|min:4|max:30',
            'orgao_emissor' => 'required|min:3|max:191',
            'dt_expedicao' => 'required|date_format:Y-m-d|before_or_equal:today',
        ];
        
        $pessoaJuridica = [
            'razao_social' => 'required|min:5|max:191|regex:/^\D*$/',
            'capital_social' => 'required|min:4|max:16|regex:/^((?!(0))[0-9\.]{1,}),([0-9]{2})$/',
            'nire' => 'nullable|min:5|max:20',
            'tipo_empresa' => 'required|in:'.implode(',', tipos_empresa()),
            'dt_inicio_atividade' => 'required|date_format:Y-m-d|before_or_equal:today',
            'inscricao_municipal' => 'nullable|min:5|max:30',
            'inscricao_estadual' => 'nullable|min:5|max:30',
            'checkEndEmpresa' => 'present',
            'cep_empresa' => 'required_if:checkEndEmpresa,off|nullable|size:9|regex:/([0-9]{5})\-([0-9]{3})/',
            'bairro_empresa' => 'required_if:checkEndEmpresa,off|nullable|min:4|max:191',
            'logradouro_empresa' => 'required_if:checkEndEmpresa,off|nullable|min:4|max:191',
            'numero_empresa' => 'required_if:checkEndEmpresa,off|nullable|max:10',
            'complemento_empresa' => 'nullable|max:50',
            'cidade_empresa' => 'required_if:checkEndEmpresa,off|nullable|min:4|max:191|regex:/^\D*$/',
            'uf_empresa' => 'required_if:checkEndEmpresa,off|nullable|in:'.implode(',', array_keys(estados())),
            'nome_rt' => 'required|min:5|max:191|regex:/^\D*$/',
            'nome_social_rt' => 'nullable|min:5|max:191|regex:/^\D*$/',
            'sexo_rt' => 'required|in:'.implode(',', array_keys(generos())),
            'dt_nascimento_rt' => 'required|date_format:Y-m-d|before_or_equal:'.$this->regraDtNasc,
            'cpf_rt' => ['required', new CpfCnpj],
            'tipo_identidade_rt' => 'required|in:'.implode(',', tipos_identidade()),
            'identidade_rt' => 'required|min:4|max:30',
            'orgao_emissor_rt' => 'required|min:3|max:191',
            'dt_expedicao_rt' => 'required|date_format:Y-m-d|before_or_equal:today',
            'cep_rt' => 'required|size:9|regex:/([0-9]{5})\-([0-9]{3})/',
            'bairro_rt' => 'required|min:4|max:191',
            'logradouro_rt' => 'required|min:4|max:191',
            'numero_rt' => 'required|min:1|max:10',
            'complemento_rt' => 'nullable|max:50',
            'cidade_rt' => 'required|min:4|max:191',
            'uf_rt' => 'required|in:'.implode(',', array_keys(estados())),
            'nome_mae_rt' => 'required|min:5|max:191|regex:/^\D*$/',
            'nome_pai_rt' => 'nullable|min:5|max:191|regex:/^\D*$/',
        ];

        if(\Route::is('externo.contabil.inserir.preregistro'))
            return $contabilCriarPR;

        $outrasRules = $externo->isPessoaFisica() ? $pessoaFisica : $pessoaJuridica;

        return array_merge($rules, $outrasRules);
    }

    protected function prepareForValidation()
    {
        $this->externo = getGuardExterno(auth()) == 'contabil' ? 
        auth()->guard('contabil')->user()->load('preRegistros')->preRegistros()->findOrFail($this->preRegistro)->userExterno : 
        auth()->guard('user_externo')->user();

        if(\Route::is('externo.contabil.inserir.preregistro')){
            $this->merge([
                'cpf_cnpj' => apenasNumeros($this->cpf_cnpj),
                'nome' => mb_strtoupper($this->nome)
            ]);
            return;
        }

        $preRegistro = $this->externo->load('preRegistro')->preRegistro;

        // Obrigatório salvar os anexos via rota ajax
        $anexosCount = isset($preRegistro) ? $preRegistro->anexos->count() : 0;

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
        {
            if(!isset(request()->checkEndEmpresa) || (isset(request()->checkEndEmpresa) && (request()->checkEndEmpresa != 'on')))
                $this->merge([
                    'checkEndEmpresa' => "off",
                ]);

            $this->merge([
                'cpf_rt' => apenasNumeros(request()->cpf_rt),
                'identidade_rt' => mb_strtoupper(apenasNumerosLetras(request()->identidade_rt))
            ]);
        }
        
        if($this->externo->isPessoaFisica())
        {
            if(request()->nacionalidade != "Brasileira")
                $this->merge([
                    'naturalidade_cidade' => null,
                    'naturalidade_estado' => null
                ]);
            $this->merge([
                'identidade' => mb_strtoupper(apenasNumerosLetras(request()->identidade))
            ]);
        }

        if(isset(request()->cnpj_contabil))
            $this->merge([
                'cnpj_contabil' => apenasNumeros(request()->cnpj_contabil),
            ]);
    }

    public function rules()
    {
        $all = $this->getRules($this->externo);

        if(getGuardExterno(auth()) == 'contabil'){
            unset($all['cnpj_contabil']);
            unset($all['nome_contabil']);
            unset($all['email_contabil']);
            unset($all['nome_contato_contabil']);
        }

        return $all;
    }

    public function messages()
    {
        $attr = ' no item :attribute';

        return [
            'min' => 'Quantidade mínima de :min caracteres' . $attr,
            'max' => 'Limite de :max caracteres' . $attr,
            'in' => 'Valor não é aceito' . $attr,
            'sometimes' => 'Campo obrigatório quando desabilitado' . $attr,
            'required' => 'Campo obrigatório' . $attr,
            'required_if' => 'Campo obrigatório' . $attr,
            'mimetypes' => 'O arquivo não possue extensão permitida ou está com erro' . $attr,
            'file' => 'Deve ser um arquivo' . $attr,
            'size' => 'Deve ter :size caracteres' . $attr,
            'exists' => 'Esta regional não existe' . $attr,
            'date_format' => 'Deve ser tipo data' . $attr,
            'dt_inicio_atividade.before_or_equal' => 'Data deve ser igual ou anterior a hoje' . $attr,
            'dt_expedicao_rt.before_or_equal' => 'Data deve ser igual ou anterior a hoje' . $attr,
            'dt_expedicao.before_or_equal' => 'Data deve ser igual ou anterior a hoje' . $attr,
            'dt_nascimento.before_or_equal' => 'Deve ter 18 anos completos ou mais' . $attr,
            'dt_nascimento_rt.before_or_equal' => 'Deve ter 18 anos completos ou mais' . $attr,
            'email' => 'Deve ser no formato de email email@email.com' . $attr,
            'checkEndEmpresa.present' => 'Campo opção "Mesmo Endereço" deve estar presente' . $attr,
            'nome_contabil.required_with' => 'Campo obrigatório se possui o CNPJ' . $attr,
            'nome_contato_contabil.required_with' => 'Campo obrigatório se possui o CNPJ' . $attr,
            'telefone_contabil.required_with' => 'Campo obrigatório se possui o CNPJ' . $attr,
            'email_contabil.required_with' => 'Campo obrigatório se possui o CNPJ' . $attr,
            'telefone_1.required_with' => 'Campo obrigatório se possui o tipo de telefone opcional' . $attr,
            'tipo_telefone_1.required_with' => 'Campo obrigatório se possui o telefone opcional' . $attr,
            'regex' => 'Formato inválido' . $attr,
            'distinct' => 'Os valores devem ser diferentes' . $attr,
            'array' => 'Formato inválido' . $attr,
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        $contabilCriarPR = [
            'cpf_cnpj' => 'CPF / CNPJ',
            'nome' => 'Nome',
            'email' => 'E-mail',
        ];

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
            'naturalidade_cidade' => '"Naturalidade - Cidade"',
            'naturalidade_estado' => '"Naturalidade - Estado"',
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

        if(\Route::is('externo.contabil.inserir.preregistro'))
            return $contabilCriarPR;
            
        $outrasRules = $this->externo->isPessoaFisica() ? $pessoaFisica : $pessoaJuridica;

        return array_merge($rules, $outrasRules);
    }
}
