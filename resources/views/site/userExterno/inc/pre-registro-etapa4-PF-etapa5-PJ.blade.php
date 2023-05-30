@component('components.justificativa_pre_registro', [
    'resultado' => $resultado,
    'correcoes' => $resultado->getTextosJustificadosByAba($codigos[4])
])
@endcomponent

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="email_pre">E-mail <span class="text-danger">*</span></label>
        <input
            type="email"
            id="email_pre"
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

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="tipo_telefone">{{ $codigos[4]['tipo_telefone'] }} - Tipo de telefone <span class="text-danger">*</span></label><br>
        <select 
            name="tipo_telefone" 
            id="tipo_telefone"
            class="{{ $classes[4] }} form-control {{ $errors->has('tipo_telefone') ? 'is-invalid' : '' }} obrigatorio"
        >
            <option value="">Selecione a opção...</option>
        @foreach(tipos_contatos() as $tipo)
            @if(!empty(old('tipo_telefone')))
            <option value="{{ $tipo }}" {{ old('tipo_telefone') == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
            @elseif(isset($resultado->getTipoTelefone()[0]))
            <option value="{{ $tipo }}" {{ mb_strtoupper($tipo, 'UTF-8') == $resultado->getTipoTelefone()[0] ? 'selected' : '' }}>{{ $tipo }}</option>
            @else
            <option value="{{ $tipo }}">{{ $tipo }}</option>
            @endif
        @endforeach
        </select>
        @if($errors->has('tipo_telefone'))
        <div class="invalid-feedback">
            {{ $errors->first('tipo_telefone') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="telefone">{{ $codigos[4]['telefone'] }} - Nº de telefone <span class="text-danger">*</span></label>
        <input type="text"
            class="{{ $classes[4] }} form-control celularInput {{ $errors->has('telefone') ? 'is-invalid' : '' }} obrigatorio"
            name="telefone"
            id="telefone"
            value="{{ empty(old('telefone')) && isset($resultado->getTelefone()[0]) ? $resultado->getTelefone()[0] : old('telefone') }}"
            placeholder="(99) 99999-9999"
        />
        @if($errors->has('telefone'))
        <div class="invalid-feedback">
            {{ $errors->first('telefone') }}
        </div>
        @endif
    </div>
</div>

<fieldset id="opcoesCelular" {{ isset($resultado->getTelefone()[0]) && ($resultado->getTipoTelefone()[0] == 'CELULAR') ? '' : 'disabled' }}>
    <div class="form-row mb-2">
        <div class="col-sm mb-2-576">
            <label>{{ $codigos[4]['opcional_celular'] }} <small class="bold">(opcional)</small> - Opções de comunicação </label><br>
            @foreach(opcoes_celular() as $key => $tipo)
            <div class="form-check-inline">
                <label for="{{ 'opcional_celular_' . $key }}" class="form-check-label">
                    <input 
                        type="checkbox" 
                        name="opcional_celular[]"
                        id="{{ 'opcional_celular_' . $key }}"
                        class="{{ $classes[4] }} form-check-input {{ $errors->has('opcional_celular') ? 'is-invalid' : '' }}" 
                        value="{{ $tipo }}" 
                        @if(!empty(old('opcional_celular')))
                        {{ in_array($tipo, old('opcional_celular')) ? 'checked' : '' }}
                        @elseif(isset($resultado->getOpcionalCelular()[0]))
                        {{ in_array(mb_strtoupper($tipo, 'UTF-8'), $resultado->getOpcionalCelular()[0]) ? 'checked' : '' }}
                        @endif
                    >{{ $tipo }}
                    @if($errors->has('opcional_celular'))
                    <div class="invalid-feedback">
                        {{ $errors->first('opcional_celular') }}
                    </div>
                    @endif
                </label>
            </div>
            @endforeach
        </div>
    </div>
</fieldset>

<div class="linha-lg-mini"></div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="tipo_telefone_1">{{ $codigos[4]['tipo_telefone_1'] }} <small class="bold">(opcional)</small> - Tipo de telefone </label><br>
        <select 
            name="tipo_telefone_1" 
            id="tipo_telefone_1"
            class="{{ $classes[4] }} form-control {{ $errors->has('tipo_telefone_1') ? 'is-invalid' : '' }}"
        >
            <option value="">Selecione a opção...</option>
        @foreach(tipos_contatos() as $tipo)
            @if(!empty(old('tipo_telefone_1')))
            <option value="{{ $tipo }}" {{ old('tipo_telefone_1') == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
            @elseif(isset($resultado->getTipoTelefone()[1]))
            <option value="{{ $tipo }}" {{ mb_strtoupper($tipo, 'UTF-8') == $resultado->getTipoTelefone()[1] ? 'selected' : '' }}>{{ $tipo }}</option>
            @else
            <option value="{{ $tipo }}">{{ $tipo }}</option>
            @endif
        @endforeach
        </select>
        @if($errors->has('tipo_telefone_1'))
        <div class="invalid-feedback">
            {{ $errors->first('tipo_telefone_1') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="telefone_1">{{ $codigos[4]['telefone_1'] }} <small class="bold">(opcional)</small> - Nº de telefone </span></label>
        <input type="text"
            class="{{ $classes[4] }} form-control celularInput {{ $errors->has('telefone_1') ? 'is-invalid' : '' }}"
            name="telefone_1"
            id="telefone_1"
            value="{{ empty(old('telefone_1')) && isset($resultado->getTelefone()[1]) ? $resultado->getTelefone()[1] : old('telefone_1') }}"
            placeholder="(99) 99999-9999"
        />
        @if($errors->has('telefone_1'))
        <div class="invalid-feedback">
            {{ $errors->first('telefone_1') }}
        </div>
        @endif
    </div>
</div>

<fieldset id="opcoesCelular_1" {{ isset($resultado->getTelefone()[1]) && ($resultado->getTipoTelefone()[1] == 'CELULAR') ? '' : 'disabled' }}>
    <div class="form-row mb-2">
        <div class="col-sm mb-2-576">
            <label for="opcional_celular_1">{{ $codigos[4]['opcional_celular_1'] }} <small class="bold">(opcional)</small> - Opções de comunicação </label><br>
            @foreach(opcoes_celular() as $key => $tipo)
            <div class="form-check-inline">
                <label for="{{ 'opcional_celular__' . $key }}" class="form-check-label">
                    <input 
                        type="checkbox" 
                        name="opcional_celular_1[]"
                        id="{{ 'opcional_celular__' . $key }}"
                        class="{{ $classes[4] }} form-check-input {{ $errors->has('opcional_celular_1') ? 'is-invalid' : '' }}" 
                        value="{{ $tipo }}" 
                        @if(!empty(old('opcional_celular_1')))
                        {{ in_array($tipo, old('opcional_celular_1')) == $tipo ? 'checked' : '' }}
                        @elseif(isset($resultado->getOpcionalCelular()[1]))
                        {{ in_array(mb_strtoupper($tipo, 'UTF-8'), $resultado->getOpcionalCelular()[1]) ? 'checked' : '' }}
                        @endif
                    >{{ $tipo }}
                    @if($errors->has('opcional_celular_1'))
                    <div class="invalid-feedback">
                        {{ $errors->first('opcional_celular_1') }}
                    </div>
                    @endif
                </label>
            </div>
            @endforeach
        </div>
    </div>
</fieldset>