<div class="card-body">
@if(isset($resultado->contabil_id))
    <p id="cnpj_contabil">
        <span class="font-weight-bolder">{{ array_search('cnpj', $cod) }} - CNPJ: </span>
        {{ isset($resultado->contabil->cnpj) ? formataCpfCnpj($resultado->contabil->cnpj) : '------' }}
        <button class="btn btn-outline-danger btn-sm ml-2 justificativaPreRegistro" type="button" value="cnpj_contabil">
            <i class="fas fa-{{-- se tiver justificado 'edit' --}}times"></i>
        </button>
        {{-- se tiver justificado aparece o span '<span class="badge badge-warning ml-2">Justificado</span>' --}}
    </p>
    <p id="nome_contabil">
        <span class="font-weight-bolder">{{ array_search('nome', $cod) }} - Nome da contabilidade: </span>
        {{ isset($resultado->contabil->nome) ? $resultado->contabil->nome : '------' }}
        <button class="btn btn-outline-danger btn-sm ml-2 justificativaPreRegistro" type="button" value="nome_contabil">
            <i class="fas fa-times"></i>
        </button>
    </p>
    <p id="email_contabil">
        <span class="font-weight-bolder">{{ array_search('email', $cod) }} - E-mail da contabilidade: </span>
        {{ isset($resultado->contabil->email) ? $resultado->contabil->email : '------' }}
        <button class="btn btn-outline-danger btn-sm ml-2 justificativaPreRegistro" type="button" value="email_contabil">
            <i class="fas fa-times"></i>
        </button>
    </p>
    <p id="nome_contato_contabil">
        <span class="font-weight-bolder">{{ array_search('nome_contato', $cod) }} - Nome de contato da contabilidade: </span>
        {{ isset($resultado->contabil->nome_contato) ? $resultado->contabil->nome_contato : '------' }}
        <button class="btn btn-outline-danger btn-sm ml-2 justificativaPreRegistro" type="button" value="nome_contato_contabil">
            <i class="fas fa-times"></i>
        </button>
    </p>
    <p id="telefone_contabil">
        <span class="font-weight-bolder">{{ array_search('telefone', $cod) }} - Telefone da contabilidade: </span>
        {{ isset($resultado->contabil->telefone) ? $resultado->contabil->telefone : '------' }}
        <button class="btn btn-outline-danger btn-sm ml-2 justificativaPreRegistro" type="button" value="telefone_contabil">
            <i class="fas fa-times"></i>
        </button>
    </p>
@else
    <p>Sem contabilidade</p>
@endif
</div>