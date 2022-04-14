@php
$justificativas = 'Teste para mostrar as justificativas do Atendimento após análise';
@endphp

@if(isset($justificativas))
    <div class="d-block w-100">
        <p class="alert alert-warning">{{ $justificativas }}</p>
    </div>
@endif

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="cpf_cnpj">R07 - {{ strlen($user->cpf_cnpj) == 11 ? 'CPF' : 'CNPJ' }} *</label>
        <input
            type="text"
            class="form-control cpfOuCnpj {{ $errors->has('cpf_cnpj') ? 'is-invalid' : '' }}"
            value="{{ $user->cpf_cnpj }}"
            readonly
            disabled
        />
    </div>
</div>

@if(strlen($user->cpf_cnpj) == 11)
<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="nome">R08 - Nome Completo *</label>
        <input
            type="text"
            class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
            value="{{ $user->nome }}"
            placeholder="Nome Completo"
            readonly
            disabled
        />
        @if($errors->has('nome'))
        <div class="invalid-feedback">
            {{ $errors->first('nome') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="nome_social">R09 - Nome Social</label>
        <input
            name="nome_social"
            type="text"
            class="form-control {{ $errors->has('nome_social') ? 'is-invalid' : '' }}"
            value="{{-- $user->nome --}}"
            placeholder="Nome Social"
        />
        @if($errors->has('nome_social'))
        <div class="invalid-feedback">
            {{ $errors->first('nome_social') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="sexo">R10 - Sexo *</label><br>
        <div class="form-check-inline">
            <label class="form-check-label">
                <input type="radio" 
                    class="form-check-input" 
                    name="sexo" 
                    value="F" {{ (!empty(old('sexo')) && (old('sexo') == 'F')) || (isset($resultado->sexo) && $resultado->sexo == 'F') ? 'checked' : '' }}
                />
                Feminino
            </label>
        </div>
        <div class="form-check-inline">
            <label class="form-check-label">
                <input type="radio" 
                    class="form-check-input" 
                    name="sexo" 
                    value="M" {{ (!empty(old('sexo')) && (old('sexo') == 'F')) || (isset($resultado->sexo) && $resultado->sexo == 'M') ? 'checked' : '' }}
                />
                Masculino
            </label>
        </div>
        @if($errors->has('sexo'))
        <div class="invalid-feedback">
            {{ $errors->first('sexo') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="dt_nasc">R11 - Data de Nascimento *</label>
        <input
            name="dt_nasc"
            type="date"
            class="form-control {{ $errors->has('dt_nasc') ? 'is-invalid' : '' }}"
            value="{{-- $user->nome --}}"
        />
        @if($errors->has('dt_nasc'))
        <div class="invalid-feedback">
            {{ $errors->first('dt_nasc') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="estado_civil">R12 - Estado Civil *</label>
        <select 
            name="estado_civil" 
            class="form-control {{ $errors->has('estado_civil') ? 'is-invalid' : '' }}" 
        >
        @foreach(estados_civis() as $estado_civil)
            @if(!empty(old('estado_civil')))
            <option value="{{ $estado_civil }}" {{ old('estado_civil') == $estado_civil ? 'selected' : '' }}>{{ $estado_civil }}</option>
            @elseif(isset($resultado->estado_civil))
            <option value="{{ $estado_civil }}" {{ $estado_civil == $resultado->estado_civil ? 'selected' : '' }}>{{ $estado_civil }}</option>
            @else
            <option value="{{ $estado_civil }}">{{ $estado_civil }}</option>
            @endif
        @endforeach
        </select>
        @if($errors->has('estado_civil'))
        <div class="invalid-feedback">
            {{ $errors->first('estado_civil') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="nacionalidade">R13 - Nacionalidade *</label>
        <select 
            name="nacionalidade" 
            class="form-control {{ $errors->has('nacionalidade') ? 'is-invalid' : '' }}" 
        >
        @foreach(nacionalidades() as $nacionalidade)
            @if(!empty(old('nacionalidade')))
            <option value="{{ $nacionalidade }}" {{ old('nacionalidade') == $nacionalidade ? 'selected' : '' }}>{{ $nacionalidade }}</option>
            @elseif(isset($resultado->nacionalidade))
            <option value="{{ $nacionalidade }}" {{ $nacionalidade == $resultado->nacionalidade ? 'selected' : '' }}>{{ $nacionalidade }}</option>
            @else
            <option value="{{ $nacionalidade }}" {{ $nacionalidade == 'Brasileiro' ? 'selected' : '' }}>{{ $nacionalidade }}</option>
            @endif
        @endforeach
        </select>
        @if($errors->has('nacionalidade'))
        <div class="invalid-feedback">
            {{ $errors->first('nacionalidade') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="naturalidade">R14 - Naturalidade *</label>
        <select 
            name="naturalidade" 
            class="form-control {{ $errors->has('naturalidade') ? 'is-invalid' : '' }}" 
        >
        @foreach(estados() as $key => $naturalidade)
            @if(!empty(old('naturalidade')))
            <option value="{{ $key }}" {{ old('naturalidade') == $naturalidade ? 'selected' : '' }}>{{ $naturalidade }}</option>
            @elseif(isset($resultado->naturalidade))
            <option value="{{ $key }}" {{ $key == $resultado->naturalidade ? 'selected' : '' }}>{{ $naturalidade }}</option>
            @else
            <option value="{{ $key }}" {{ $key == 'SP' ? 'selected' : '' }}>{{ $naturalidade }}</option>
            @endif
        @endforeach
        </select>
        @if($errors->has('naturalidade'))
        <div class="invalid-feedback">
            {{ $errors->first('naturalidade') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="nome_mae">R15 - Nome da Mãe *</label>
        <input
            name="nome_mae"
            type="text"
            class="form-control {{ $errors->has('nome_mae') ? 'is-invalid' : '' }}"
            value="{{-- $user->nome --}}"
            placeholder="Nome da Mãe"
            minlength="5"
            maxlength="191"
        />
        @if($errors->has('nome_mae'))
        <div class="invalid-feedback">
            {{ $errors->first('nome_mae') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="nome_pai">R16 - Nome do Pai *</label>
        <input
            name="nome_pai"
            type="text"
            class="form-control {{ $errors->has('nome_pai') ? 'is-invalid' : '' }}"
            value="{{-- $user->nome --}}"
            placeholder="Nome do Pai"
            minlength="5"
            maxlength="191"
        />
        @if($errors->has('nome_pai'))
        <div class="invalid-feedback">
            {{ $errors->first('nome_pai') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="rg">R17 - N° RG *</label>
        <input
            name="rg"
            type="text"
            id="rg"
            class="form-control rgInput {{ $errors->has('rg') ? 'is-invalid' : '' }}"
            value="{{-- $user->nome --}}"
            placeholder="RG"
            maxlength="20"
        />
        @if($errors->has('rg'))
        <div class="invalid-feedback">
            {{ $errors->first('rg') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="emissor">R18 - Órgão Emissor *</label>
        <input
            name="emissor"
            type="text"
            class="form-control {{ $errors->has('emissor') ? 'is-invalid' : '' }}"
            value="{{-- $user->nome --}}"
            placeholder="Emissor"
            maxlength="10"
        />
        @if($errors->has('emissor'))
        <div class="invalid-feedback">
            {{ $errors->first('emissor') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="dt_expedicao">R19 - Data de Expedição *</label>
        <input
            name="dt_expedicao"
            type="date"
            class="form-control {{ $errors->has('dt_expedicao') ? 'is-invalid' : '' }}"
            value="{{-- $user->nome --}}"
        />
        @if($errors->has('dt_expedicao'))
        <div class="invalid-feedback">
            {{ $errors->first('dt_expedicao') }}
        </div>
        @endif
    </div>
</div>

@elseif(strlen($user->cpf_cnpj) == 14)

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="razao_social">R20 - Razão Social *</label>
        <input
            name="razao_social"
            type="text"
            class="form-control {{ $errors->has('razao_social') ? 'is-invalid' : '' }}"
            value="{{-- $user->nome --}}"
            placeholder="Razão Social"
            minlength="5"
            maxlength="191"
        />
        @if($errors->has('razao_social'))
        <div class="invalid-feedback">
            {{ $errors->first('razao_social') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="capital_social">R21 - Capital Social em R$ *</label>
        <input
            type="text"
            name="capital_social"
            class="form-control capitalSocial {{ $errors->has('capital_social') ? 'is-invalid' : '' }}"
            placeholder="1.000,00"
            value="{{ isset($resultado->capital_social) ? $resultado->capital_social : old('capital_social') }}"
        />
        @if($errors->has('capital_social'))
        <div class="invalid-feedback">
            {{ $errors->first('capital_social') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="nire">R22 - NIRE *</label>
        <input
            type="text"
            name="nire"
            class="form-control {{ $errors->has('nire') ? 'is-invalid' : '' }}"
            placeholder="NIRE"
            value="{{ isset($resultado->nire) ? $resultado->nire : old('nire') }}"
            maxlength="20"
        />
        @if($errors->has('nire'))
        <div class="invalid-feedback">
            {{ $errors->first('nire') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="tipo_empresa">R23 - Tipo da Empresa *</label><br>
        @foreach(tipos_empresa() as $tipo)
        <div class="form-check-inline">
            <label class="form-check-label">
                @if(!empty(old('tipo_empresa')))
                <input type="radio" class="form-check-input" name="tipo_empresa" value="{{ $tipo }}" {{ old('tipo_empresa') == $tipo ? 'checked' : '' }} />{{ $tipo }}
                @elseif(isset($resultado->tipo_empresa))
                <input type="radio" class="form-check-input" name="tipo_empresa" value="{{ $tipo }}" {{ $tipo == $resultado->tipo ? 'checked' : '' }} />{{ $tipo }}
                @else
                <input type="radio" class="form-check-input" name="tipo_empresa" value="{{ $tipo }}" />{{ $tipo }}
                @endif
            </label>
        </div>
        @endforeach
        @if($errors->has('tipo_empresa'))
        <div class="invalid-feedback">
            {{ $errors->first('tipo_empresa') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="dt_inicio_atividade">R24 - Data início da atividade *</label>
        <input
            type="date"
            name="dt_inicio_atividade"
            class="form-control {{ $errors->has('dt_inicio_atividade') ? 'is-invalid' : '' }}"
            value="{{ isset($resultado->dt_inicio_atividade) ? $resultado->dt_inicio_atividade : old('dt_inicio_atividade') }}"
        />
        @if($errors->has('dt_inicio_atividade'))
        <div class="invalid-feedback">
            {{ $errors->first('dt_inicio_atividade') }}
        </div>
        @endif
    </div>
</div>

<!-- Verificar validação das Inscrições -->
<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="insc_estadual">R25 - Inscrição Estadual *</label>
        <input
            type="text"
            name="insc_estadual"
            class="form-control {{ $errors->has('insc_estadual') ? 'is-invalid' : '' }}"
            placeholder=""
            value="{{ isset($resultado->insc_estadual) ? $resultado->insc_estadual : old('insc_estadual') }}"
        />
        @if($errors->has('insc_estadual'))
        <div class="invalid-feedback">
            {{ $errors->first('insc_estadual') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="insc_municipal">R26 - Inscrição Municipal *</label>
        <input
            type="text"
            name="insc_municipal"
            class="form-control {{ $errors->has('insc_municipal') ? 'is-invalid' : '' }}"
            placeholder=""
            value="{{ isset($resultado->insc_municipal) ? $resultado->insc_municipal : old('insc_municipal') }}"
        />
        @if($errors->has('insc_municipal'))
        <div class="invalid-feedback">
            {{ $errors->first('insc_municipal') }}
        </div>
        @endif
    </div>
</div>
@endif

<div class="linha-lg-mini"></div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="ramo_atividade">R27 - Ramo de Atividade *</label>
        <input
            name="ramo_atividade"
            type="text"
            class="form-control {{ $errors->has('ramo_atividade') || isset($justificativas) ? 'is-invalid' : '' }}"
            value="{{-- $user->nome --}}"
            placeholder="Ramo de Atividade"
            minlength="5"
            maxlength="191"
        />
        @if($errors->has('ramo_atividade'))
        <div class="invalid-feedback">
            {{ $errors->first('ramo_atividade') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="segmento">R28 - Segmento *</label>
        <select 
            name="segmento" 
            class="form-control {{ $errors->has('segmento') || isset($justificativas) ? 'is-invalid' : '' }}" 
        >
        @foreach(segmentos() as $segmento)
            @if(!empty(old('segmento')))
            <option value="{{ $segmento }}" {{ old('segmento') == $segmento ? 'selected' : '' }}>{{ $segmento }}</option>
            @elseif(isset($resultado->segmento))
            <option value="{{ $segmento }}" {{ $segmento == $resultado->segmento ? 'selected' : '' }}>{{ $segmento }}</option>
            @else
            <option value="{{ $segmento }}">{{ $segmento }}</option>
            @endif
        @endforeach
        </select>
        @if($errors->has('segmento'))
        <div class="invalid-feedback">
            {{ $errors->first('segmento') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="idregional">R29 - Região de Atuação *</label>
        <select 
            name="idregional" 
            class="form-control {{ $errors->has('idregional') ? 'is-invalid' : '' }}" 
        >
        @foreach($regionais as $regional)
            @if(!empty(old('idregional')))
            <option value="{{ $regional->idregional }}" {{ old('idregional') == $regional->idregional ? 'selected' : '' }}>{{ $regional->regional }}</option>
            @elseif(isset($resultado->idregional))
            <option value="{{ $regional->idregional }}" {{ $regional->idregional == $resultado->regional ? 'selected' : '' }}>{{ $regional->regional }}</option>
            @else
            <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
            @endif
        @endforeach
        </select>
        @if($errors->has('idregional'))
        <div class="invalid-feedback">
            {{ $errors->first('idregional') }}
        </div>
        @endif
    </div>
</div>