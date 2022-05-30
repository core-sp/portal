<h5 class="bold mb-2">Endereço de correspondência</h5>
<div class="form-row mb-2">
    <div class="col-sm-4 mb-2-576">
        <label for="cep">{{ array_search('cep', $codPre) }} - CEP *</label>
        <input
            type="text"
            name="cep"
            class="{{ $classes[4] }} {{ array_search('cep', $codPre) }} form-control cep {{ $errors->has('cep') ? 'is-invalid' : '' }}"
            id="cep_pre"
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
            class="{{ $classes[4] }} {{ array_search('bairro', $codPre) }} form-control {{ $errors->has('bairro') ? 'is-invalid' : '' }}"
            id="bairro_pre"
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
            class="{{ $classes[4] }} {{ array_search('logradouro', $codPre) }} form-control {{ $errors->has('logradouro') ? 'is-invalid' : '' }}"
            id="rua_pre"
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
            class="{{ $classes[4] }} {{ array_search('numero', $codPre) }} form-control {{ $errors->has('numero') ? 'is-invalid' : '' }}"
            id="numero_pre"
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
            class="{{ $classes[4] }} {{ array_search('complemento', $codPre) }} form-control {{ $errors->has('complemento') ? 'is-invalid' : '' }}"
            id="complemento_pre"
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
            id="cidade_pre"
            class="{{ $classes[4] }} {{ array_search('cidade', $codPre) }} form-control {{ $errors->has('cidade') ? 'is-invalid' : '' }}"
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
            id="uf_pre" 
            class="{{ $classes[4] }} {{ array_search('uf', $codPre) }} form-control {{ $errors->has('uf') ? 'is-invalid' : '' }}"
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

@if(strlen($resultado->userExterno->cpf_cnpj) == 14)
<br>

<h5 class="bold mb-2">Endereço da empresa</h5>
<div class="form-row mb-2">
    <div class="form-check-inline">
        <label class="form-check-label">
            <input type="checkbox" 
                id="checkEndEmpresa" 
                class="{{ $classes[3] }} {{ array_search('bairro', $codCnpj).'-'.array_search('uf', $codCnpj) }} form-check-input {{ $errors->has('checkEndEmpresa') ? 'is-invalid' : '' }}" 
                name="checkEndEmpresa" 
                @if(isset($resultado->pessoaJuridica) && $resultado->pessoaJuridica->mesmoEndereco())
                    checked
                @endif
            />
            Mesmo endereço da correspondência
            
            @if($errors->has('checkEndEmpresa'))
            <div class="invalid-feedback">
                {{ $errors->first('checkEndEmpresa') }}
            </div>
            @endif
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
                class="{{ $classes[3] }} {{ array_search('cep', $codCnpj) }} form-control cep {{ $errors->has('cep_empresa') ? 'is-invalid' : '' }}"
                id="cep_empresa"
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
                class="{{ $classes[3] }} {{ array_search('bairro', $codCnpj) }} form-control {{ $errors->has('bairro_empresa') ? 'is-invalid' : '' }}"
                id="bairro_empresa"
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
                class="{{ $classes[3] }} {{ array_search('logradouro', $codCnpj) }} form-control {{ $errors->has('logradouro_empresa') ? 'is-invalid' : '' }}"
                id="rua_empresa"
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
                class="{{ $classes[3] }} {{ array_search('numero', $codCnpj) }} form-control {{ $errors->has('numero_empresa') ? 'is-invalid' : '' }}"
                id="numero_empresa"
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
                class="{{ $classes[3] }} {{ array_search('complemento', $codCnpj) }} form-control {{ $errors->has('complemento_empresa') ? 'is-invalid' : '' }}"
                id="complemento_empresa"
                value="{{ empty(old('complemento_empresa')) && isset($resultado->pessoaJuridica->complemento) ? $resultado->pessoaJuridica->complemento : old('complemento_empresa') }}"
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
                id="cidade_empresa"
                class="{{ $classes[3] }} {{ array_search('cidade', $codCnpj) }} form-control {{ $errors->has('cidade_empresa') ? 'is-invalid' : '' }}"
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
                id="uf_empresa" 
                class="{{ $classes[3] }} {{ array_search('uf', $codCnpj) }} form-control {{ $errors->has('uf_empresa') ? 'is-invalid' : '' }}"
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