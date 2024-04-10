@php
    $camposEditados = $resultado->getCamposEditados();
@endphp

<div class="card-body bg-light">
    <h5 class="font-weight-bolder mb-3">Endereço de correspondência</h5>

    <p id="cep">
        <span class="font-weight-bolder">{{ $codigos[2]['cep'] }} - CEP: </span>
        {{ isset($resultado->cep) ? $resultado->cep : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'cep',
        ])
        @endcomponent
        @if(array_key_exists('cep', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="bairro">
        <span class="font-weight-bolder">{{ $codigos[2]['bairro'] }} - Bairro: </span>
        {{ isset($resultado->bairro) ? $resultado->bairro : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'bairro',
        ])
        @endcomponent
        @if(array_key_exists('bairro', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="logradouro">
        <span class="font-weight-bolder">{{ $codigos[2]['logradouro'] }} - Logradouro: </span>
        {{ isset($resultado->logradouro) ? $resultado->logradouro : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'logradouro',
        ])
        @endcomponent
        @if(array_key_exists('logradouro', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="numero">
        <span class="font-weight-bolder">{{ $codigos[2]['numero'] }} - Número: </span>
        {{ isset($resultado->numero) ? $resultado->numero : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'numero',
        ])
        @endcomponent
        @if(array_key_exists('numero', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="complemento">
        <span class="font-weight-bolder">{{ $codigos[2]['complemento'] }} - Complemento: </span>
        {{ isset($resultado->complemento) ? $resultado->complemento : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'complemento',
        ])
        @endcomponent
        @if(array_key_exists('complemento', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="cidade">
        <span class="font-weight-bolder">{{ $codigos[2]['cidade'] }} - Município: </span>
        {{ isset($resultado->cidade) ? $resultado->cidade : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'cidade',
        ])
        @endcomponent
        @if(array_key_exists('cidade', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="uf">
        <span class="font-weight-bolder">{{ $codigos[2]['uf'] }} - Estado: </span>
        {{ isset($resultado->uf) ? $resultado->uf : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'uf',
        ])
        @endcomponent
        @if(array_key_exists('uf', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

@if(!$resultado->userExterno->isPessoaFisica())
<br>
<h5 class="font-weight-bolder mb-3">Endereço da empresa</h5>

    @if(isset($resultado->pessoaJuridica) && $resultado->pessoaJuridica->mesmoEndereco())

    <p id="checkEndEmpresa">
        <span class="font-weight-bolder">{{ $codigos[2]['checkEndEmpresa'] }} - Mesmo endereço da correspondência </span>
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'checkEndEmpresa',
        ])
        @endcomponent
        @if(array_key_exists('checkEndEmpresa', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    @else

    <p id="cep_empresa">
        <span class="font-weight-bolder">{{ $codigos[2]['cep_empresa'] }} - CEP: </span>
        {{ isset($resultado->pessoaJuridica->cep) ? $resultado->pessoaJuridica->cep : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'cep_empresa',
        ])
        @endcomponent
        @if(array_key_exists('cep_empresa', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="bairro_empresa">
        <span class="font-weight-bolder">{{ $codigos[2]['bairro_empresa'] }} - Bairro: </span>
        {{ isset($resultado->pessoaJuridica->bairro) ? $resultado->pessoaJuridica->bairro : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'bairro_empresa',
        ])
        @endcomponent
        @if(array_key_exists('bairro_empresa', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="logradouro_empresa">
        <span class="font-weight-bolder">{{ $codigos[2]['logradouro_empresa'] }} - Logradouro: </span>
        {{ isset($resultado->pessoaJuridica->logradouro) ? $resultado->pessoaJuridica->logradouro : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'logradouro_empresa',
        ])
        @endcomponent
        @if(array_key_exists('logradouro_empresa', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="numero_empresa">
        <span class="font-weight-bolder">{{ $codigos[2]['numero_empresa'] }} - Número: </span>
        {{ isset($resultado->pessoaJuridica->numero) ? $resultado->pessoaJuridica->numero : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'numero_empresa',
        ])
        @endcomponent
        @if(array_key_exists('numero_empresa', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="complemento_empresa">
        <span class="font-weight-bolder">{{ $codigos[2]['complemento_empresa'] }} - Complemento: </span>
        {{ isset($resultado->pessoaJuridica->complemento) ? $resultado->pessoaJuridica->complemento : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'complemento_empresa',
        ])
        @endcomponent
        @if(array_key_exists('complemento_empresa', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="cidade_empresa">
        <span class="font-weight-bolder">{{ $codigos[2]['cidade_empresa'] }} - Município: </span>
        {{ isset($resultado->pessoaJuridica->cidade) ? $resultado->pessoaJuridica->cidade : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'cidade_empresa',
        ])
        @endcomponent
        @if(array_key_exists('cidade_empresa', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="uf_empresa">
        <span class="font-weight-bolder">{{ $codigos[2]['uf_empresa'] }} - Estado: </span>
        {{ isset($resultado->pessoaJuridica->uf) ? $resultado->pessoaJuridica->uf : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'uf_empresa',
        ])
        @endcomponent
        @if(array_key_exists('uf_empresa', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    @endif

@endif
</div>