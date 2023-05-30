@php
    $camposEditados = $resultado->getCamposEditados();
    $arrayJustificativas = $resultado->getJustificativaArray();
@endphp

<div class="card-body bg-light">
    <p id="cnpj_contabil">
        <span class="font-weight-bolder">{{ $codigos[0]['cnpj_contabil'] }} - CNPJ: </span>
        {{ isset($resultado->contabil->cnpj) ? formataCpfCnpj($resultado->contabil->cnpj) : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'cnpj_contabil',
            'resultado' => $arrayJustificativas
        ])
        @endcomponent
        @if(array_key_exists('cnpj_contabil', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="nome_contabil">
        <span class="font-weight-bolder">{{ $codigos[0]['nome_contabil'] }} - Nome da contabilidade: </span>
        {{ isset($resultado->contabil->nome) ? $resultado->contabil->nome : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'nome_contabil',
            'resultado' => $arrayJustificativas
        ])
        @endcomponent
        @if(array_key_exists('nome_contabil', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="email_contabil">
        <span class="font-weight-bolder">{{ $codigos[0]['email_contabil'] }} - E-mail da contabilidade: </span>
        {{ isset($resultado->contabil->email) ? $resultado->contabil->email : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'email_contabil',
            'resultado' => $arrayJustificativas
        ])
        @endcomponent
        @if(array_key_exists('email_contabil', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="nome_contato_contabil">
        <span class="font-weight-bolder">{{ $codigos[0]['nome_contato_contabil'] }} - Nome de contato da contabilidade: </span>
        {{ isset($resultado->contabil->nome_contato) ? $resultado->contabil->nome_contato : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'nome_contato_contabil',
            'resultado' => $arrayJustificativas
        ])
        @endcomponent
        @if(array_key_exists('nome_contato_contabil', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="telefone_contabil">
        <span class="font-weight-bolder">{{ $codigos[0]['telefone_contabil'] }} - Telefone da contabilidade: </span>
        {{ isset($resultado->contabil->telefone) ? $resultado->contabil->telefone : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'telefone_contabil',
            'resultado' => $arrayJustificativas
        ])
        @endcomponent
        @if(array_key_exists('telefone_contabil', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>
</div>