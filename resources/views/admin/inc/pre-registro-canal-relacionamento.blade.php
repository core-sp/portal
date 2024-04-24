@php
    $camposEditados = $resultado->getCamposEditados();
@endphp

<div class="card-body bg-light">

    @include('admin.inc.pre-registro-btn-remover-just', ['aba' => $abas[5], 'valor_btn' => 'parte_canal_relacionamento'])

    <p>
        <span class="font-weight-bolder">E-mail: </span>
        {{ $resultado->userExterno->email }}
    </p>

    <p id="tipo_telefone">
        <span class="font-weight-bolder">{{ $nome_campos['tipo_telefone'] }} - Tipo de telefone: </span>
        {{ isset($resultado->getTipoTelefone()[0]) ? $resultado->getTipoTelefone()[0] : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'tipo_telefone',
        ])
        @endcomponent
        @if(array_key_exists('tipo_telefone', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="telefone">
        <span class="font-weight-bolder">{{ $nome_campos['telefone'] }} - Nº de telefone: </span>
        {{ isset($resultado->getTelefone()[0]) ? $resultado->getTelefone()[0] : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'telefone',
        ])
        @endcomponent
        @if(array_key_exists('telefone', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    @if($resultado->tipoTelefoneCelular())
    <p id="opcional_celular">
        <span class="font-weight-bolder">{{ $nome_campos['opcional_celular'] }} <small class="font-weight-bolder">(opcional)</small> - Opções de comunicação 
            <small class="font-weight-bolder">({{ implode(', ', opcoes_celular()) }})</small>: </span>
        {{ isset($resultado->getOpcionalCelular()[0]) ? implode(', ', $resultado->getOpcionalCelular()[0]) : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'opcional_celular',
        ])
        @endcomponent
        @if(array_key_exists('opcional_celular', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>
    @endif

    <p id="tipo_telefone_1">
        <span class="font-weight-bolder">{{ $nome_campos['tipo_telefone_1'] }} 
            <small class="font-weight-bolder">(opcional)</small> - Tipo de telefone: </span>
        {{ isset($resultado->getTipoTelefone()[1]) ? $resultado->getTipoTelefone()[1] : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'tipo_telefone_1',
        ])
        @endcomponent
        @if(array_key_exists('tipo_telefone_1', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="telefone_1">
        <span class="font-weight-bolder">{{ $nome_campos['telefone_1'] }} 
            <small class="font-weight-bolder">(opcional)</small> - Nº de telefone: </span>
        {{ isset($resultado->getTelefone()[1]) ? $resultado->getTelefone()[1] : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'telefone_1',
        ])
        @endcomponent
        @if(array_key_exists('telefone_1', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    @if($resultado->tipoTelefoneOpcionalCelular())
    <p id="opcional_celular_1">
        <span class="font-weight-bolder">{{ $nome_campos['opcional_celular_1'] }} <small class="font-weight-bolder">(opcional)</small> - Opções de comunicação 
            <small class="font-weight-bolder">({{ implode(', ', opcoes_celular()) }})</small>: </span>
        {{ isset($resultado->getOpcionalCelular()[1]) ? implode(', ', $resultado->getOpcionalCelular()[1]) : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'opcional_celular_1',
        ])
        @endcomponent
        @if(array_key_exists('opcional_celular_1', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>
    @endif

</div>