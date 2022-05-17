<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="tipo_telefone">{{ array_search('tipo_telefone', $codPre) }} - Tipo *</label><br>
        @foreach(tipos_contatos() as $tipo)
        <div class="form-check-inline">
            <label class="form-check-label">
                @if(!empty(old('tipo_telefone')))
                <input type="radio" class="{{ $classes[4] }} {{ array_search('tipo_telefone', $codPre) }} form-check-input" name="tipo_telefone" value="{{ $tipo }}" {{ old('tipo_telefone') == $tipo ? 'checked' : '' }} />{{ $tipo }}
                @elseif(isset(explode(',', $resultado->tipo_telefone)[0]))
                <input type="radio" class="{{ $classes[4] }} {{ array_search('tipo_telefone', $codPre) }} form-check-input" name="tipo_telefone" value="{{ $tipo }}" {{ $tipo == explode(';', $resultado->tipo_telefone)[0] ? 'checked' : '' }} />{{ $tipo }}
                @else
                <input type="radio" class="{{ $classes[4] }} {{ array_search('tipo_telefone', $codPre) }} form-check-input" name="tipo_telefone" value="{{ $tipo }}" />{{ $tipo }}
                @endif
            </label>
        </div>
        @endforeach
        @if($errors->has('tipo_telefone'))
        <div class="invalid-feedback">
            {{ $errors->first('tipo_telefone') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="telefone">{{ array_search('telefone', $codPre) }} - Telefone *</label>
        <input type="text"
            class="{{ $classes[4] }} {{ array_search('telefone', $codPre) }} form-control telefoneInput {{ $errors->has('telefone') ? 'is-invalid' : '' }}"
            name="telefone"
            value="{{ empty(old('telefone')) && isset(explode(';', $resultado->telefone)[0]) ? explode(';', $resultado->telefone)[0] : old('telefone') }}"
            placeholder="(99) 99999-9999"
        />
        @if($errors->has('telefone'))
        <div class="invalid-feedback">
            {{ $errors->first('telefone') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="tipo_telefone_1">{{ array_search('tipo_telefone', $codPre) }} - Tipo </label><br>
        @foreach(tipos_contatos() as $tipo)
        <div class="form-check-inline">
            <label class="form-check-label">
                @if(!empty(old('tipo_telefone_1')))
                <input type="radio" class="{{ $classes[4] }} {{ array_search('tipo_telefone', $codPre) }} form-check-input" name="tipo_telefone_1" value="{{ $tipo }}" {{ old('tipo_telefone_1') == $tipo ? 'checked' : '' }} />{{ $tipo }}
                @elseif(isset(explode(';', $resultado->tipo_telefone)[1]))
                <input type="radio" class="{{ $classes[4] }} {{ array_search('tipo_telefone', $codPre) }} form-check-input" name="tipo_telefone_1" value="{{ $tipo }}" {{ $tipo == explode(';', $resultado->tipo_telefone)[1] ? 'checked' : '' }} />{{ $tipo }}
                @else
                <input type="radio" class="{{ $classes[4] }} {{ array_search('tipo_telefone', $codPre) }} form-check-input" name="tipo_telefone_1" value="{{ $tipo }}" />{{ $tipo }}
                @endif
            </label>
        </div>
        @endforeach
        @if($errors->has('tipo_telefone_1'))
        <div class="invalid-feedback">
            {{ $errors->first('tipo_telefone_1') }}
        </div>
        @endif
    </div>
    <div class="col-sm-6 mb-2-576">
        <label for="telefone_1">{{ array_search('telefone', $codPre) }} - Telefone </label>
        <input type="text"
            class="{{ $classes[4] }} {{ array_search('telefone', $codPre) }} form-control celularInput {{ $errors->has('telefone_1') ? 'is-invalid' : '' }}"
            name="telefone_1"
            value="{{ empty(old('telefone_1')) && isset(explode(';', $resultado->telefone)[1]) ? explode(';', $resultado->telefone)[1] : old('telefone_1') }}"
            placeholder="(xx) 99999-9999"
        />
        @if($errors->has('telefone_1'))
        <div class="invalid-feedback">
            {{ $errors->first('telefone_1') }}
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