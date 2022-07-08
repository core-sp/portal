<div class="card-body bg-light">
    <p id="tipo_{{ $resultado->userExterno->isPessoaFisica() ? 'cpf' : 'cnpj' }}">
        <span class="font-weight-bolder">{{ $resultado->userExterno->isPessoaFisica() ? 'CPF' : 'CNPJ' }}: </span>
        {{ formataCpfCnpj($resultado->userExterno->cpf_cnpj) }}
    </p>

@if($resultado->userExterno->isPessoaFisica())
    <p>
        <span class="font-weight-bolder">Nome Completo: </span>
        {{ $resultado->userExterno->nome }}
    </p>

    <p id="nome_social">
        <span class="font-weight-bolder">{{ $codigos[1]['nome_social'] }} - Nome Social: </span>
        {{ isset($resultado->pessoaFisica->nome_social) ? $resultado->pessoaFisica->nome_social : '------' }}
        @component('components.justificativa_pre_registro_admin', [
                'campo' => 'nome_social',
                'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="sexo">
        <span class="font-weight-bolder">{{ $codigos[1]['sexo'] }} - Gênero: </span>
        {{ isset($resultado->pessoaFisica->sexo) ? generos()[$resultado->pessoaFisica->sexo] : '------' }}
        @component('components.justificativa_pre_registro_admin', [
                'campo' => 'sexo',
                'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="dt_nascimento">
        <span class="font-weight-bolder">{{ $codigos[1]['dt_nascimento'] }} - Data de Nascimento: </span>
        {{ isset($resultado->pessoaFisica->dt_nascimento) ? onlyDate($resultado->pessoaFisica->dt_nascimento) : '------' }}
        @component('components.justificativa_pre_registro_admin', [
                'campo' => 'dt_nascimento',
                'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="estado_civil">
        <span class="font-weight-bolder">{{ $codigos[1]['estado_civil'] }} - Estado Civil: </span>
        {{ isset($resultado->pessoaFisica->estado_civil) ? $resultado->pessoaFisica->estado_civil : '------' }}
        @component('components.justificativa_pre_registro_admin', [
                'campo' => 'estado_civil',
                'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="nacionalidade">
        <span class="font-weight-bolder">{{ $codigos[1]['nacionalidade'] }} - Nacionalidade: </span>
        {{ isset($resultado->pessoaFisica->nacionalidade) ? $resultado->pessoaFisica->nacionalidade : '------' }}
        @component('components.justificativa_pre_registro_admin', [
                'campo' => 'nacionalidade',
                'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="naturalidade">
        <span class="font-weight-bolder">{{ $codigos[1]['naturalidade'] }} - Naturalidade: </span>
        {{ isset($resultado->pessoaFisica->naturalidade) ? $resultado->pessoaFisica->naturalidade : '------' }}
        @component('components.justificativa_pre_registro_admin', [
                'campo' => 'naturalidade',
                'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="nome_mae">
        <span class="font-weight-bolder">{{ $codigos[1]['nome_mae'] }} - Nome da Mãe: </span>
        {{ isset($resultado->pessoaFisica->nome_mae) ? $resultado->pessoaFisica->nome_mae : '------' }}
        @component('components.justificativa_pre_registro_admin', [
                'campo' => 'nome_mae',
                'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="nome_pai">
        <span class="font-weight-bolder">{{ $codigos[1]['nome_pai'] }} - Nome do Pai: </span>
        {{ isset($resultado->pessoaFisica->nome_pai) ? $resultado->pessoaFisica->nome_pai : '------' }}
        @component('components.justificativa_pre_registro_admin', [
                'campo' => 'nome_pai',
                'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="tipo_identidade">
        <span class="font-weight-bolder">{{ $codigos[1]['tipo_identidade'] }} - Tipo do documento de identidade: </span>
        {{ isset($resultado->pessoaFisica->tipo_identidade) ? $resultado->pessoaFisica->tipo_identidade : '------' }}
        @component('components.justificativa_pre_registro_admin', [
                'campo' => 'tipo_identidade',
                'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="identidade">
        <span class="font-weight-bolder">{{ $codigos[1]['identidade'] }} - N° do documento de identidade: </span>
        {{ isset($resultado->pessoaFisica->identidade) ? $resultado->pessoaFisica->identidade : '------' }}
        @component('components.justificativa_pre_registro_admin', [
                'campo' => 'identidade',
                'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="orgao_emissor">
        <span class="font-weight-bolder">{{ $codigos[1]['orgao_emissor'] }} - Órgão Emissor: </span>
        {{ isset($resultado->pessoaFisica->orgao_emissor) ? $resultado->pessoaFisica->orgao_emissor : '------' }}
        @component('components.justificativa_pre_registro_admin', [
                'campo' => 'orgao_emissor',
                'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="dt_expedicao">
        <span class="font-weight-bolder">{{ $codigos[1]['dt_expedicao'] }} - Data de Expedição: </span>
        {{ isset($resultado->pessoaFisica->dt_expedicao) ? onlyDate($resultado->pessoaFisica->dt_expedicao) : '------' }}
        @component('components.justificativa_pre_registro_admin', [
                'campo' => 'dt_expedicao',
                'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

@else

    <p id="razao_social">
        <span class="font-weight-bolder">{{ $codigos[1]['razao_social'] }} - Razão Social: </span>
        {{ isset($resultado->pessoaJuridica->razao_social) ? $resultado->pessoaJuridica->razao_social : '------' }}
        @component('components.justificativa_pre_registro_admin', [
                'campo' => 'razao_social',
                'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="capital_social">
        <span class="font-weight-bolder">{{ $codigos[1]['capital_social'] }} - Capital Social: R$ </span>
        {{ isset($resultado->pessoaJuridica->capital_social) ? $resultado->pessoaJuridica->capital_social : '------' }}
        @component('components.justificativa_pre_registro_admin', [
                'campo' => 'capital_social',
                'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="nire">
        <span class="font-weight-bolder">{{ $codigos[1]['nire'] }} - NIRE: </span>
        {{ isset($resultado->pessoaJuridica->nire) ? $resultado->pessoaJuridica->nire : '------' }}
        @component('components.justificativa_pre_registro_admin', [
                'campo' => 'nire',
                'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="tipo_empresa">
        <span class="font-weight-bolder">{{ $codigos[1]['tipo_empresa'] }} - Tipo da Empresa: </span>
        {{ isset($resultado->pessoaJuridica->tipo_empresa) ? $resultado->pessoaJuridica->tipo_empresa : '------' }}
        @component('components.justificativa_pre_registro_admin', [
                'campo' => 'tipo_empresa',
                'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="dt_inicio_atividade">
        <span class="font-weight-bolder">{{ $codigos[1]['dt_inicio_atividade'] }} - Data início da atividade: </span>
        {{ isset($resultado->pessoaJuridica->dt_inicio_atividade) ? onlyDate($resultado->pessoaJuridica->dt_inicio_atividade) : '------' }}
        @component('components.justificativa_pre_registro_admin', [
                'campo' => 'dt_inicio_atividade',
                'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="inscricao_municipal">
        <span class="font-weight-bolder">{{ $codigos[1]['inscricao_municipal'] }} - Inscrição Municipal: </span>
        {{ isset($resultado->pessoaJuridica->inscricao_municipal) ? $resultado->pessoaJuridica->inscricao_municipal : '------' }}
        @component('components.justificativa_pre_registro_admin', [
                'campo' => 'inscricao_municipal',
                'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="inscricao_estadual">
        <span class="font-weight-bolder">{{ $codigos[1]['inscricao_estadual'] }} - Inscrição Estadual: </span>
        {{ isset($resultado->pessoaJuridica->inscricao_estadual) ? $resultado->pessoaJuridica->inscricao_estadual : '------' }}
        @component('components.justificativa_pre_registro_admin', [
                'campo' => 'inscricao_estadual',
                'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

@endif

    <p id="segmento">
        <span class="font-weight-bolder">{{ $codigos[1]['segmento'] }} - Segmento: </span>
        {{ isset($resultado->segmento) ? $resultado->segmento : '------' }}
        @component('components.justificativa_pre_registro_admin', [
                'campo' => 'segmento',
                'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="idregional">
        <span class="font-weight-bolder">{{ $codigos[1]['idregional'] }} - Região de Atuação: </span>
        {{ isset($resultado->idregional) ? $resultado->regional->regional : '------' }}
        @component('components.justificativa_pre_registro_admin', [
                'campo' => 'idregional',
                'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="registro_secundario">
        <span class="font-weight-bolder">Registro Secundário: </span>
        <input 
            type="text" 
            value="{{ isset($resultado->registro_secundario) ? formataRegistro($resultado->registro_secundario) : '' }}"
            name="registro_secundario"
            maxlength="20"
            {{ $resultado->atendentePodeEditar() ? '' : 'disabled' }}
        />
        @if($resultado->atendentePodeEditar())
        <button class="btn btn-outline-success btn-sm ml-2 addValorPreRegistro" type="button" value="registro_secundario">
            <i class="fas fa-save"></i>
        </button>
        @endif
    </p>

</div>