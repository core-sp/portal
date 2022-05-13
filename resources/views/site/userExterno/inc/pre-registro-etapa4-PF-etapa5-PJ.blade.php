<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="tipo_telefone.1">{{ array_search('tipo_telefone', $codPre) }} - Tipo *</label><br>
        @foreach(tipos_contatos() as $tipo)
        <div class="form-check-inline">
            <label class="form-check-label">
                @if(!empty(old('tipo_telefone.1')))
                <input type="radio" class="preRegistro {{ array_search('tipo_telefone', $codPre) }} form-check-input" name="tipo_telefone.1" value="{{ $tipo }}" {{ old('tipo_telefone.1') == $tipo ? 'checked' : '' }} />{{ $tipo }}
                @elseif(isset(explode(',', $resultado->tipo_telefone)[0]))
                <input type="radio" class="preRegistro {{ array_search('tipo_telefone', $codPre) }} form-check-input" name="tipo_telefone.1" value="{{ $tipo }}" {{ $tipo == explode(',', $resultado->tipo_telefone)[0] ? 'checked' : '' }} />{{ $tipo }}
                @else
                <input type="radio" class="preRegistro {{ array_search('tipo_telefone', $codPre) }} form-check-input" name="tipo_telefone.1" value="{{ $tipo }}" />{{ $tipo }}
                @endif
            </label>
        </div>
        @endforeach
        @if($errors->has('tipo_telefone.1'))
        <div class="invalid-feedback">
            {{ $errors->first('tipo_telefone.1') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="telefone.1">{{ array_search('telefone', $codPre) }} - Telefone *</label>
        <input type="text"
            class="preRegistro {{ array_search('telefone', $codPre) }} form-control telefoneInput {{ $errors->has('telefone.1') ? 'is-invalid' : '' }}"
            name="telefone.1"
            value="{{ empty(old('telefone.1')) && isset(explode(',', $resultado->telefone)[0]) ? explode(',', $resultado->telefone)[0] : old('telefone.1') }}"
            placeholder="(99) 99999-9999"
        />
        @if($errors->has('telefone1'))
        <div class="invalid-feedback">
            {{ $errors->first('telefone1') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="tipo_telefone.2">{{ array_search('tipo_telefone', $codPre) }} - Tipo </label><br>
        @foreach(tipos_contatos() as $tipo)
        <div class="form-check-inline">
            <label class="form-check-label">
                @if(!empty(old('tipo_telefone.2')))
                <input type="radio" class="preRegistro {{ array_search('tipo_telefone', $codPre) }} form-check-input" name="tipo_telefone.2" value="{{ $tipo }}" {{ old('tipo_telefone.2') == $tipo ? 'checked' : '' }} />{{ $tipo }}
                @elseif(isset(explode(',', $resultado->tipo_telefone)[1]))
                <input type="radio" class="preRegistro {{ array_search('tipo_telefone', $codPre) }} form-check-input" name="tipo_telefone.2" value="{{ $tipo }}" {{ $tipo == explode(',', $resultado->tipo_telefone)[1] ? 'checked' : '' }} />{{ $tipo }}
                @else
                <input type="radio" class="preRegistro {{ array_search('tipo_telefone', $codPre) }} form-check-input" name="tipo_telefone.2" value="{{ $tipo }}" />{{ $tipo }}
                @endif
            </label>
        </div>
        @endforeach
        @if($errors->has('tipo_telefone.2'))
        <div class="invalid-feedback">
            {{ $errors->first('tipo_telefone.2') }}
        </div>
        @endif
    </div>
    <div class="col-sm-6 mb-2-576">
        <label for="telefone.2">{{ array_search('telefone', $codPre) }} - Telefone </label>
        <input type="text"
            class="preRegistro {{ array_search('telefone', $codPre) }} form-control celularInput {{ $errors->has('telefone.2') ? 'is-invalid' : '' }}"
            name="telefone.2"
            value="{{ empty(old('telefone.2')) && isset(explode(',', $resultado->telefone)[1]) ? explode(',', $resultado->telefone)[1] : old('telefone.2') }}"
            placeholder="(xx) 99999-9999"
        />
        @if($errors->has('telefone.2'))
        <div class="invalid-feedback">
            {{ $errors->first('telefone.2') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="email">{{ array_search('email', $codUser) }} - E-mail *</label>
        <input
            type="email"
            class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
            value="{{ $resultado->userExterno->email }}"
            readonly
            disabled
        />
        @if($errors->has('email'))
        <div class="invalid-feedback">
            {{ $errors->first('email') }}
        </div>
        @endif
    </div>
</div>