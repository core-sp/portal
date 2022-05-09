<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="nome_contabil">{{ array_search('nome', $cod) }} - Nome da Contabilidade</label>
        <input
            name="nome_contabil"
            type="text"
            class="form-control {{ $errors->has('nome_contabil') ? 'is-invalid' : '' }}"
            value="{{-- $user->nome --}}"
            placeholder="Nome da Contabilidade"
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
        <label for="cnpj_contabil">{{ array_search('cnpj', $cod) }} - CNPJ</label>
        <input
            name="cnpj_contabil"
            type="text"
            class="form-control cnpjInput {{ $errors->has('cnpj_contabil') ? 'is-invalid' : '' }}"
            value="{{-- $user->cpf_cnpj --}}"
            placeholder="CNPJ"
        />
        @if($errors->has('cnpj_contabil'))
        <div class="invalid-feedback">
            {{ $errors->first('cnpj_contabil') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="email_contabil">{{ array_search('email', $cod) }} - E-mail</label>
        <input
            name="email_contabil"
            type="email"
            class="form-control {{ $errors->has('email_contabil') ? 'is-invalid' : '' }}"
            value="{{-- $user->email --}}"
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
        <label for="contato_contabil">{{ array_search('nome_contato', $cod) }} - Nome de Contato</label>
        <input
            name="contato_contabil"
            type="text"
            class="form-control {{ $errors->has('contato_contabil') ? 'is-invalid' : '' }}"
            value="{{-- $user->cpf_cnpj --}}"
            placeholder="Nome de Contato"
        />
        @if($errors->has('contato_contabil'))
        <div class="invalid-feedback">
            {{ $errors->first('contato_contabil') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="celular_contabil">{{ array_search('telefone', $cod) }} - Celular</label>
        <input type="text"
            class="form-control celularInput {{ $errors->has('celular_contabil') ? 'is-invalid' : '' }}"
            name="celular_contabil"
            value="{{-- isset($resultado->celular) ? $resultado->celular : old('celular_contabil') --}}"
            placeholder="(xx) 99999-9999"
        />
        @if($errors->has('celular_contabil'))
        <div class="invalid-feedback">
            {{ $errors->first('celular_contabil') }}
        </div>
        @endif
    </div>
</div>
