<div class="card-body bg-light">
    <h5 class="font-weight-bolder mb-3">Endereço de correspondência</h5>

    <p id="cep">
        <span class="font-weight-bolder">{{ $codigos[2]['cep'] }} - CEP: </span>
        {{ isset($resultado->cep) ? $resultado->cep : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'cep',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="bairro">
        <span class="font-weight-bolder">{{ $codigos[2]['bairro'] }} - Bairro: </span>
        {{ isset($resultado->bairro) ? $resultado->bairro : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'bairro',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="logradouro">
        <span class="font-weight-bolder">{{ $codigos[2]['logradouro'] }} - Logradouro: </span>
        {{ isset($resultado->logradouro) ? $resultado->logradouro : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'logradouro',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="numero">
        <span class="font-weight-bolder">{{ $codigos[2]['numero'] }} - Número: </span>
        {{ isset($resultado->numero) ? $resultado->numero : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'numero',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="complemento">
        <span class="font-weight-bolder">{{ $codigos[2]['complemento'] }} - Complemento: </span>
        {{ isset($resultado->complemento) ? $resultado->complemento : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'complemento',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="cidade">
        <span class="font-weight-bolder">{{ $codigos[2]['cidade'] }} - Município: </span>
        {{ isset($resultado->cidade) ? $resultado->cidade : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'cidade',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="uf">
        <span class="font-weight-bolder">{{ $codigos[2]['uf'] }} - Estado: </span>
        {{ isset($resultado->uf) ? $resultado->uf : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'uf',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

@if(!$resultado->userExterno->isPessoaFisica())
<br>
<h5 class="font-weight-bolder mb-3">Endereço da empresa</h5>

    @if($resultado->pessoaJuridica->mesmoEndereco())

    <p id="checkEndEmpresa">
        <span class="font-weight-bolder">{{ $codigos[2]['checkEndEmpresa'] }} - Mesmo endereço da correspondência </span>
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'checkEndEmpresa',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    @else

    <p id="cep_empresa">
        <span class="font-weight-bolder">{{ $codigos[2]['cep_empresa'] }} - CEP: </span>
        {{ isset($resultado->pessoaJuridica->cep) ? $resultado->pessoaJuridica->cep : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'cep_empresa',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="bairro_empresa">
        <span class="font-weight-bolder">{{ $codigos[2]['bairro_empresa'] }} - Bairro: </span>
        {{ isset($resultado->pessoaJuridica->bairro) ? $resultado->pessoaJuridica->bairro : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'bairro_empresa',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="logradouro_empresa">
        <span class="font-weight-bolder">{{ $codigos[2]['logradouro_empresa'] }} - Logradouro: </span>
        {{ isset($resultado->pessoaJuridica->logradouro) ? $resultado->pessoaJuridica->logradouro : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'logradouro_empresa',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="numero_empresa">
        <span class="font-weight-bolder">{{ $codigos[2]['numero_empresa'] }} - Número: </span>
        {{ isset($resultado->pessoaJuridica->numero) ? $resultado->pessoaJuridica->numero : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'numero_empresa',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="complemento_empresa">
        <span class="font-weight-bolder">{{ $codigos[2]['complemento_empresa'] }} - Complemento: </span>
        {{ isset($resultado->pessoaJuridica->complemento) ? $resultado->pessoaJuridica->complemento : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'complemento_empresa',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="cidade_empresa">
        <span class="font-weight-bolder">{{ $codigos[2]['cidade_empresa'] }} - Município: </span>
        {{ isset($resultado->pessoaJuridica->cidade) ? $resultado->pessoaJuridica->cidade : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'cidade_empresa',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="uf_empresa">
        <span class="font-weight-bolder">{{ $codigos[2]['uf_empresa'] }} - Estado: </span>
        {{ isset($resultado->pessoaJuridica->uf) ? $resultado->pessoaJuridica->uf : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'uf_empresa',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    @endif

@endif
</div>