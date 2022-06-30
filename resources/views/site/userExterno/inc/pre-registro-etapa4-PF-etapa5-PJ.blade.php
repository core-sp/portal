<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="email">E-mail <span class="text-danger">*</span></label>
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

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="tipo_telefone">{{ array_search('tipo_telefone', $codPre) }} - Tipo de telefone <span class="text-danger">*</span></label><br>
        <select 
            name="tipo_telefone" 
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
        <label for="telefone">{{ array_search('telefone', $codPre) }} - Nº de telefone <span class="text-danger">*</span></label>
        <input type="text"
            class="{{ $classes[4] }} form-control telefoneInput {{ $errors->has('telefone') ? 'is-invalid' : '' }} obrigatorio"
            name="telefone"
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
            <label for="opcional_celular[]">{{ array_search('opcional_celular', $codPre) }} - Opcões de comunicação </label><br>
            @foreach(opcoes_celular() as $tipo)
            <div class="form-check-inline">
                <label class="form-check-label">
                    <input 
                        type="checkbox" 
                        name="opcional_celular[]"
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
        <label for="tipo_telefone_1">{{ array_search('tipo_telefone', $codPre) }} <small class="bold">(opcional)</small> - Tipo de telefone </label><br>
        <select 
            name="tipo_telefone_1" 
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
        <label for="telefone_1">{{ array_search('telefone', $codPre) }} <small class="bold">(opcional)</small> - Nº de telefone </span></label>
        <input type="text"
            class="{{ $classes[4] }} form-control telefoneInput {{ $errors->has('telefone_1') ? 'is-invalid' : '' }}"
            name="telefone_1"
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
            <label for="opcional_celular_1[]">{{ array_search('opcional_celular', $codPre) }} <small class="bold">(opcional)</small> - Opcões de comunicação </label><br>
            @foreach(opcoes_celular() as $tipo)
            <div class="form-check-inline">
                <label class="form-check-label">
                    <input 
                        type="checkbox" 
                        name="opcional_celular_1[]"
                        class="{{ $classes[4] }} form-check-input {{ $errors->has('opcional_celular_1') ? 'is-invalid' : '' }}" 
                        value="{{ $tipo }}" 
                        @if(!empty(old('opcional_celular_1')))
                        {{ old('opcional_celular_1') == $tipo ? 'checked' : '' }}
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