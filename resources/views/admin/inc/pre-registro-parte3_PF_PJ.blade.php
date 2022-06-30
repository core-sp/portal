<div class="card-body">
    <h5 class="font-weight-bolder mb-3">Endereço de correspondência</h5>

    <p id="cep">
        <span class="font-weight-bolder">{{ array_search('cep', $codPre) }} - CEP: </span>
        {{ isset($resultado->cep) ? $resultado->cep : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'cep',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="bairro">
        <span class="font-weight-bolder">{{ array_search('bairro', $codPre) }} - Bairro: </span>
        {{ isset($resultado->bairro) ? $resultado->bairro : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'bairro',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="logradouro">
        <span class="font-weight-bolder">{{ array_search('logradouro', $codPre) }} - Logradouro: </span>
        {{ isset($resultado->logradouro) ? $resultado->logradouro : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'logradouro',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="numero">
        <span class="font-weight-bolder">{{ array_search('numero', $codPre) }} - Número: </span>
        {{ isset($resultado->numero) ? $resultado->numero : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'numero',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="complemento">
        <span class="font-weight-bolder">{{ array_search('complemento', $codPre) }} - Complemento: </span>
        {{ isset($resultado->complemento) ? $resultado->complemento : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'complemento',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="cidade">
        <span class="font-weight-bolder">{{ array_search('cidade', $codPre) }} - Município: </span>
        {{ isset($resultado->cidade) ? $resultado->cidade : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'cidade',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="uf">
        <span class="font-weight-bolder">{{ array_search('uf', $codPre) }} - Estado: </span>
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
        <span>Mesmo endereço da correspondência </span>
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'checkEndEmpresa',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    @else

    <p id="cep_empresa">
        <span class="font-weight-bolder">{{ array_search('cep', $codCnpj) }} - CEP: </span>
        {{ isset($resultado->pessoaJuridica->cep) ? $resultado->pessoaJuridica->cep : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'cep_empresa',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="bairro_empresa">
        <span class="font-weight-bolder">{{ array_search('bairro', $codCnpj) }} - Bairro: </span>
        {{ isset($resultado->pessoaJuridica->bairro) ? $resultado->pessoaJuridica->bairro : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'bairro_empresa',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="logradouro_empresa">
        <span class="font-weight-bolder">{{ array_search('logradouro', $codCnpj) }} - Logradouro: </span>
        {{ isset($resultado->pessoaJuridica->logradouro) ? $resultado->pessoaJuridica->logradouro : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'logradouro_empresa',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="numero_empresa">
        <span class="font-weight-bolder">{{ array_search('numero', $codCnpj) }} - Número: </span>
        {{ isset($resultado->pessoaJuridica->numero) ? $resultado->pessoaJuridica->numero : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'numero_empresa',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="complemento_empresa">
        <span class="font-weight-bolder">{{ array_search('complemento', $codCnpj) }} - Complemento: </span>
        {{ isset($resultado->pessoaJuridica->complemento) ? $resultado->pessoaJuridica->complemento : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'complemento_empresa',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="cidade_empresa">
        <span class="font-weight-bolder">{{ array_search('cidade', $codCnpj) }} - Município: </span>
        {{ isset($resultado->pessoaJuridica->cidade) ? $resultado->pessoaJuridica->cidade : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'cidade_empresa',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="uf_empresa">
        <span class="font-weight-bolder">{{ array_search('uf', $codCnpj) }} - Estado: </span>
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