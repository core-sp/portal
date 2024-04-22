@php
    $camposEditados = $resultado->getCamposEditados();
@endphp

<div class="card-body bg-light">

    @if(!$resultado->pessoaJuridica->possuiRT())
    <p><i>Ainda não possui Responsável Técnico.</i></p>

    @else

    <p id="cpf_rt">
        <span class="font-weight-bolder">{{ $nome_campos['cpf_rt'] }} - CPF: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->cpf) ? formataCpfCnpj($resultado->pessoaJuridica->responsavelTecnico->cpf) : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'cpf_rt',
        ])
        @endcomponent
        @if(array_key_exists('cpf_rt', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="registro">
        <span class="font-weight-bolder">{{ $nome_campos['registro'] }} - Registro: </span>
        <input 
            type="text" 
            value="{{ isset($resultado->pessoaJuridica->responsavelTecnico->registro) ? formataRegistro($resultado->pessoaJuridica->responsavelTecnico->registro) : '' }}"
            name="registro"
            maxlength="20"
            {{ $resultado->atendentePodeEditar() ? '' : 'disabled' }}
        />
        @if($resultado->atendentePodeEditar())
        <button class="btn btn-outline-primary btn-sm ml-2 addValorPreRegistro" type="button" value="registro">
            <i class="fas fa-save"></i>
        </button>
        @endif
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'registro',
        ])
        @endcomponent
    </p>

    <p id="nome_rt">
        <span class="font-weight-bolder">{{ $nome_campos['nome_rt'] }} - Nome Completo: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->nome) ? $resultado->pessoaJuridica->responsavelTecnico->nome : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'nome_rt',
        ])
        @endcomponent
        @if(array_key_exists('nome_rt', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="nome_social_rt">
        <span class="font-weight-bolder">{{ $nome_campos['nome_social_rt'] }} - Nome Social: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->nome_social) ? $resultado->pessoaJuridica->responsavelTecnico->nome_social : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'nome_social_rt',
        ])
        @endcomponent
        @if(array_key_exists('nome_social_rt', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="dt_nascimento_rt">
        <span class="font-weight-bolder">{{ $nome_campos['dt_nascimento_rt'] }} - Data de Nascimento: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->dt_nascimento) ? onlyDate($resultado->pessoaJuridica->responsavelTecnico->dt_nascimento) : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'dt_nascimento_rt',
        ])
        @endcomponent
        @if(array_key_exists('dt_nascimento_rt', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="sexo_rt">
        <span class="font-weight-bolder">{{ $nome_campos['sexo_rt'] }} - Gênero: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->sexo) ? generos()[$resultado->pessoaJuridica->responsavelTecnico->sexo] : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'sexo_rt',
        ])
        @endcomponent
        @if(array_key_exists('sexo_rt', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="tipo_identidade_rt">
        <span class="font-weight-bolder">{{ $nome_campos['tipo_identidade_rt'] }} - Tipo do documento de identidade: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->tipo_identidade) ? $resultado->pessoaJuridica->responsavelTecnico->tipo_identidade : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'tipo_identidade_rt',
        ])
        @endcomponent
        @if(array_key_exists('tipo_identidade_rt', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="identidade_rt">
        <span class="font-weight-bolder">{{ $nome_campos['identidade_rt'] }} - N° do documento de identidade: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->identidade) ? $resultado->pessoaJuridica->responsavelTecnico->identidade : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'identidade_rt',
        ])
        @endcomponent
        @if(array_key_exists('identidade_rt', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="orgao_emissor_rt">
        <span class="font-weight-bolder">{{ $nome_campos['orgao_emissor_rt'] }} - Órgão Emissor: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->orgao_emissor) ? $resultado->pessoaJuridica->responsavelTecnico->orgao_emissor : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'orgao_emissor_rt',
        ])
        @endcomponent
        @if(array_key_exists('orgao_emissor_rt', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="dt_expedicao_rt">
        <span class="font-weight-bolder">{{ $nome_campos['dt_expedicao_rt'] }} - Data de Expedição: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->dt_expedicao) ? onlyDate($resultado->pessoaJuridica->responsavelTecnico->dt_expedicao) : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'dt_expedicao_rt',
        ])
        @endcomponent
        @if(array_key_exists('dt_expedicao_rt', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="titulo_eleitor_rt">
        <span class="font-weight-bolder">{{ $nome_campos['titulo_eleitor_rt'] }} - Título de Eleitor: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->titulo_eleitor) ? $resultado->pessoaJuridica->responsavelTecnico->titulo_eleitor : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'titulo_eleitor_rt',
        ])
        @endcomponent
        @if(array_key_exists('titulo_eleitor_rt', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="zona_rt">
        <span class="font-weight-bolder">{{ $nome_campos['zona_rt'] }} - Zona Eleitoral: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->zona) ? $resultado->pessoaJuridica->responsavelTecnico->zona : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'zona_rt',
        ])
        @endcomponent
        @if(array_key_exists('zona_rt', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="secao_rt">
        <span class="font-weight-bolder">{{ $nome_campos['secao_rt'] }} - Seção Eleitoral: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->secao) ? $resultado->pessoaJuridica->responsavelTecnico->secao : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'secao_rt',
        ])
        @endcomponent
        @if(array_key_exists('secao_rt', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="ra_reservista_rt">
        <span class="font-weight-bolder">{{ $nome_campos['ra_reservista_rt'] }} - RA Reservista: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->ra_reservista) ? $resultado->pessoaJuridica->responsavelTecnico->ra_reservista : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'ra_reservista_rt',
        ])
        @endcomponent
        @if(array_key_exists('ra_reservista_rt', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="cep_rt">
        <span class="font-weight-bolder">{{ $nome_campos['cep_rt'] }} - CEP: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->cep) ? $resultado->pessoaJuridica->responsavelTecnico->cep : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'cep_rt',
        ])
        @endcomponent
        @if(array_key_exists('cep_rt', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="bairro_rt">
        <span class="font-weight-bolder">{{ $nome_campos['bairro_rt'] }} - Bairro: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->bairro) ? $resultado->pessoaJuridica->responsavelTecnico->bairro : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'bairro_rt',
        ])
        @endcomponent
        @if(array_key_exists('bairro_rt', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="logradouro_rt">
        <span class="font-weight-bolder">{{ $nome_campos['logradouro_rt'] }} - Logradouro: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->logradouro) ? $resultado->pessoaJuridica->responsavelTecnico->logradouro : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'logradouro_rt',
        ])
        @endcomponent
        @if(array_key_exists('logradouro_rt', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="numero_rt">
        <span class="font-weight-bolder">{{ $nome_campos['numero_rt'] }} - Número: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->numero) ? $resultado->pessoaJuridica->responsavelTecnico->numero : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'numero_rt',
        ])
        @endcomponent
        @if(array_key_exists('numero_rt', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="complemento_rt">
        <span class="font-weight-bolder">{{ $nome_campos['complemento_rt'] }} - Complemento: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->complemento) ? $resultado->pessoaJuridica->responsavelTecnico->complemento : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'complemento_rt',
        ])
        @endcomponent
        @if(array_key_exists('complemento_rt', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="cidade_rt">
        <span class="font-weight-bolder">{{ $nome_campos['cidade_rt'] }} - Município: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->cidade) ? $resultado->pessoaJuridica->responsavelTecnico->cidade : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'cidade_rt',
        ])
        @endcomponent
        @if(array_key_exists('cidade_rt', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="uf_rt">
        <span class="font-weight-bolder">{{ $nome_campos['uf_rt'] }} - Estado: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->uf) ? $resultado->pessoaJuridica->responsavelTecnico->uf : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'uf_rt',
        ])
        @endcomponent
        @if(array_key_exists('uf_rt', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="nome_mae_rt">
        <span class="font-weight-bolder">{{ $nome_campos['nome_mae_rt'] }} - Nome da Mãe: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->nome_mae) ? $resultado->pessoaJuridica->responsavelTecnico->nome_mae : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'nome_mae_rt',
        ])
        @endcomponent
        @if(array_key_exists('nome_mae_rt', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="nome_pai_rt">
        <span class="font-weight-bolder">{{ $nome_campos['nome_pai_rt'] }} - Nome do Pai: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->nome_pai) ? $resultado->pessoaJuridica->responsavelTecnico->nome_pai : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'nome_pai_rt',
        ])
        @endcomponent
        @if(array_key_exists('nome_pai_rt', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    @endif
</div>