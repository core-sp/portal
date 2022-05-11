<h5 class="bold mb-2">Endereço de correspondência</h5>
<div class="form-row mb-2">
    <div class="col-sm-4 mb-2-576">
        <label for="cep">{{ array_search('cep', $codPre) }} - CEP *</label>
        <input
            type="text"
            name="cep"
            class="PreRegistro form-control cep {{ $errors->has('cep') ? 'is-invalid' : '' }}"
            id="cep"
            placeholder="CEP"
            value="{{ empty(old('cep')) && isset($resultado->cep) ? $resultado->cep : old('cep') }}"
        />
        @if($errors->has('cep'))
        <div class="invalid-feedback">
            {{ $errors->first('cep') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="bairro">{{ array_search('bairro', $codPre) }} - Bairro *</label>
        <input
            type="text"
            name="bairro"
            class="PreRegistro form-control {{ $errors->has('bairro') ? 'is-invalid' : '' }}"
            id="bairro"
            placeholder="Bairro"
            value="{{ empty(old('bairro')) && isset($resultado->bairro) ? $resultado->bairro : old('bairro') }}"
        />
        @if($errors->has('bairro'))
        <div class="invalid-feedback">
            {{ $errors->first('bairro') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="logradouro">{{ array_search('logradouro', $codPre) }} - Logradouro *</label>
        <input
            type="text"
            name="logradouro"
            class="PreRegistro form-control {{ $errors->has('logradouro') ? 'is-invalid' : '' }}"
            id="rua"
            placeholder="Logradouro"
            value="{{ empty(old('logradouro')) && isset($resultado->logradouro) ? $resultado->logradouro : old('logradouro') }}"
        />
        @if($errors->has('logradouro'))
        <div class="invalid-feedback">
            {{ $errors->first('logradouro') }}
        </div>
        @endif
    </div>
    <div class="col-sm-2 mb-2-576">
        <label for="numero">{{ array_search('numero', $codPre) }} - Número *</label>
        <input
            type="text"
            name="numero"
            class="PreRegistro form-control numero {{ $errors->has('numero') ? 'is-invalid' : '' }}"
            id="numero"
            placeholder="Número"
            value="{{ empty(old('numero')) && isset($resultado->numero) ? $resultado->numero : old('numero') }}"
        />
        @if($errors->has('numero'))
        <div class="invalid-feedback">
            {{ $errors->first('numero') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm-3 mb-2-576">
        <label for="complemento">{{ array_search('complemento', $codPre) }} - Complemento</label>
        <input
            type="text"
            name="complemento"
            class="PreRegistro form-control {{ $errors->has('complemento') ? 'is-invalid' : '' }}"
            id="complemento"
            placeholder="Complemento"
            value="{{ empty(old('complemento')) && isset($resultado->complemento) ? $resultado->complemento : old('complemento') }}"
        />
        @if($errors->has('complemento'))
        <div class="invalid-feedback">
            {{ $errors->first('complemento') }}
        </div>
        @endif
    </div>
    <div class="col-sm-5 mb-2-576">
        <label for="cidade">{{ array_search('cidade', $codPre) }} - Município *</label>
        <input
            type="text"
            name="cidade"
            id="cidade"
            class="PreRegistro form-control {{ $errors->has('cidade') ? 'is-invalid' : '' }}"
            placeholder="Município"
            value="{{ empty(old('cidade')) && isset($resultado->cidade) ? $resultado->cidade : old('cidade') }}"
        />
        @if($errors->has('cidade'))
        <div class="invalid-feedback">
            {{ $errors->first('cidade') }}
        </div>
        @endif
    </div>
    <div class="col-sm-4 mb-2-576">
        <label for="uf">{{ array_search('uf', $codPre) }} - Estado *</label>
        <select 
            name="uf" 
            id="uf" 
            class="PreRegistro form-control {{ $errors->has('uf') ? 'is-invalid' : '' }}"
        >
            <option value="">Selecione a opção...</option>
        @foreach(estados() as $key => $estado)
            @if(!empty(old('uf')))
            <option value="{{ $key }}" {{ old('uf') == $key ? 'selected' : '' }}>{{ $estado }}</option>
            @elseif(isset($resultado->uf))
            <option value="{{ $key }}" {{ $key == $resultado->uf ? 'selected' : '' }}>{{ $estado }}</option>
            @else
            <option value="{{ $key }}">{{ $estado }}</option>
            @endif
        @endforeach
        </select>
        @if($errors->has('uf'))
        <div class="invalid-feedback">
            {{ $errors->first('uf') }}
        </div>
        @endif
    </div>
</div>

<br>

@if(strlen($resultado->userExterno->cpf_cnpj) == 14)
<h5 class="bold mb-2">Endereço da empresa</h5>
<div class="form-row mb-2">
    <div class="form-check-inline">
        <label class="form-check-label">
            <input type="checkbox" 
                id="checkEndEmpresa" 
                class="PreRegistroCnpj form-check-input" 
                name="checkEndEmpresa" 
                checked
            />
            Mesmo endereço da correspondência
        </label>
    </div>
</div>

<div id="habilitarEndEmpresa">
    <div class="form-row mb-2">
        <div class="col-sm-4 mb-2-576">
            <label for="cep_empresa">{{ array_search('cep', $codCnpj) }} - CEP *</label>
            <input
                type="text"
                name="cep_empresa"
                class="PreRegistroCnpj form-control cep {{ $errors->has('cep_empresa') ? 'is-invalid' : '' }}"
                id="cep"
                placeholder="CEP"
                value="{{ empty(old('cep_empresa')) && isset($resultado->pessoaJuridica->cep) ? $resultado->pessoaJuridica->cep : old('cep_empresa') }}"
            />
            @if($errors->has('cep_empresa'))
            <div class="invalid-feedback">
                {{ $errors->first('cep_empresa') }}
            </div>
            @endif
        </div>
        <div class="col-sm mb-2-576">
            <label for="bairro_empresa">{{ array_search('bairro', $codCnpj) }} - Bairro *</label>
            <input
                type="text"
                name="bairro_empresa"
                class="PreRegistroCnpj form-control {{ $errors->has('bairro_empresa') ? 'is-invalid' : '' }}"
                id="bairro"
                placeholder="Bairro"
                value="{{ empty(old('bairro_empresa')) && isset($resultado->pessoaJuridica->bairro) ? $resultado->pessoaJuridica->bairro : old('bairro_empresa') }}"
            />
            @if($errors->has('bairro_empresa'))
            <div class="invalid-feedback">
                {{ $errors->first('bairro_empresa') }}
            </div>
            @endif
        </div>
    </div>

    <div class="form-row mb-2">
        <div class="col-sm mb-2-576">
            <label for="logradouro_empresa">{{ array_search('logradouro', $codCnpj) }} - Logradouro *</label>
            <input
                type="text"
                name="logradouro_empresa"
                class="PreRegistroCnpj form-control {{ $errors->has('logradouro_empresa') ? 'is-invalid' : '' }}"
                id="rua"
                placeholder="Logradouro"
                value="{{ empty(old('logradouro_empresa')) && isset($resultado->pessoaJuridica->logradouro) ? $resultado->pessoaJuridica->logradouro : old('logradouro_empresa') }}"
            />
            @if($errors->has('logradouro_empresa'))
            <div class="invalid-feedback">
                {{ $errors->first('logradouro_empresa') }}
            </div>
            @endif
        </div>
        <div class="col-sm-2 mb-2-576">
            <label for="numero_empresa">{{ array_search('numero', $codCnpj) }} - Número *</label>
            <input
                type="text"
                name="numero_empresa"
                class="PreRegistroCnpj form-control numero {{ $errors->has('numero_empresa') ? 'is-invalid' : '' }}"
                id="numero_empresa"
                placeholder="Número"
                value="{{ empty(old('numero_empresa')) && isset($resultado->pessoaJuridica->numero) ? $resultado->pessoaJuridica->numero : old('numero_empresa') }}"
            />
            @if($errors->has('numero_empresa'))
            <div class="invalid-feedback">
                {{ $errors->first('numero_empresa') }}
                </div>
            @endif
        </div>
    </div>

    <div class="form-row mb-2">
        <div class="col-sm-3 mb-2-576">
            <label for="complemento_empresa">{{ array_search('complemento', $codCnpj) }} - Complemento</label>
            <input
                type="text"
                name="complemento_empresa"
                class="PreRegistroCnpj form-control {{ $errors->has('complemento_empresa') ? 'is-invalid' : '' }}"
                id="complemento_empresa"
                placeholder="Complemento"
                value="{{ empty(old('numero_empresa')) && isset($resultado->pessoaJuridica->numero) ? $resultado->pessoaJuridica->numero : old('numero_empresa') }}"
            />
            @if($errors->has('complemento_empresa'))
            <div class="invalid-feedback">
                {{ $errors->first('complemento_empresa') }}
            </div>
            @endif
        </div>
        <div class="col-sm-5 mb-2-576">
            <label for="cidade_empresa">{{ array_search('cidade', $codCnpj) }} - Município *</label>
            <input
                type="text"
                name="cidade_empresa"
                id="cidade"
                class="PreRegistroCnpj form-control {{ $errors->has('cidade_empresa') ? 'is-invalid' : '' }}"
                placeholder="Município"
                value="{{ empty(old('cidade_empresa')) && isset($resultado->pessoaJuridica->cidade) ? $resultado->pessoaJuridica->cidade : old('cidade_empresa') }}"
            />
            @if($errors->has('cidade_empresa'))
            <div class="invalid-feedback">
                {{ $errors->first('cidade_empresa') }}
            </div>
            @endif
        </div>
        <div class="col-sm-4 mb-2-576">
            <label for="uf_empresa">{{ array_search('uf', $codCnpj) }} - Estado *</label>
            <select 
                name="uf_empresa" 
                id="uf" 
                class="PreRegistroCnpj form-control {{ $errors->has('uf_empresa') ? 'is-invalid' : '' }}"
            >
                <option value="">Selecione a opção...</option>
            @foreach(estados() as $key => $estado)
                @if(!empty(old('uf_empresa')))
                <option value="{{ $key }}" {{ old('uf_empresa') == $key ? 'selected' : '' }}>{{ $estado }}</option>
                @elseif(isset($resultado->pessoaJuridica->uf))
                <option value="{{ $key }}" {{ $key == $resultado->pessoaJuridica->uf ? 'selected' : '' }}>{{ $estado }}</option>
                @else
                <option value="{{ $key }}">{{ $estado }}</option>
                @endif
            @endforeach
            </select>
            @if($errors->has('uf_empresa'))
            <div class="invalid-feedback">
                {{ $errors->first('uf_empresa') }}
            </div>
            @endif
        </div>
    </div>
</div>
@endif