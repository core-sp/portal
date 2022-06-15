<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="cnpj_contabil">{{ array_search('cnpj', $cod) }} - CNPJ</label>
        <input
            name="cnpj_contabil"
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

<fieldset id="campos_contabil" {{ isset($resultado->contabil->cnpj) ? '' : 'disabled' }}>
    <div class="form-row mb-2">
        <div class="col-sm mb-2-576">
            <label for="nome_contabil">{{ array_search('nome', $cod) }} - Nome da Contabilidade</label>
            <input
                name="nome_contabil"
                type="text"
                class="{{ $classes[1] }} form-control {{ $errors->has('nome_contabil') ? 'is-invalid' : '' }}"
                value="{{ empty(old('nome_contabil')) && isset($resultado->contabil->nome) ? $resultado->contabil->nome : old('nome_contabil') }}"
                minlength="5"
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
            <label for="email_contabil">{{ array_search('email', $cod) }} - E-mail</label>
            <input
                name="email_contabil"
                type="email"
                class="{{ $classes[1] }} form-control {{ $errors->has('email_contabil') ? 'is-invalid' : '' }}"
                value="{{ empty(old('email_contabil')) && isset($resultado->contabil->email) ? $resultado->contabil->email : old('email_contabil') }}"
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
            <label for="nome_contato_contabil">{{ array_search('nome_contato', $cod) }} - Nome de Contato</label>
            <input
                name="nome_contato_contabil"
                type="text"
                class="{{ $classes[1] }} form-control {{ $errors->has('nome_contato_contabil') ? 'is-invalid' : '' }}"
                value="{{ empty(old('nome_contato_contabil')) && isset($resultado->contabil->nome_contato) ? $resultado->contabil->nome_contato : old('nome_contato_contabil') }}"
            />
            @if($errors->has('nome_contato_contabil'))
            <div class="invalid-feedback">
                {{ $errors->first('nome_contato_contabil') }}
            </div>
            @endif
        </div>
        <div class="col-sm mb-2-576">
            <label for="telefone_contabil">{{ array_search('telefone', $cod) }} - Telefone</label>
            <input type="text"
                class="{{ $classes[1] }} form-control telefoneInput {{ $errors->has('telefone_contabil') ? 'is-invalid' : '' }}"
                name="telefone_contabil"
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
