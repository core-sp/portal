@component('components.justificativa_pre_registro', [
    'resultado' => $resultado,
    'correcoes' => $resultado->getTextosJustificadosByAba($codigos[0])
])
@endcomponent

<small class="text-muted text-left">
    <em>
        <span class="font-weight-bolder">Obs:</span> Se registro realizado por Escritório de Contabilidade, inserir os dados solicitados abaixo, caso contrário, avançar esta etapa sem o preenchimento.
    </em>
</small>

<div class="form-row mb-3 mt-2">
    <div class="col-sm mb-2-576">
        <label for="cnpj_contabil">{{ $codigos[0]['cnpj_contabil'] }} - CNPJ</label> 
        <small class="text-muted text-left ml-2">
            <em>
                Após inserir um CNPJ válido aguarde 24h para trocar
            </em>
        </small>
        <input
            name="cnpj_contabil"
            id="cnpj_contabil"
            type="text"
            class="{{ $classes[1] }} form-control cnpjInput {{ $errors->has('cnpj_contabil') ? 'is-invalid' : '' }}"
            value="{{ empty(old('cnpj_contabil')) && isset($resultado->contabil->cnpj) ? $resultado->contabil->cnpj : old('cnpj_contabil') }}"
            placeholder="00.000.000/0000-00"
        />
        @if($errors->has('cnpj_contabil'))
        <div class="invalid-feedback">
            {{ $errors->first('cnpj_contabil') }}
        </div>
        @endif
    </div>
</div>

<div class="col p-0 mb-2 mt-2">
<small class="text-muted text-left">
    <em>
        <span class="text-danger font-weight-bolder">*</span> Campos obrigatórios caso preencha CNPJ da contabilidade
    </em>
</small>
</div>

<fieldset id="campos_contabil" {{ isset($resultado->contabil->cnpj) && !$resultado->contabil->possuiLogin() ? '' : 'disabled' }}>
    <div class="form-row mb-2">
        <div class="col-sm mb-2-576">
            <label for="nome_contabil">{{ $codigos[0]['nome_contabil'] }} - Nome da Contabilidade <span class="text-danger">*</span></label>
            <input
                name="nome_contabil"
                id="nome_contabil"
                type="text"
                class="{{ $classes[1] }} text-uppercase form-control {{ $errors->has('nome_contabil') ? 'is-invalid' : '' }} obrigatorio"
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
            <label for="email_contabil">{{ $codigos[0]['email_contabil'] }} - E-mail <span class="text-danger">*</span></label>
            <input
                name="email_contabil"
                id="email_contabil"
                type="email"
                class="{{ $classes[1] }} form-control {{ $errors->has('email_contabil') ? 'is-invalid' : '' }} obrigatorio"
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
            <label for="nome_contato_contabil">{{ $codigos[0]['nome_contato_contabil'] }} - Nome de Contato <span class="text-danger">*</span></label>
            <input
                name="nome_contato_contabil"
                id="nome_contato_contabil"
                type="text"
                class="{{ $classes[1] }} text-uppercase form-control {{ $errors->has('nome_contato_contabil') ? 'is-invalid' : '' }} obrigatorio"
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
            <label for="telefone_contabil">{{ $codigos[0]['telefone_contabil'] }} - Telefone <span class="text-danger">*</span></label>
            <input type="text"
                class="{{ $classes[1] }} form-control celularInput {{ $errors->has('telefone_contabil') ? 'is-invalid' : '' }} obrigatorio"
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
