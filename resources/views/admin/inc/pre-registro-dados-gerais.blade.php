@php
    $camposEditados = $resultado->getCamposEditados();
@endphp

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
            'preRegistro' => $resultado,
            'campo' => 'nome_social',
        ])
        @endcomponent
        @if(array_key_exists('nome_social', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="sexo">
        <span class="font-weight-bolder">{{ $codigos[1]['sexo'] }} - Gênero: </span>
        {{ isset($resultado->pessoaFisica->sexo) ? generos()[$resultado->pessoaFisica->sexo] : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'sexo',
        ])
        @endcomponent
        @if(array_key_exists('sexo', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="dt_nascimento">
        <span class="font-weight-bolder">{{ $codigos[1]['dt_nascimento'] }} - Data de Nascimento: </span>
        {{ isset($resultado->pessoaFisica->dt_nascimento) ? onlyDate($resultado->pessoaFisica->dt_nascimento) : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'dt_nascimento',
        ])
        @endcomponent
        @if(array_key_exists('dt_nascimento', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="estado_civil">
        <span class="font-weight-bolder">{{ $codigos[1]['estado_civil'] }} - Estado Civil: </span>
        {{ isset($resultado->pessoaFisica->estado_civil) ? $resultado->pessoaFisica->estado_civil : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'estado_civil',
        ])
        @endcomponent
        @if(array_key_exists('estado_civil', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="nacionalidade">
        <span class="font-weight-bolder">{{ $codigos[1]['nacionalidade'] }} - Nacionalidade: </span>
        {{ isset($resultado->pessoaFisica->nacionalidade) ? $resultado->pessoaFisica->nacionalidade : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'nacionalidade',
        ])
        @endcomponent
        @if(array_key_exists('nacionalidade', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="naturalidade_cidade">
        <span class="font-weight-bolder">{{ $codigos[1]['naturalidade_cidade'] }} - Naturalidade - Cidade: </span>
        {{ isset($resultado->pessoaFisica->naturalidade_cidade) ? $resultado->pessoaFisica->naturalidade_cidade : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'naturalidade_cidade',
        ])
        @endcomponent
        @if(array_key_exists('naturalidade_cidade', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="naturalidade_estado">
        <span class="font-weight-bolder">{{ $codigos[1]['naturalidade_estado'] }} - Naturalidade - Estado: </span>
        {{ isset($resultado->pessoaFisica->naturalidade_estado) ? $resultado->pessoaFisica->naturalidade_estado : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'naturalidade_estado',
        ])
        @endcomponent
        @if(array_key_exists('naturalidade_estado', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="nome_mae">
        <span class="font-weight-bolder">{{ $codigos[1]['nome_mae'] }} - Nome da Mãe: </span>
        {{ isset($resultado->pessoaFisica->nome_mae) ? $resultado->pessoaFisica->nome_mae : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'nome_mae',
        ])
        @endcomponent
        @if(array_key_exists('nome_mae', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="nome_pai">
        <span class="font-weight-bolder">{{ $codigos[1]['nome_pai'] }} - Nome do Pai: </span>
        {{ isset($resultado->pessoaFisica->nome_pai) ? $resultado->pessoaFisica->nome_pai : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'nome_pai',
        ])
        @endcomponent
        @if(array_key_exists('nome_pai', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="tipo_identidade">
        <span class="font-weight-bolder">{{ $codigos[1]['tipo_identidade'] }} - Tipo do documento de identidade: </span>
        {{ isset($resultado->pessoaFisica->tipo_identidade) ? $resultado->pessoaFisica->tipo_identidade : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'tipo_identidade',
        ])
        @endcomponent
        @if(array_key_exists('tipo_identidade', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="identidade">
        <span class="font-weight-bolder">{{ $codigos[1]['identidade'] }} - N° do documento de identidade: </span>
        {{ isset($resultado->pessoaFisica->identidade) ? $resultado->pessoaFisica->identidade : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'identidade',
        ])
        @endcomponent
        @if(array_key_exists('identidade', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="orgao_emissor">
        <span class="font-weight-bolder">{{ $codigos[1]['orgao_emissor'] }} - Órgão Emissor: </span>
        {{ isset($resultado->pessoaFisica->orgao_emissor) ? $resultado->pessoaFisica->orgao_emissor : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'orgao_emissor',
        ])
        @endcomponent
        @if(array_key_exists('orgao_emissor', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="dt_expedicao">
        <span class="font-weight-bolder">{{ $codigos[1]['dt_expedicao'] }} - Data de Expedição: </span>
        {{ isset($resultado->pessoaFisica->dt_expedicao) ? onlyDate($resultado->pessoaFisica->dt_expedicao) : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'dt_expedicao',
        ])
        @endcomponent
        @if(array_key_exists('dt_expedicao', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="titulo_eleitor">
        <span class="font-weight-bolder">{{ $codigos[1]['titulo_eleitor'] }} - Título de Eleitor: </span>
        {{ isset($resultado->pessoaFisica->titulo_eleitor) ? $resultado->pessoaFisica->titulo_eleitor : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'titulo_eleitor',
        ])
        @endcomponent
        @if(array_key_exists('titulo_eleitor', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="zona">
        <span class="font-weight-bolder">{{ $codigos[1]['zona'] }} - Zona Eleitoral: </span>
        {{ isset($resultado->pessoaFisica->zona) ? $resultado->pessoaFisica->zona : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'zona',
        ])
        @endcomponent
        @if(array_key_exists('zona', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="secao">
        <span class="font-weight-bolder">{{ $codigos[1]['secao'] }} - Seção Eleitoral: </span>
        {{ isset($resultado->pessoaFisica->secao) ? $resultado->pessoaFisica->secao : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'secao',
        ])
        @endcomponent
        @if(array_key_exists('secao', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="ra_reservista">
        <span class="font-weight-bolder">{{ $codigos[1]['ra_reservista'] }} - RA Reservista: </span>
        {{ isset($resultado->pessoaFisica->ra_reservista) ? $resultado->pessoaFisica->ra_reservista : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'ra_reservista',
        ])
        @endcomponent
        @if(array_key_exists('ra_reservista', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

@else

    <p id="razao_social">
        <span class="font-weight-bolder">{{ $codigos[1]['razao_social'] }} - Razão Social: </span>
        {{ isset($resultado->pessoaJuridica->razao_social) ? $resultado->pessoaJuridica->razao_social : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'razao_social',
        ])
        @endcomponent
        @if(array_key_exists('razao_social', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="nome_fantasia">
        <span class="font-weight-bolder">{{ $codigos[1]['nome_fantasia'] }} - Nome Fantasia: </span>
        {{ isset($resultado->pessoaJuridica->nome_fantasia) ? $resultado->pessoaJuridica->nome_fantasia : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'nome_fantasia',
        ])
        @endcomponent
        @if(array_key_exists('nome_fantasia', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="capital_social">
        <span class="font-weight-bolder">{{ $codigos[1]['capital_social'] }} - Capital Social: R$ </span>
        {{ isset($resultado->pessoaJuridica->capital_social) ? $resultado->pessoaJuridica->capital_social : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'capital_social',
        ])
        @endcomponent
        @if(array_key_exists('capital_social', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="nire">
        <span class="font-weight-bolder">{{ $codigos[1]['nire'] }} - NIRE: </span>
        {{ isset($resultado->pessoaJuridica->nire) ? $resultado->pessoaJuridica->nire : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'nire',
        ])
        @endcomponent
        @if(array_key_exists('nire', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="tipo_empresa">
        <span class="font-weight-bolder">{{ $codigos[1]['tipo_empresa'] }} - Tipo da Empresa: </span>
        {{ isset($resultado->pessoaJuridica->tipo_empresa) ? $resultado->pessoaJuridica->tipo_empresa : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'tipo_empresa',
        ])
        @endcomponent
        @if(array_key_exists('tipo_empresa', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="dt_inicio_atividade">
        <span class="font-weight-bolder">{{ $codigos[1]['dt_inicio_atividade'] }} - Data início da atividade: </span>
        {{ isset($resultado->pessoaJuridica->dt_inicio_atividade) ? onlyDate($resultado->pessoaJuridica->dt_inicio_atividade) : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'dt_inicio_atividade',
        ])
        @endcomponent
        @if(array_key_exists('dt_inicio_atividade', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>


@endif

    <p id="segmento">
        <span class="font-weight-bolder">{{ $codigos[1]['segmento'] }} - Segmento: </span>
        {{ isset($resultado->segmento) ? $resultado->segmento : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'segmento',
        ])
        @endcomponent
        @if(array_key_exists('segmento', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="idregional">
        <span class="font-weight-bolder">{{ $codigos[1]['idregional'] }} - Região de Atuação: </span>
        {{ isset($resultado->idregional) ? $resultado->regional->regional : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'idregional',
        ])
        @endcomponent
        @if(array_key_exists('idregional', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
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
        <button class="btn btn-outline-primary btn-sm ml-2 addValorPreRegistro" type="button" value="registro_secundario">
            <i class="fas fa-save"></i>
        </button>
        @endif
    </p>

</div>