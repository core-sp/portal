<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CpfCnpj;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Rules\Cnpj;
use App\Rules\Cpf;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PreRegistroRequest extends FormRequest
{
    private $regraDtNasc;
    private $regraPath;
    private $regraReservistaPF;
    private $regraReservistaRT;
    private $externo;
    private $socios;
    private $msg_socios;

    private function upperImplode($array)
    {
        return mb_strtoupper(implode(',', $array), 'UTF-8');
    }

    private function getRules()
    {
        if(\Route::is('externo.contabil.inserir.preregistro'))
            return [
                'cpf_cnpj' => ['required', new CpfCnpj, 'unique:contabeis,cnpj'],
                'nome' => 'required|min:5|max:191',
                'email' => 'required|email:rfc,filter|min:10|max:191|unique:contabeis,email',
            ];

        $pessoa = null;

        $segmentos = $this->upperImplode(segmentos());
        $tipos_contatos = $this->upperImplode(tipos_contatos());
        $opcoes_celular = $this->upperImplode(opcoes_celular());
        $estados_civis = $this->upperImplode(estados_civis());
        $nacionalidades = $this->upperImplode(nacionalidades());
        $tipos_identidade = $this->upperImplode(tipos_identidade());

        $rules = [
            'path' => $this->regraPath,
            'cnpj_contabil' => ['nullable', new Cnpj, 'unique:users_externo,cpf_cnpj'],
            'nome_contabil' => 'required_with:cnpj_contabil|nullable|min:5|max:191',
            'email_contabil' => 'required_with:cnpj_contabil|nullable|email:rfc,filter|min:10|max:191',
            'nome_contato_contabil' => 'required_with:cnpj_contabil|nullable|min:5|max:191|regex:/^\D*$/',
            'telefone_contabil' => 'required_with:cnpj_contabil|nullable|min:14|max:17|regex:/(\([0-9]{2}\))\s([0-9]{4,5})\-([0-9]{4,5})$/',
            'segmento' => 'nullable|in:'. $segmentos,
            'idregional' => 'required|exists:regionais,idregional',
            'cep' => 'required|size:9|regex:/([0-9]{5})\-([0-9]{3})$/',
            'bairro' => 'required|min:4|max:191',
            'logradouro' => 'required|min:4|max:191',
            'numero' => 'required|min:1|max:10',
            'complemento' => 'nullable|max:50',
            'cidade' => 'required|min:4|max:191|regex:/^\D*$/',
            'uf' => 'required|in:'.implode(',', array_keys(estados())),
            'tipo_telefone' => 'required|in:'. $tipos_contatos,
            'telefone' => 'required|min:14|max:17|regex:/(\([0-9]{2}\))\s([0-9]{4,5})\-([0-9]{4,5})$/',
            'opcional_celular' => 'nullable|array|in:'. $opcoes_celular,
            'opcional_celular.*' => 'distinct',
            'tipo_telefone_1' => 'required_with:telefone_1|nullable|in:'. $tipos_contatos,
            'telefone_1' => 'required_with:tipo_telefone_1|nullable|min:14|max:17|regex:/(\([0-9]{2}\))\s([0-9]{4,5})\-([0-9]{4,5})$/',
            'opcional_celular_1' => 'nullable|array|in:'. $opcoes_celular,
            'opcional_celular_1.*' => 'distinct',
            'pergunta' => 'required|min:2|max:191'
        ];

        if($this->externo->isPessoaFisica())
            $pessoa = [
                'nome_social' => 'nullable|min:5|max:191|regex:/^\D*$/',
                'sexo' => 'required|in:'.implode(',', array_keys(generos())),
                'dt_nascimento' => 'required|date_format:Y-m-d|before_or_equal:'.$this->regraDtNasc,
                'estado_civil' => 'nullable|in:'. $estados_civis,
                'nacionalidade' => 'required|in:'. $nacionalidades,
                'naturalidade_cidade' => 'required_if:nacionalidade,BRASILEIRA|nullable|string|min:4|max:191',
                'naturalidade_estado' => 'required_if:nacionalidade,BRASILEIRA|nullable|in:'.implode(',', array_keys(estados())),
                'nome_mae' => 'required|min:5|max:191|regex:/^\D*$/',
                'nome_pai' => 'nullable|min:5|max:191|regex:/^\D*$/',
                'tipo_identidade' => 'required|in:'. $tipos_identidade,
                'identidade' => 'required|min:4|max:30',
                'orgao_emissor' => 'required|min:3|max:191',
                'dt_expedicao' => 'required|date_format:Y-m-d|before_or_equal:today',
                'titulo_eleitor' => 'required_if:nacionalidade,BRASILEIRA|nullable|min:12|max:15',
                'zona' => 'required_if:nacionalidade,BRASILEIRA|max:6',
                'secao' => 'required_if:nacionalidade,BRASILEIRA|max:8',
                'ra_reservista' => [
                    Rule::requiredIf(function () {
                        return ($this->sexo == 'M') && $this->regraReservistaPF;
                    }),
                    'nullable',
                    'min:12',
                    'max:15',
                ],
            ];
        
        if(!$this->externo->isPessoaFisica()){
            $pessoa = [
                'razao_social' => 'required|min:5|max:191|regex:/^\D*$/',
                'capital_social' => 'required|min:4|max:16|regex:/^((?!(0))[0-9\.]{1,}),([0-9]{2})$/',
                'nire' => 'nullable|min:5|max:20',
                'tipo_empresa' => 'required|in:'.implode(',', tipos_empresa()),
                'dt_inicio_atividade' => 'required|date_format:Y-m-d|before_or_equal:today',
                'nome_fantasia' => 'required|min:5|max:191',
                'checkEndEmpresa' => 'present',
                'cep_empresa' => 'required_if:checkEndEmpresa,off|nullable|size:9|regex:/([0-9]{5})\-([0-9]{3})$/',
                'bairro_empresa' => 'required_if:checkEndEmpresa,off|nullable|min:4|max:191',
                'logradouro_empresa' => 'required_if:checkEndEmpresa,off|nullable|min:4|max:191',
                'numero_empresa' => 'required_if:checkEndEmpresa,off|nullable|max:10',
                'complemento_empresa' => 'nullable|max:50',
                'cidade_empresa' => 'required_if:checkEndEmpresa,off|nullable|min:4|max:191|regex:/^\D*$/',
                'uf_empresa' => 'required_if:checkEndEmpresa,off|nullable|in:'.implode(',', array_keys(estados())),
                'cpf_rt' => ['required', new Cpf],
                'nome_rt' => 'required_with:cpf_rt|min:5|max:191|regex:/^\D*$/',
                'nome_social_rt' => 'nullable|min:5|max:191|regex:/^\D*$/',
                'sexo_rt' => 'required_with:cpf_rt|in:'.implode(',', array_keys(generos())),
                'dt_nascimento_rt' => 'required_with:cpf_rt|date_format:Y-m-d|before_or_equal:'.$this->regraDtNasc,
                'tipo_identidade_rt' => 'required_with:cpf_rt|in:'. $tipos_identidade,
                'identidade_rt' => 'required_with:cpf_rt|min:4|max:30',
                'orgao_emissor_rt' => 'required_with:cpf_rt|min:3|max:191',
                'dt_expedicao_rt' => 'required_with:cpf_rt|date_format:Y-m-d|before_or_equal:today',
                'cep_rt' => 'required_with:cpf_rt|size:9|regex:/([0-9]{5})\-([0-9]{3})$/',
                'bairro_rt' => 'required_with:cpf_rt|min:4|max:191',
                'logradouro_rt' => 'required_with:cpf_rt|min:4|max:191',
                'numero_rt' => 'required_with:cpf_rt|min:1|max:10',
                'complemento_rt' => 'nullable|max:50',
                'cidade_rt' => 'required_with:cpf_rt|min:4|max:191',
                'uf_rt' => 'required_with:cpf_rt|in:'.implode(',', array_keys(estados())),
                'nome_mae_rt' => 'required_with:cpf_rt|min:5|max:191|regex:/^\D*$/',
                'nome_pai_rt' => 'required_if:checkRT_socio,on|nullable|min:5|max:191|regex:/^\D*$/',
                'titulo_eleitor_rt' => 'required_with:cpf_rt|nullable|min:12|max:15',
                'zona_rt' => 'required_with:cpf_rt|max:6',
                'secao_rt' => 'required_with:cpf_rt|max:8',
                'ra_reservista_rt' => [
                    Rule::requiredIf(function () {
                        return ($this->sexo_rt == 'M') && $this->regraReservistaRT && (strlen($this->cpf_rt) == 11);
                    }),
                    'nullable',
                    'min:12',
                    'max:15',
                ],
            ];
        }

        return array_merge($rules, $pessoa, $this->socios);
    }

    protected function prepareForValidation()
    {
        if(\Route::is('externo.contabil.inserir.preregistro')){
            $this->merge([
                'cpf_cnpj' => apenasNumeros($this->cpf_cnpj),
                'nome' => mb_strtoupper($this->nome)
            ]);
            return;
        }

        $user_contabil_check = auth()->guard('contabil')->check();

        if(\Route::is('externo.verifica.inserir.preregistro') && 
        (isset($this->preRegistro) && !$user_contabil_check) || (!isset($this->preRegistro) && $user_contabil_check))
            throw new ModelNotFoundException(isset($this->preRegistro) ? "Página não encontrada para usuário externo comum." : "Página não encontrada para contabilidade.", 401);
        
        $this->externo = $user_contabil_check ? 
        auth()->guard('contabil')->user()->preRegistros->find($this->preRegistro)->userExterno : 
        auth()->guard('user_externo')->user();

        $this->socios = array();
        $this->msg_socios = array();

        $preRegistro = $this->externo->load('preRegistro')->preRegistro;
        if(!isset($preRegistro))
            throw new ModelNotFoundException("No query results for model [App\PreRegistro] ");

        // apaga todos os dados vindo do formulário
        $pergunta = $this->pergunta;
        $this->replace([]);
        $this->merge(['pergunta' => $pergunta]);

        // Obrigatório salvar os anexos via rota ajax
        $anexosCount = isset($preRegistro) ? $preRegistro->anexos->count() : 0;

        $this->regraPath = '';
        $this->regraDtNasc = Carbon::today()->subYears(18)->format('Y-m-d');
        $dataReservista = Carbon::today()->subYears(45)->format('Y-m-d');

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
        
        // substitui com os dados já salvos
        $this->merge($preRegistro->arrayValidacaoInputs());

        // substitui com os dados já salvos
        if(isset($preRegistro->contabil_id))
            $this->merge($preRegistro->contabil->arrayValidacaoInputs());
        
        if(!$this->externo->isPessoaFisica())
        {
            // substitui com os dados já salvos
            $this->merge($preRegistro->pessoaJuridica->arrayValidacaoInputs());

            if(isset($preRegistro->pessoaJuridica->responsavel_tecnico_id))
                $this->merge($preRegistro->pessoaJuridica->responsavelTecnico->arrayValidacaoInputs());

            $this->regraReservistaRT = $this->filled('dt_nascimento_rt') && Carbon::hasFormat($this->dt_nascimento_rt, 'Y-m-d') && 
            ($this->dt_nascimento_rt >= $dataReservista);

            // if(!isset(request()->checkEndEmpresa) || (isset(request()->checkEndEmpresa) && (request()->checkEndEmpresa != 'on')))
            //     $this->merge([
            //         'checkEndEmpresa' => "off",
            //     ]);

            $this->merge([
                'cpf_rt' => apenasNumeros($this->cpf_rt),
                'identidade_rt' => mb_strtoupper(apenasNumerosLetras($this->identidade_rt))
            ]);

            // confere os sócios
            $socios = $preRegistro->pessoaJuridica->possuiSocio() ? $preRegistro->pessoaJuridica->socios : null;
            if(isset($socios)){
                foreach($socios as $socio){
                    $this->socios = array_merge($this->socios, $socio->arrayValidacao());
                    $this->msg_socios = array_merge($this->msg_socios, $socio->arrayValidacaoMsg());
                    $this->merge($socio->arrayValidacaoInputs());
                }
                $this->merge(['checkRT_socio' => !$preRegistro->pessoaJuridica->possuiRTSocio() ? 'off' : 'on']);
            }
            else{
                $this->socios = ['cpf_cnpj_socio_' => 'required'];
                $this->msg_socios = ['cpf_cnpj_socio_' => '"CPF / CNPJ do Sócio"'];
            }
            $socios = null;
        }
        
        if($this->externo->isPessoaFisica())
        {
            // substitui com os dados já salvos
            $this->merge($preRegistro->pessoaFisica->arrayValidacaoInputs());

            $this->regraReservistaPF = $this->filled('nacionalidade') && ($this->nacionalidade == 'BRASILEIRA') && 
            $this->filled('dt_nascimento') && Carbon::hasFormat($this->dt_nascimento, 'Y-m-d') && ($this->dt_nascimento >= $dataReservista);

            if($this->filled('nacionalidade') && ($this->nacionalidade != "BRASILEIRA"))
                $this->merge([
                    'naturalidade_cidade' => null,
                    'naturalidade_estado' => null
                ]);
            $this->merge([
                'identidade' => mb_strtoupper(apenasNumerosLetras($this->identidade))
            ]);
        }

        if(isset($this->cnpj_contabil))
            $this->merge([
                'cnpj_contabil' => apenasNumeros($this->cnpj_contabil),
            ]);

        // if($user_contabil_check)
        // {
        //     $contabil = auth()->guard('contabil')->user();
        //     $this->merge([
        //         'cnpj_contabil' => $contabil->cnpj,
        //         'nome_contabil' => $contabil->nome,
        //         'email_contabil' => $contabil->email,
        //         'nome_contato_contabil' => $contabil->nome_contato,
        //         'telefone_contabil' => $contabil->telefone,
        //     ]);
        // }
    }

    public function rules()
    {
        return $this->getRules();
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
            'required_with' => 'Campo obrigatório' . $attr,
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
            'cpf_cnpj.unique' => 'Não pode solicitar pré-registro com o CNPJ fornecido devido constar no Portal como Contabilidade',
            'cnpj_contabil.unique' => 'O CNPJ fornecido já consta no Portal com outro tipo de conta',
            'email.unique' => 'E-mail já existe como Contabilidade',
            'not_in' => 'Valor não é aceito',
            'unique' => 'O valor já existe e não pode ser usado',
            'different' => 'O valor deve ser diferente do CPF do Responsável Técnico',
            'before_or_equal' => 'Deve ter 18 anos completos ou mais',
            'nome_pai_rt.required_if' => 'Por ser Sócio e Responsável Técnico o campo' . $attr . ' é obrigatório',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        if(\Route::is('externo.contabil.inserir.preregistro'))
            return [
                'cpf_cnpj' => 'CPF / CNPJ',
                'nome' => 'Nome',
                'email' => 'E-mail',
            ];

        $pessoa = null;

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

        if($this->externo->isPessoaFisica())
            $pessoa = [
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
                'titulo_eleitor' => '"Título de Eleitor"',
                'zona' => '"Zona Eleitoral"',
                'secao' => '"Seção Eleitoral"',
                'ra_reservista' => 'RA Reservista',
            ];

        if(!$this->externo->isPessoaFisica())
            $pessoa = [
                'razao_social' => '"Razão social"',
                'capital_social' => '"Capital social"',
                'nire' => '"Nire"',
                'tipo_empresa' => '"Tipo da empresa"',
                'dt_inicio_atividade' => '"Data de início das atividades"',
                'nome_fantasia' => '"Nome Fantasia"',
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
                'titulo_eleitor_rt' => '"Título de Eleitor do responsável técnico"',
                'zona_rt' => '"Zona Eleitoral do responsável técnico"',
                'secao_rt' => '"Seção Eleitoral do responsável técnico"',
                'ra_reservista_rt' => 'RA Reservista do responsável técnico',
            ];
            
        return array_merge($rules, $pessoa, $this->msg_socios);
    }
}
