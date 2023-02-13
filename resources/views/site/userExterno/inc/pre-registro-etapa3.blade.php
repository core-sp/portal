@component('components.justificativa_pre_registro', [
    'resultado' => $resultado,
    'correcoes' => $resultado->getTextosJustificadosByAba($codigos[2])
])
@endcomponent

<h5 class="bold mb-2">Endereço de correspondência</h5>
<div class="form-row mb-2">
    <div class="col-sm-4 mb-2-576">
        <label for="cep_pre">{{ $codigos[2]['cep'] }} - CEP <span class="text-danger">*</span></label>
        <input
            type="text"
            name="cep"
            class="{{ $classes[4] }} form-control cep {{ $errors->has('cep') ? 'is-invalid' : '' }} obrigatorio"
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
        <label for="bairro_pre">{{ $codigos[2]['bairro'] }} - Bairro <span class="text-danger">*</span></label>
        <input
            type="text"
            name="bairro"
            class="{{ $classes[4] }} text-uppercase form-control {{ $errors->has('bairro') ? 'is-invalid' : '' }} obrigatorio"
            id="bairro_pre"
            value="{{ empty(old('bairro')) && isset($resultado->bairro) ? $resultado->bairro : old('bairro') }}"
            maxlength="191"
        />
        @if($errors->has('bairro'))
        <div class="invalid-feedback">
            {{ $errors->first('bairro') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-md col-lg mb-2-576">
        <label for="rua_pre">{{ $codigos[2]['logradouro'] }} - Logradouro <span class="text-danger">*</span></label>
        <input
            type="text"
            name="logradouro"
            class="{{ $classes[4] }} text-uppercase form-control {{ $errors->has('logradouro') ? 'is-invalid' : '' }} obrigatorio"
            id="rua_pre"
            value="{{ empty(old('logradouro')) && isset($resultado->logradouro) ? $resultado->logradouro : old('logradouro') }}"
            maxlength="191"
        />
        @if($errors->has('logradouro'))
        <div class="invalid-feedback">
            {{ $errors->first('logradouro') }}
        </div>
        @endif
    </div>
    <div class="col-md-3 col-lg-2 mb-2-576">
        <label for="numero_pre">{{ $codigos[2]['numero'] }} - Número <span class="text-danger">*</span></label>
        <input
            type="text"
            name="numero"
            class="{{ $classes[4] }} text-uppercase form-control {{ $errors->has('numero') ? 'is-invalid' : '' }} obrigatorio"
            id="numero_pre"
            value="{{ empty(old('numero')) && isset($resultado->numero) ? $resultado->numero : old('numero') }}"
            maxlength="10"
        />
        @if($errors->has('numero'))
        <div class="invalid-feedback">
            {{ $errors->first('numero') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-md-3 col-lg-3 col-xl-3 mb-2-576">
        <label for="complemento_pre">{{ $codigos[2]['complemento'] }} - Complemento</label>
        <input
            type="text"
            name="complemento"
            class="{{ $classes[4] }} text-uppercase form-control {{ $errors->has('complemento') ? 'is-invalid' : '' }}"
            id="complemento_pre"
            value="{{ empty(old('complemento')) && isset($resultado->complemento) ? $resultado->complemento : old('complemento') }}"
            maxlength="50"
        />
        @if($errors->has('complemento'))
        <div class="invalid-feedback">
            {{ $errors->first('complemento') }}
        </div>
        @endif
    </div>
    <div class="col-md col-lg-5 col-xl-5 mb-2-576">
        <label for="cidade_pre">{{ $codigos[2]['cidade'] }} - Município <span class="text-danger">*</span></label>
        <input
            type="text"
            name="cidade"
            id="cidade_pre"
            class="{{ $classes[4] }} text-uppercase form-control {{ $errors->has('cidade') ? 'is-invalid' : '' }} obrigatorio"
            value="{{ empty(old('cidade')) && isset($resultado->cidade) ? $resultado->cidade : old('cidade') }}"
            maxlength="191"
        />
        @if($errors->has('cidade'))
        <div class="invalid-feedback">
            {{ $errors->first('cidade') }}
        </div>
        @endif
    </div>
    <div class="col-lg-4 col-xl-4 mb-2-576">
        <label for="uf_pre">{{ $codigos[2]['uf'] }} - Estado <span class="text-danger">*</span></label>
        <select 
            name="uf" 
            id="uf_pre" 
            class="{{ $classes[4] }} form-control {{ $errors->has('uf') ? 'is-invalid' : '' }} obrigatorio"
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

@if(!$resultado->userExterno->isPessoaFisica())
<br>

<h5 class="bold mb-2">Endereço da empresa</h5>
<div class="form-row mb-2">
    <div class="form-check-inline">
        <label for="checkEndEmpresa" class="form-check-label">
            <input type="checkbox" 
                id="checkEndEmpresa" 
                class="{{ $classes[3] }} form-check-input {{ $errors->has('checkEndEmpresa') ? 'is-invalid' : '' }}" 
                name="checkEndEmpresa" 
                {{ $resultado->pessoaJuridica->mesmoEndereco() ? 'checked' : '' }}
            />
            <span class="bold">{{ $codigos[2]['checkEndEmpresa'] }} - Mesmo endereço da correspondência</span>
            
            @if($errors->has('checkEndEmpresa'))
            <div class="invalid-feedback">
                {{ $errors->first('checkEndEmpresa') }}
            </div>
            @endif
        </label>
        
    </div>
</div>

<fieldset id="habilitarEndEmpresa" {{ $resultado->pessoaJuridica->mesmoEndereco() ? 'disabled' : '' }}>
    <div class="form-row mb-2">
        <div class="col-sm-4 mb-2-576">
            <label for="cep_empresa">{{ $codigos[2]['cep_empresa'] }} - CEP <span class="text-danger">*</span></label>
            <input
                type="text"
                name="cep_empresa"
                class="{{ $classes[3] }} form-control cep {{ $errors->has('cep_empresa') ? 'is-invalid' : '' }} obrigatorio"
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
            <label for="bairro_empresa">{{ $codigos[2]['bairro_empresa'] }} - Bairro <span class="text-danger">*</span></label>
            <input
                type="text"
                name="bairro_empresa"
                class="{{ $classes[3] }} text-uppercase form-control {{ $errors->has('bairro_empresa') ? 'is-invalid' : '' }} obrigatorio"
                id="bairro_empresa"
                value="{{ empty(old('bairro_empresa')) && isset($resultado->pessoaJuridica->bairro) ? $resultado->pessoaJuridica->bairro : old('bairro_empresa') }}"
                maxlength="191"
            />
            @if($errors->has('bairro_empresa'))
            <div class="invalid-feedback">
                {{ $errors->first('bairro_empresa') }}
            </div>
            @endif
        </div>
    </div>

    <div class="form-row mb-2">
        <div class="col-md col-lg mb-2-576">
            <label for="rua_empresa">{{ $codigos[2]['logradouro_empresa'] }} - Logradouro <span class="text-danger">*</span></label>
            <input
                type="text"
                name="logradouro_empresa"
                class="{{ $classes[3] }} text-uppercase form-control {{ $errors->has('logradouro_empresa') ? 'is-invalid' : '' }} obrigatorio"
                id="rua_empresa"
                value="{{ empty(old('logradouro_empresa')) && isset($resultado->pessoaJuridica->logradouro) ? $resultado->pessoaJuridica->logradouro : old('logradouro_empresa') }}"
                maxlength="191"
            />
            @if($errors->has('logradouro_empresa'))
            <div class="invalid-feedback">
                {{ $errors->first('logradouro_empresa') }}
            </div>
            @endif
        </div>
        <div class="col-md-3 col-lg-2 mb-2-576">
            <label for="numero_empresa">{{ $codigos[2]['numero_empresa'] }} - Número <span class="text-danger">*</span></label>
            <input
                type="text"
                name="numero_empresa"
                class="{{ $classes[3] }} text-uppercase form-control {{ $errors->has('numero_empresa') ? 'is-invalid' : '' }} obrigatorio"
                id="numero_empresa"
                value="{{ empty(old('numero_empresa')) && isset($resultado->pessoaJuridica->numero) ? $resultado->pessoaJuridica->numero : old('numero_empresa') }}"
                maxlength="10"
            />
            @if($errors->has('numero_empresa'))
            <div class="invalid-feedback">
                {{ $errors->first('numero_empresa') }}
                </div>
            @endif
        </div>
    </div>

    <div class="form-row mb-2">
        <div class="col-md-3 col-lg-3 col-xl-3 mb-2-576">
            <label for="complemento_empresa">{{ $codigos[2]['complemento_empresa'] }} - Complemento</label>
            <input
                type="text"
                name="complemento_empresa"
                class="{{ $classes[3] }} text-uppercase form-control {{ $errors->has('complemento_empresa') ? 'is-invalid' : '' }}"
                id="complemento_empresa"
                value="{{ empty(old('complemento_empresa')) && isset($resultado->pessoaJuridica->complemento) ? $resultado->pessoaJuridica->complemento : old('complemento_empresa') }}"
                maxlength="50"
            />
            @if($errors->has('complemento_empresa'))
            <div class="invalid-feedback">
                {{ $errors->first('complemento_empresa') }}
            </div>
            @endif
        </div>
        <div class="col-md col-lg-5 col-xl-5 mb-2-576">
            <label for="cidade_empresa">{{ $codigos[2]['cidade_empresa'] }} - Município <span class="text-danger">*</span></label>
            <input
                type="text"
                name="cidade_empresa"
                id="cidade_empresa"
                class="{{ $classes[3] }} text-uppercase form-control {{ $errors->has('cidade_empresa') ? 'is-invalid' : '' }} obrigatorio"
                value="{{ empty(old('cidade_empresa')) && isset($resultado->pessoaJuridica->cidade) ? $resultado->pessoaJuridica->cidade : old('cidade_empresa') }}"
                maxlength="191"
            />
            @if($errors->has('cidade_empresa'))
            <div class="invalid-feedback">
                {{ $errors->first('cidade_empresa') }}
            </div>
            @endif
        </div>
        <div class="col-lg-4 col-xl-4 mb-2-576">
            <label for="uf_empresa">{{ $codigos[2]['uf_empresa'] }} - Estado <span class="text-danger">*</span></label>
            <select 
                name="uf_empresa" 
                id="uf_empresa" 
                class="{{ $classes[3] }} form-control {{ $errors->has('uf_empresa') ? 'is-invalid' : '' }} obrigatorio"
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
</fieldset>
@endif