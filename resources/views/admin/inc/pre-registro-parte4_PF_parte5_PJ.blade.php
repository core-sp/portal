@php
    $camposEditados = $resultado->getCamposEditados();
    $arrayJustificativas = $resultado->getJustificativaArray();
@endphp

<div class="card-body bg-light">

    <p>
        <span class="font-weight-bolder">E-mail: </span>
        {{ $resultado->userExterno->email }}
    </p>

    <p id="tipo_telefone">
        <span class="font-weight-bolder">{{ $codigos[4]['tipo_telefone'] }} - Tipo de telefone: </span>
        {{ isset($resultado->getTipoTelefone()[0]) ? $resultado->getTipoTelefone()[0] : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'tipo_telefone',
            'resultado' => $arrayJustificativas
        ])
        @endcomponent
        @if(array_key_exists('tipo_telefone', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="telefone">
        <span class="font-weight-bolder">{{ $codigos[4]['telefone'] }} - Nº de telefone: </span>
        {{ isset($resultado->getTelefone()[0]) ? $resultado->getTelefone()[0] : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'telefone',
            'resultado' => $arrayJustificativas
        ])
        @endcomponent
        @if(array_key_exists('telefone', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    @if($resultado->getTipoTelefone()[0] == mb_strtoupper(tipos_contatos()[0], 'UTF-8'))
    <p id="opcional_celular">
        <span class="font-weight-bolder">{{ $codigos[4]['opcional_celular'] }} <small class="font-weight-bolder">(opcional)</small> - Opções de comunicação 
            <small class="font-weight-bolder">({{ implode(', ', opcoes_celular()) }})</small>: </span>
        {{ isset($resultado->getOpcionalCelular()[0]) ? implode(', ', $resultado->getOpcionalCelular()[0]) : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'opcional_celular',
            'resultado' => $arrayJustificativas
        ])
        @endcomponent
        @if(array_key_exists('opcional_celular', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>
    @endif

    <p id="tipo_telefone_1">
        <span class="font-weight-bolder">{{ $codigos[4]['tipo_telefone_1'] }} 
            <small class="font-weight-bolder">(opcional)</small> - Tipo de telefone: </span>
        {{ isset($resultado->getTipoTelefone()[1]) ? $resultado->getTipoTelefone()[1] : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'tipo_telefone_1',
            'resultado' => $arrayJustificativas
        ])
        @endcomponent
        @if(array_key_exists('tipo_telefone_1', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="telefone_1">
        <span class="font-weight-bolder">{{ $codigos[4]['telefone_1'] }} 
            <small class="font-weight-bolder">(opcional)</small> - Nº de telefone: </span>
        {{ isset($resultado->getTelefone()[1]) ? $resultado->getTelefone()[1] : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'telefone_1',
            'resultado' => $arrayJustificativas
        ])
        @endcomponent
        @if(array_key_exists('telefone_1', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    @if(isset($resultado->getTipoTelefone()[1]) && ($resultado->getTipoTelefone()[1] == mb_strtoupper(tipos_contatos()[0], 'UTF-8')))
    <p id="opcional_celular_1">
        <span class="font-weight-bolder">{{ $codigos[4]['opcional_celular_1'] }} <small class="font-weight-bolder">(opcional)</small> - Opções de comunicação 
            <small class="font-weight-bolder">({{ implode(', ', opcoes_celular()) }})</small>: </span>
        {{ isset($resultado->getOpcionalCelular()[1]) ? implode(', ', $resultado->getOpcionalCelular()[1]) : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'opcional_celular_1',
            'resultado' => $arrayJustificativas
        ])
        @endcomponent
        @if(array_key_exists('opcional_celular_1', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>
    @endif

</div>