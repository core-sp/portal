<h5 class="bold mb-2">Endereço de correspondência</h5>
    <div class="form-row mb-2">
        <div class="col-sm-4 mb-2-576">
            <label for="cep">CEP *</label>
            <input
                type="text"
                name="cep"
                class="form-control cep {{ $errors->has('cep') ? 'is-invalid' : '' }}"
                id="cep"
                placeholder="CEP"
                value="{{-- isset($resultado->cep) && explode(';', $resultado->cep)[0] ? explode(';', $resultado->cep)[0] : old('cep') --}}"
                required
            />
            @if($errors->has('cep'))
            <div class="invalid-feedback">
                {{ $errors->first('cep') }}
            </div>
            @endif
        </div>
        <div class="col-sm mb-2-576">
            <label for="bairro">Bairro *</label>
            <input
                type="text"
                name="bairro"
                class="form-control {{ $errors->has('bairro') ? 'is-invalid' : '' }}"
                id="bairro"
                placeholder="Bairro"
                value="{{ isset($resultado->bairro) && explode(';', $resultado->bairro)[0] ? explode(';', $resultado->bairro)[0] : old('bairro') }}"
                required
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
            <label for="rua">Logradouro *</label>
            <input
                type="text"
                name="logradouro"
                class="form-control {{ $errors->has('logradouro') ? 'is-invalid' : '' }}"
                id="rua"
                placeholder="Logradouro"
                value="{{ isset($resultado->logradouro) && explode(';', $resultado->logradouro)[0] ? explode(';', $resultado->logradouro)[0] : old('logradouro') }}"
                required
            />
            @if($errors->has('logradouro'))
            <div class="invalid-feedback">
                {{ $errors->first('logradouro') }}
            </div>
            @endif
        </div>
        <div class="col-sm-2 mb-2-576">
            <label for="numero">Número *</label>
            <input
                type="text"
                name="numero"
                class="form-control numero {{ $errors->has('numero') ? 'is-invalid' : '' }}"
                id="numero"
                placeholder="Número"
                value="{{ isset($resultado->numero) && explode(';', $resultado->numero)[0] ? explode(';', $resultado->numero)[0] : old('numero') }}"
                required
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
            <label for="complemento">Complemento</label>
            <input
                type="text"
                name="complemento"
                class="form-control {{ $errors->has('complemento') ? 'is-invalid' : '' }}"
                id="complemento"
                placeholder="Complemento"
                value="{{ isset($resultado->complemento) && explode(';', $resultado->complemento)[0] ? explode(';', $resultado->complemento)[0] : old('complemento') }}"
            />
            @if($errors->has('complemento'))
            <div class="invalid-feedback">
                {{ $errors->first('complemento') }}
            </div>
            @endif
        </div>
        <div class="col-sm-5 mb-2-576">
            <label for="municipio">Município *</label>
            <input
                type="text"
                name="municipio"
                id="municipio"
                class="form-control {{ $errors->has('municipio') ? 'is-invalid' : '' }}"
                placeholder="Município"
                value="{{ isset($resultado->municipio) && explode(';', $resultado->municipio)[0] ? explode(';', $resultado->municipio)[0] : old('municipio') }}"
                required
            />
            @if($errors->has('municipio'))
            <div class="invalid-feedback">
                {{ $errors->first('municipio') }}
            </div>
            @endif
        </div>
        <div class="col-sm-4 mb-2-576">
            <label for="estado">Estado *</label>
            <select 
                name="estado" 
                id="estado" 
                class="form-control {{ $errors->has('estado') ? 'is-invalid' : '' }}"
                required
            >
            @foreach(estados() as $key => $estado)
                @if(!empty(old('estado')))
                <option value="{{ $key }}" {{ old('estado') == $key ? 'selected' : '' }}>{{ $estado }}</option>
                @else
                    @if(isset($resultado->estado) && explode(';', $resultado->estado)[0])
                    <option value="{{ $key }}" {{ $key == explode(';', $resultado->estado)[0] ? 'selected' : '' }}>{{ $estado }}</option>
                    @else
                    <option value="{{ $key }}">{{ $estado }}</option>
                    @endif
                @endif
            @endforeach
            </select>
            @if($errors->has('estado'))
            <div class="invalid-feedback">
                {{ $errors->first('estado') }}
            </div>
            @endif
        </div>
    </div>

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
                <label for="cep_empresa">CEP *</label>
                <input
                    type="text"
                    name="cep_empresa"
                    class="form-control cep {{ $errors->has('cep_empresa') ? 'is-invalid' : '' }}"
                    id="cep_empresa"
                    placeholder="CEP"
                    value="{{-- isset($resultado->cep) && explode(';', $resultado->cep)[1] ? explode(';', $resultado->cep)[1] : old('cep_empresa') --}}"
                    required
                />
                @if($errors->has('cep_empresa'))
                <div class="invalid-feedback">
                    {{ $errors->first('cep_empresa') }}
                </div>
                @endif
            </div>
            <div class="col-sm mb-2-576">
                <label for="bairro_empresa">Bairro *</label>
                <input
                    type="text"
                    name="bairro_empresa"
                    class="form-control {{ $errors->has('bairro_empresa') ? 'is-invalid' : '' }}"
                    id="bairro_empresa"
                    placeholder="Bairro"
                    value="{{-- isset($resultado->bairro) && explode(';', $resultado->bairro)[1] ? explode(';', $resultado->bairro)[1] : old('bairro_empresa') --}}"
                    required
                >
                @if($errors->has('bairro_empresa'))
                <div class="invalid-feedback">
                    {{ $errors->first('bairro_empresa') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mb-2">
            <div class="col-sm mb-2-576">
                <label for="logradouro_empresa">Logradouro *</label>
                <input
                    type="text"
                    name="logradouro_empresa"
                    class="form-control {{ $errors->has('logradouro_empresa') ? 'is-invalid' : '' }}"
                    id="logradouro_empresa"
                    placeholder="Logradouro"
                    value="{{-- isset($resultado->logradouro) && explode(';', $resultado->logradouro)[1] ? explode(';', $resultado->logradouro)[1] : old('logradouro_empresa') --}}"
                    required
                />
                @if($errors->has('logradouro_empresa'))
                <div class="invalid-feedback">
                    {{ $errors->first('logradouro_empresa') }}
                </div>
                @endif
            </div>
            <div class="col-sm-2 mb-2-576">
                <label for="numero_empresa">Número *</label>
                <input
                    type="text"
                    name="numero_empresa"
                    class="form-control numero {{ $errors->has('numero_empresa') ? 'is-invalid' : '' }}"
                    id="numero_empresa"
                    placeholder="Número"
                    value="{{-- isset($resultado->numero) && explode(';', $resultado->numero)[1] ? explode(';', $resultado->numero)[1] : old('numero_empresa') --}}"
                    required
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
                <label for="complemento_empresa">Complemento</label>
                <input
                    type="text"
                    name="complemento_empresa"
                    class="form-control {{ $errors->has('complemento_empresa') ? 'is-invalid' : '' }}"
                    id="complemento_empresa"
                    placeholder="Complemento"
                    value="{{-- isset($resultado->complemento) && explode(';', $resultado->complemento)[1] ? explode(';', $resultado->complemento)[1] : old('complemento_empresa') --}}"
                />
                @if($errors->has('complemento_empresa'))
                <div class="invalid-feedback">
                    {{ $errors->first('complemento_empresa') }}
                </div>
                @endif
            </div>
            <div class="col-sm-5 mb-2-576">
                <label for="municipio_empresa">Município *</label>
                <input
                    type="text"
                    name="municipio_empresa"
                    id="municipio_empresa"
                    class="form-control {{ $errors->has('municipio_empresa') ? 'is-invalid' : '' }}"
                    placeholder="Município"
                    value="{{-- isset($resultado->municipio) && explode(';', $resultado->municipio)[1] ? explode(';', $resultado->municipio)[1] : old('municipio_empresa') --}}"
                    required
                />
                @if($errors->has('municipio_empresa'))
                <div class="invalid-feedback">
                    {{ $errors->first('municipio_empresa') }}
                </div>
                @endif
            </div>
            <div class="col-sm-4 mb-2-576">
                <label for="estado_empresa">Estado *</label>
                <select 
                    name="estado_empresa" 
                    id="estado_empresa" 
                    class="form-control {{ $errors->has('estado_empresa') ? 'is-invalid' : '' }}"
                    required
                >
                @foreach(estados() as $key => $estado)
                    @if(!empty(old('estado_empresa')))
                    <option value="{{ $key }}" {{ old('estado_empresa') == $key ? 'selected' : '' }}>{{ $estado }}</option>
                    @else
                        @if(isset($resultado->estado) && explode(';', $resultado->estado)[1])
                        <option value="{{ $key }}" {{ $key == explode(';', $resultado->estado)[1] ? 'selected' : '' }}>{{ $estado }}</option>
                        @else
                        <option value="{{ $key }}">{{ $estado }}</option>
                        @endif
                    @endif
                @endforeach
                </select>
                @if($errors->has('estado_empresa'))
                <div class="invalid-feedback">
                    {{ $errors->first('estado_empresa') }}
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif