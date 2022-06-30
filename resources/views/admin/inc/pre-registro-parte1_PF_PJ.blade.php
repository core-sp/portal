<div class="card-body">
@if(isset($resultado->contabil_id))
    <p id="cnpj_contabil">
        <span class="font-weight-bolder">{{ array_search('cnpj', $cod) }} - CNPJ: </span>
        {{ isset($resultado->contabil->cnpj) ? formataCpfCnpj($resultado->contabil->cnpj) : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'cnpj_contabil',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="nome_contabil">
        <span class="font-weight-bolder">{{ array_search('nome', $cod) }} - Nome da contabilidade: </span>
        {{ isset($resultado->contabil->nome) ? $resultado->contabil->nome : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'nome_contabil',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="email_contabil">
        <span class="font-weight-bolder">{{ array_search('email', $cod) }} - E-mail da contabilidade: </span>
        {{ isset($resultado->contabil->email) ? $resultado->contabil->email : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'email_contabil',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="nome_contato_contabil">
        <span class="font-weight-bolder">{{ array_search('nome_contato', $cod) }} - Nome de contato da contabilidade: </span>
        {{ isset($resultado->contabil->nome_contato) ? $resultado->contabil->nome_contato : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'nome_contato_contabil',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="telefone_contabil">
        <span class="font-weight-bolder">{{ array_search('telefone', $cod) }} - Telefone da contabilidade: </span>
        {{ isset($resultado->contabil->telefone) ? $resultado->contabil->telefone : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'telefone_contabil',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

@else
    <p>Sem contabilidade</p>
@endif
</div>