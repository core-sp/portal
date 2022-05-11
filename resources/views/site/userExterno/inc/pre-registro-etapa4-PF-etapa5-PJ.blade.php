<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="tipo_telefone1">{{ array_search('tipo_telefone', $codPre) }} - Tipo *</label><br>
        @foreach(tipos_contatos() as $tipo)
        <div class="form-check-inline">
            <label class="form-check-label">
                @if(!empty(old('tipo_telefone1')))
                <input type="radio" class="PreRegistro form-check-input" name="tipo_telefone1" value="{{ $tipo }}" {{ old('tipo_telefone1') == $tipo ? 'checked' : '' }} />{{ $tipo }}
                @elseif(isset(explode(',', $resultado->tipo_telefone)[0]))
                <input type="radio" class="PreRegistro form-check-input" name="tipo_telefone1" value="{{ $tipo }}" {{ $tipo == explode(',', $resultado->tipo_telefone)[0] ? 'checked' : '' }} />{{ $tipo }}
                @else
                <input type="radio" class="PreRegistro form-check-input" name="tipo_telefone1" value="{{ $tipo }}" />{{ $tipo }}
                @endif
            </label>
        </div>
        @endforeach
        @if($errors->has('tipo_telefone1'))
        <div class="invalid-feedback">
            {{ $errors->first('tipo_telefone1') }}
        </div>
        @endif
    </div>
    <div class="col-sm-6 mb-2-576">
        <label for="telefone1">{{ array_search('telefone', $codPre) }} - Telefone *</label>
        <input type="text"
            class="PreRegistro form-control telefoneInput {{ $errors->has('telefone1') ? 'is-invalid' : '' }}"
            name="telefone1"
            value="{{ empty(old('telefone1')) && isset(explode(',', $resultado->telefone)[0]) ? explode(',', $resultado->telefone)[0] : old('telefone1') }}"
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
        <label for="tipo_telefone2">{{ array_search('tipo_telefone', $codPre) }} - Tipo </label><br>
        @foreach(tipos_contatos() as $tipo)
        <div class="form-check-inline">
            <label class="form-check-label">
                @if(!empty(old('tipo_telefone2')))
                <input type="radio" class="PreRegistro form-check-input" name="tipo_telefone2" value="{{ $tipo }}" {{ old('tipo_telefone2') == $tipo ? 'checked' : '' }} />{{ $tipo }}
                @elseif(isset(explode(',', $resultado->tipo_telefone)[1]))
                <input type="radio" class="PreRegistro form-check-input" name="tipo_telefone2" value="{{ $tipo }}" {{ $tipo == explode(',', $resultado->tipo_telefone)[1] ? 'checked' : '' }} />{{ $tipo }}
                @else
                <input type="radio" class="PreRegistro form-check-input" name="tipo_telefone2" value="{{ $tipo }}" />{{ $tipo }}
                @endif
            </label>
        </div>
        @endforeach
        @if($errors->has('tipo_telefone2'))
        <div class="invalid-feedback">
            {{ $errors->first('tipo_telefone2') }}
        </div>
        @endif
    </div>
    <div class="col-sm-6 mb-2-576">
        <label for="telefone2">{{ array_search('telefone', $codPre) }} - Telefone </label>
        <input type="text"
            class="PreRegistro form-control celularInput {{ $errors->has('telefone2') ? 'is-invalid' : '' }}"
            name="telefone2"
            value="{{ empty(old('telefone2')) && isset(explode(',', $resultado->telefone)[1]) ? explode(',', $resultado->telefone)[1] : old('telefone2') }}"
            placeholder="(xx) 99999-9999"
        />
        @if($errors->has('telefone2'))
        <div class="invalid-feedback">
            {{ $errors->first('telefone2') }}
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