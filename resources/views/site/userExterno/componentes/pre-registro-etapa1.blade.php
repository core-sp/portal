<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="nome_contabil">Nome da Contabilidade *</label>
        <input
            type="text"
            id="nome_contabil"
            class="form-control {{ $errors->has('nome_contabil') ? 'is-invalid' : '' }}"
            value="{{-- $user->nome --}}"
            placeholder="Nome da Contabilidade"
            minlength="5"
            maxlength="191"
            required
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
        <label for="cnpj_contabil">CNPJ *</label>
        <input
            type="text"
            class="form-control cnpjInput {{ $errors->has('cnpj_contabil') ? 'is-invalid' : '' }}"
            id="cnpj_contabil"
            value="{{-- $user->cpf_cnpj --}}"
            placeholder="CNPJ"
            required
        />
        @if($errors->has('cnpj_contabil'))
        <div class="invalid-feedback">
            {{ $errors->first('cnpj_contabil') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="email_contabil">E-mail *</label>
        <input
            type="email"
            class="form-control {{ $errors->has('email_contabil') ? 'is-invalid' : '' }}"
            value="{{-- $user->email --}}"
            required
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
        <label for="contato_contabil">Nome de Contato *</label>
        <input
            type="text"
            class="form-control {{ $errors->has('contato_contabil') ? 'is-invalid' : '' }}"
            id="contato_contabil"
            value="{{-- $user->cpf_cnpj --}}"
            placeholder="Nome de Contato"
            required
        />
        @if($errors->has('contato_contabil'))
        <div class="invalid-feedback">
            {{ $errors->first('contato_contabil') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="celular_contabil">Celular *</label>
        <input type="text"
            class="form-control celularInput {{ $errors->has('celular_contabil') ? 'is-invalid' : '' }}"
            name="celular"
            value="{{-- isset($resultado->celular) ? $resultado->celular : old('celular_contabil') --}}"
            placeholder="Celular"
        />
        @if($errors->has('celular_contabil'))
        <div class="invalid-feedback">
            {{ $errors->first('celular_contabil') }}
        </div>
        @endif
    </div>
</div>
