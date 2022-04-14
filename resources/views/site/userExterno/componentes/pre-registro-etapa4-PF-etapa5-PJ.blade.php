<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="tipo_rel">R60 - Tipo *</label><br>
        @foreach(tipos_contatos() as $tipo)
        <div class="form-check-inline">
            <label class="form-check-label">
                @if(!empty(old('tipo_rel')))
                <input type="radio" class="form-check-input" name="tipo_rel[]" value="{{ $tipo }}" {{ old('tipo_rel') == $tipo ? 'checked' : '' }} />{{ $tipo }}
                @elseif(isset($resultado->tipo_empresa))
                <input type="radio" class="form-check-input" name="tipo_rel[]" value="{{ $tipo }}" {{ $tipo == $resultado->tipo ? 'checked' : '' }} />{{ $tipo }}
                @else
                <input type="radio" class="form-check-input" name="tipo_rel[]" value="{{ $tipo }}" />{{ $tipo }}
                @endif
            </label>
        </div>
        @endforeach
        @if($errors->has('tipo_rel'))
        <div class="invalid-feedback">
            {{ $errors->first('tipo_rel') }}
        </div>
        @endif
    </div>
    <div class="col-sm-6 mb-2-576">
        <label for="celular_rel">R61 - Telefone *</label>
        <input type="text"
            class="form-control celularInput {{ $errors->has('celular_rel') ? 'is-invalid' : '' }}"
            name="celular_rel[]"
            value="{{-- isset($resultado->celular) ? $resultado->celular : old('celular_rel') --}}"
            placeholder="(xx) 99999-9999"
        />
        @if($errors->has('celular_rel'))
        <div class="invalid-feedback">
            {{ $errors->first('celular_rel') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="tipo_rel">R62 - Tipo </label><br>
        @foreach(tipos_contatos() as $tipo)
        <div class="form-check-inline">
            <label class="form-check-label">
                @if(!empty(old('tipo_rel')))
                <input type="radio" class="form-check-input" name="tipo_rel[]" value="{{ $tipo }}" {{ old('tipo_rel') == $tipo ? 'checked' : '' }} />{{ $tipo }}
                @elseif(isset($resultado->tipo_empresa))
                <input type="radio" class="form-check-input" name="tipo_rel[]" value="{{ $tipo }}" {{ $tipo == $resultado->tipo ? 'checked' : '' }} />{{ $tipo }}
                @else
                <input type="radio" class="form-check-input" name="tipo_rel[]" value="{{ $tipo }}" />{{ $tipo }}
                @endif
            </label>
        </div>
        @endforeach
        @if($errors->has('tipo_rel'))
        <div class="invalid-feedback">
            {{ $errors->first('tipo_rel') }}
        </div>
        @endif
    </div>
    <div class="col-sm-6 mb-2-576">
        <label for="celular_rel">R63 - Telefone </label>
        <input type="text"
            class="form-control celularInput {{ $errors->has('celular_rel') ? 'is-invalid' : '' }}"
            name="celular_rel[]"
            value="{{-- isset($resultado->celular) ? $resultado->celular : old('celular_rel') --}}"
            placeholder="(xx) 99999-9999"
        />
        @if($errors->has('celular_rel'))
        <div class="invalid-feedback">
            {{ $errors->first('celular_rel') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="tipo_email_rel">R64 - Tipo *</label><br>
        @foreach(tipos_contatos() as $tipo)
        <div class="form-check-inline">
            <label class="form-check-label">
                @if(!empty(old('tipo_email_rel')))
                <input type="radio" class="form-check-input" name="tipo_email_rel" value="{{ $tipo }}" {{ old('tipo_email_rel') == $tipo ? 'checked' : '' }} />{{ $tipo }}
                @elseif(isset($resultado->tipo_empresa))
                <input type="radio" class="form-check-input" name="tipo_email_rel" value="{{ $tipo }}" {{ $tipo == $resultado->tipo ? 'checked' : '' }} />{{ $tipo }}
                @else
                <input type="radio" class="form-check-input" name="tipo_email_rel" value="{{ $tipo }}" />{{ $tipo }}
                @endif
            </label>
        </div>
        @endforeach
        @if($errors->has('tipo_email_rel'))
        <div class="invalid-feedback">
            {{ $errors->first('tipo_email_rel') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="email_rel">R65 - E-mail *</label>
        <input
            name="email_rel"
            type="email"
            class="form-control {{ $errors->has('email_rel') ? 'is-invalid' : '' }}"
            value="{{-- $user->email --}}"
        />
        @if($errors->has('email_rel'))
        <div class="invalid-feedback">
            {{ $errors->first('email_rel') }}
        </div>
        @endif
    </div>
</div>