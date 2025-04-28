@component('components.justificativa_pre_registro', [
    'resultado' => $resultado,
    'correcoes' => $resultado->getCodigosJustificadosByAba($nome_campos)
])
@endcomponent

<small class="text-muted text-left">
    <em>
        <span class="font-weight-bolder">Observações:</span>
        <br>
        {!! auth()->guard('contabil')->check() ? 
            'Para alterar os dados abaixo, acesse a aba <a class="text-primary" href="' . route('externo.editar.view') . '"><i>Alterar dados do cadastro</i></a> do menu.' : 
            'Se registro realizado por Escritório de Contabilidade, inserir os dados solicitados abaixo, caso contrário, avançar esta etapa sem o preenchimento. <br>
            <span class="text-danger font-weight-bolder">*</span> Campos obrigatórios caso preencha CNPJ da contabilidade <br>
            Caso a contabilidade possua login no Portal, somente ela poderá alterar os dados.'
        !!}
    </em>
</small>

<div class="form-row mb-3 mt-4">
    <div class="col-sm mb-2-576">
        <label {{ !auth()->guard('contabil')->check() ? 'for=cnpj_contabil' : '' }}>{{ $nome_campos['cnpj_contabil'] }} - CNPJ</label> 
        <small class="text-{{ auth()->guard('contabil')->check() ? 'black' : 'muted' }} text-left ml-2">
            <em>
                {{ auth()->guard('contabil')->check() ? 
                    'Somente o representante pode alterar / remover o CNPJ da contabilidade' : 'Após inserir um CNPJ válido aguarde ' . $resultado::TOTAL_HIST_DIAS_UPDATE * 24 . 'h caso queira trocar' }}
            </em>
        </small>
        <input
            class="{{ $classe }} form-control cnpjInput {{ $errors->has('cnpj_contabil') ? 'is-invalid' : '' }}"
            value="{{ empty(old('cnpj_contabil')) && isset($resultado->contabil->cnpj) ? $resultado->contabil->cnpj : old('cnpj_contabil') }}"
            placeholder="00.000.000/0000-00"
        @if(!auth()->guard('contabil')->check())
            name="cnpj_contabil"
            id="cnpj_contabil"
            type="text"
        @else
            readonly
        @endif
        />
        @if($errors->has('cnpj_contabil'))
        <div class="invalid-feedback">
            {{ $errors->first('cnpj_contabil') }}
        </div>
        @endif
    </div>
</div>

<fieldset id="campos_contabil" {{ isset($resultado->contabil->cnpj) && !$resultado->contabil->possuiLogin() ? '' : 'disabled' }}>
    <div class="form-row mb-2">
        <div class="col-sm mb-2-576">
            <label for="nome_contabil">{{ $nome_campos['nome_contabil'] }} - Nome da Contabilidade <span class="text-danger">*</span></label>
            <input
                name="nome_contabil"
                id="nome_contabil"
                type="text"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('nome_contabil') ? 'is-invalid' : '' }} obrigatorio"
                value="{{ empty(old('nome_contabil')) && isset($resultado->contabil->nome) ? $resultado->contabil->nome : old('nome_contabil') }}"
                maxlength="191"
            />
            @if($errors->has('nome_contabil'))
            <div class="invalid-feedback">
                {{ $errors->first('nome_contabil') }}
            </div>
            @endif
        </div>
    </div>

    <div class="form-row mb-2">
        <div class="col-sm mb-2-576">
            <label for="email_contabil">{{ $nome_campos['email_contabil'] }} - E-mail <span class="text-danger">*</span></label>
            <input
                name="email_contabil"
                id="email_contabil"
                type="email"
                class="{{ $classe }} form-control {{ $errors->has('email_contabil') ? 'is-invalid' : '' }} obrigatorio"
                value="{{ empty(old('email_contabil')) && isset($resultado->contabil->email) ? $resultado->contabil->email : old('email_contabil') }}"
                maxlength="191"
            />
            @if($errors->has('email_contabil'))
            <div class="invalid-feedback">
                {{ $errors->first('email_contabil') }}
            </div>
            @endif
        </div>
    </div>

    <div class="form-row mb-2">
        <div class="col-sm mb-2-576">
            <label for="nome_contato_contabil">{{ $nome_campos['nome_contato_contabil'] }} - Nome de Contato <span class="text-danger">*</span></label>
            <input
                name="nome_contato_contabil"
                id="nome_contato_contabil"
                type="text"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('nome_contato_contabil') ? 'is-invalid' : '' }} obrigatorio"
                value="{{ empty(old('nome_contato_contabil')) && isset($resultado->contabil->nome_contato) ? $resultado->contabil->nome_contato : old('nome_contato_contabil') }}"
                maxlength="191"
            />
            @if($errors->has('nome_contato_contabil'))
            <div class="invalid-feedback">
                {{ $errors->first('nome_contato_contabil') }}
            </div>
            @endif
        </div>
        <div class="col-sm mb-2-576">
            <label for="telefone_contabil">{{ $nome_campos['telefone_contabil'] }} - Telefone <span class="text-danger">*</span></label>
            <input type="text"
                class="{{ $classe }} form-control telefoneInput {{ $errors->has('telefone_contabil') ? 'is-invalid' : '' }} obrigatorio"
                name="telefone_contabil"
                id="telefone_contabil"
                value="{{ empty(old('telefone_contabil')) && isset($resultado->contabil->telefone) ? $resultado->contabil->telefone : old('telefone_contabil') }}"
                placeholder="(99) 99999-9999"
            />
            @if($errors->has('telefone_contabil'))
            <div class="invalid-feedback">
                {{ $errors->first('telefone_contabil') }}
            </div>
            @endif
        </div>
    </div>
</fieldset>
