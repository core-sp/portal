@php
    $camposEditados = $resultado->getCamposEditados();
@endphp

<div class="card-body bg-light">

    <p id="cpf_cnpj_socio">
        <span class="font-weight-bolder">{{ $nome_campos['cpf_cnpj_socio'] }} - CPF / CNPJ: </span>
        {{-- isset($resultado->pessoaJuridica->responsavelTecnico->cpf) ? formataCpfCnpj($resultado->pessoaJuridica->responsavelTecnico->cpf) : '------' --}}
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'cpf_cnpj_socio',
        ])
        @endcomponent
        @if(array_key_exists('cpf_cnpj_socio', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

</div>