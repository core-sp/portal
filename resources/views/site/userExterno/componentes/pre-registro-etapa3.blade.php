<h5 class="bold mb-2">Endereço de correspondência</h5>
<div class="form-row mb-2">
    <div class="col-sm-4 mb-2-576">
        <label for="cep">R30 - CEP *</label>
        <input
            type="text"
            name="cep"
            class="form-control cep {{ $errors->has('cep') ? 'is-invalid' : '' }}"
            id="cep"
            placeholder="CEP"
            value="{{-- isset($resultado->cep) && explode(';', $resultado->cep)[0] ? explode(';', $resultado->cep)[0] : old('cep') --}}"
        />
        @if($errors->has('cep'))
        <div class="invalid-feedback">
            {{ $errors->first('cep') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="bairro">R31 - Bairro *</label>
        <input
            type="text"
            name="bairro"
            class="form-control {{ $errors->has('bairro') ? 'is-invalid' : '' }}"
            id="bairro"
            placeholder="Bairro"
            value="{{-- isset($resultado->bairro) && explode(';', $resultado->bairro)[0] ? explode(';', $resultado->bairro)[0] : old('bairro') --}}"
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
        <label for="rua">R32 - Logradouro *</label>
        <input
            type="text"
            name="rua"
            class="form-control {{ $errors->has('rua') ? 'is-invalid' : '' }}"
            id="rua"
            placeholder="Logradouro"
            value="{{-- isset($resultado->logradouro) && explode(';', $resultado->logradouro)[0] ? explode(';', $resultado->logradouro)[0] : old('rua') --}}"
        />
        @if($errors->has('rua'))
        <div class="invalid-feedback">
            {{ $errors->first('rua') }}
        </div>
        @endif
    </div>
    <div class="col-sm-2 mb-2-576">
        <label for="numero">R33 - Número *</label>
        <input
            type="text"
            name="numero"
            class="form-control numero {{ $errors->has('numero') ? 'is-invalid' : '' }}"
            id="numero"
            placeholder="Número"
            value="{{-- isset($resultado->numero) && explode(';', $resultado->numero)[0] ? explode(';', $resultado->numero)[0] : old('numero') --}}"
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
        <label for="complemento">R34 - Complemento</label>
        <input
            type="text"
            name="complemento"
            class="form-control {{ $errors->has('complemento') ? 'is-invalid' : '' }}"
            id="complemento"
            placeholder="Complemento"
            value="{{-- isset($resultado->complemento) && explode(';', $resultado->complemento)[0] ? explode(';', $resultado->complemento)[0] : old('complemento') --}}"
        />
        @if($errors->has('complemento'))
        <div class="invalid-feedback">
            {{ $errors->first('complemento') }}
        </div>
        @endif
    </div>
    <div class="col-sm-5 mb-2-576">
        <label for="cidade">R35 - Município *</label>
        <input
            type="text"
            name="cidade"
            id="cidade"
            class="form-control {{ $errors->has('cidade') ? 'is-invalid' : '' }}"
            placeholder="Município"
            value="{{-- isset($resultado->municipio) && explode(';', $resultado->municipio)[0] ? explode(';', $resultado->municipio)[0] : old('cidade') --}}"
        />
        @if($errors->has('cidade'))
        <div class="invalid-feedback">
            {{ $errors->first('cidade') }}
        </div>
        @endif
    </div>
    <div class="col-sm-4 mb-2-576">
        <label for="uf">R36 - Estado *</label>
        <select 
            name="uf" 
            id="uf" 
            class="form-control {{ $errors->has('uf') ? 'is-invalid' : '' }}"
        >
        @foreach(estados() as $key => $estado)
            @if(!empty(old('uf')))
            <option value="{{ $key }}" {{ old('uf') == $key ? 'selected' : '' }}>{{ $estado }}</option>
            @elseif(isset($resultado->estado) && explode(';', $resultado->estado)[0])
            <option value="{{ $key }}" {{ $key == explode(';', $resultado->estado)[0] ? 'selected' : '' }}>{{ $estado }}</option>
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

@if(strlen($user->cpf_cnpj) == 14)
<h5 class="bold mb-2">Endereço da empresa</h5>
<div class="form-row mb-2">
    <div class="form-check-inline">
        <label class="form-check-label">
            <input type="checkbox" id="checkEndEmpresa" class="form-check-input" name="checkEndEmpresa" checked>Mesmo endereço da correspondência
        </label>
    </div>
</div>

<div id="habilitarEndEmpresa">
    <div class="form-row mb-2">
        <div class="col-sm-4 mb-2-576">
            <label for="cep_empresa">R37 - CEP *</label>
            <input
                type="text"
                name="cep_empresa"
                class="form-control cep {{ $errors->has('cep_empresa') ? 'is-invalid' : '' }}"
                id="cep"
                placeholder="CEP"
                value="{{-- isset($resultado->cep) && explode(';', $resultado->cep)[1] ? explode(';', $resultado->cep)[1] : old('cep_empresa') --}}"
            />
            @if($errors->has('cep_empresa'))
            <div class="invalid-feedback">
                {{ $errors->first('cep_empresa') }}
            </div>
            @endif
        </div>
        <div class="col-sm mb-2-576">
            <label for="bairro_empresa">R38 - Bairro *</label>
            <input
                type="text"
                name="bairro_empresa"
                class="form-control {{ $errors->has('bairro_empresa') ? 'is-invalid' : '' }}"
                id="bairro"
                placeholder="Bairro"
                value="{{-- isset($resultado->bairro) && explode(';', $resultado->bairro)[1] ? explode(';', $resultado->bairro)[1] : old('bairro_empresa') --}}"
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
            <label for="rua_empresa">R39 - Logradouro *</label>
            <input
                type="text"
                name="rua_empresa"
                class="form-control {{ $errors->has('rua_empresa') ? 'is-invalid' : '' }}"
                id="rua"
                placeholder="Logradouro"
                value="{{-- isset($resultado->logradouro) && explode(';', $resultado->logradouro)[1] ? explode(';', $resultado->logradouro)[1] : old('rua_empresa') --}}"
            />
            @if($errors->has('rua_empresa'))
            <div class="invalid-feedback">
                {{ $errors->first('rua_empresa') }}
            </div>
            @endif
        </div>
        <div class="col-sm-2 mb-2-576">
            <label for="numero_empresa">R40 - Número *</label>
            <input
                type="text"
                name="numero_empresa"
                class="form-control numero {{ $errors->has('numero_empresa') ? 'is-invalid' : '' }}"
                id="numero_empresa"
                placeholder="Número"
                value="{{-- isset($resultado->numero) && explode(';', $resultado->numero)[1] ? explode(';', $resultado->numero)[1] : old('numero_empresa') --}}"
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
            <label for="compl_empresa">R41 - Complemento</label>
            <input
                type="text"
                name="compl_empresa"
                class="form-control {{ $errors->has('compl_empresa') ? 'is-invalid' : '' }}"
                id="compl_empresa"
                placeholder="Complemento"
                value="{{-- isset($resultado->complemento) && explode(';', $resultado->complemento)[1] ? explode(';', $resultado->complemento)[1] : old('compl_empresa') --}}"
            />
            @if($errors->has('compl_empresa'))
            <div class="invalid-feedback">
                {{ $errors->first('compl_empresa') }}
            </div>
            @endif
        </div>
        <div class="col-sm-5 mb-2-576">
            <label for="cidade_empresa">R42 - Município *</label>
            <input
                type="text"
                name="cidade_empresa"
                id="cidade"
                class="form-control {{ $errors->has('cidade_empresa') ? 'is-invalid' : '' }}"
                placeholder="Município"
                value="{{-- isset($resultado->municipio) && explode(';', $resultado->municipio)[1] ? explode(';', $resultado->municipio)[1] : old('cidade_empresa') --}}"
            />
            @if($errors->has('cidade_empresa'))
            <div class="invalid-feedback">
                {{ $errors->first('cidade_empresa') }}
            </div>
            @endif
        </div>
        <div class="col-sm-4 mb-2-576">
            <label for="uf_empresa">R43 - Estado *</label>
            <select 
                name="uf_empresa" 
                id="uf" 
                class="form-control {{ $errors->has('uf_empresa') ? 'is-invalid' : '' }}"
            >
            @foreach(estados() as $key => $estado)
                @if(!empty(old('uf_empresa')))
                <option value="{{ $key }}" {{ old('uf_empresa') == $key ? 'selected' : '' }}>{{ $estado }}</option>
                @elseif(isset($resultado->estado) && explode(';', $resultado->estado)[1])
                <option value="{{ $key }}" {{ $key == explode(';', $resultado->estado)[1] ? 'selected' : '' }}>{{ $estado }}</option>
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