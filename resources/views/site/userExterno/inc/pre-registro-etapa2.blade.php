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
        <label for="cpf_cnpj">{{ array_search('cpf_cnpj', $codUser) }} - {{ strlen($resultado->userExterno->cpf_cnpj) == 11 ? 'CPF' : 'CNPJ' }} *</label>
        <input
            type="text"
            class="form-control cpfOuCnpj {{ $errors->has('cpf_cnpj') ? 'is-invalid' : '' }}"
            value="{{ $resultado->userExterno->cpf_cnpj }}"
            readonly
            disabled
        />
    </div>
    <div class="col-sm mb-2-576">
        <label for="registro_secundario">{{ array_search('registro_secundario', $codPre) }} - Registro Secundário *</label>
        <input
            type="text"
            name="registro_secundario"
            class="{{ $classes[4] }} {{ array_search('registro_secundario', $codPre) }} form-control {{ $errors->has('registro_secundario') ? 'is-invalid' : '' }}"
            value="{{ empty(old('registro_secundario')) && isset($resultado->registro_secundario) ? $resultado->registro_secundario : old('registro_secundario') }}"
            placeholder=""
        />
        @if($errors->has('registro_secundario'))
        <div class="invalid-feedback">
            {{ $errors->first('registro_secundario') }}
        </div>
        @endif
    </div>
</div>

@if(strlen($resultado->userExterno->cpf_cnpj) == 11)
<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="nome">{{ array_search('nome', $codUser) }} - Nome Completo *</label>
        <input
            type="text"
            class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
            value="{{ $resultado->userExterno->nome }}"
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
        <label for="nome_social">{{ array_search('nome_social', $codCpf) }} - Nome Social</label>
        <input
            name="nome_social"
            type="text"
            class="{{ $classes[2] }} {{ array_search('nome_social', $codCpf) }} form-control {{ $errors->has('nome_social') ? 'is-invalid' : '' }}"
            value="{{ empty(old('nome_social')) && isset($resultado->pessoaFisica->nome_social) ? $resultado->pessoaFisica->nome_social : old('nome_social') }}"
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
        <label for="sexo">{{ array_search('sexo', $codCpf) }} - Sexo *</label><br>
        <div class="form-check-inline">
            <label class="form-check-label">
                <input type="radio" 
                    class="{{ $classes[2] }} {{ array_search('sexo', $codCpf) }} form-check-input" 
                    name="sexo" 
                    value="F" {{ (!empty(old('sexo')) && (old('sexo') == 'F')) || (isset($resultado->pessoaFisica->sexo) && $resultado->pessoaFisica->sexo == 'F') ? 'checked' : '' }}
                />
                Feminino
            </label>
        </div>
        <div class="form-check-inline">
            <label class="form-check-label">
                <input type="radio" 
                    class="{{ $classes[2] }} {{ array_search('sexo', $codCpf) }} form-check-input" 
                    name="sexo" 
                    value="M" {{ (!empty(old('sexo')) && (old('sexo') == 'F')) || (isset($resultado->pessoaFisica->sexo) && $resultado->pessoaFisica->sexo == 'M') ? 'checked' : '' }}
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
        <label for="dt_nascimento">{{ array_search('dt_nascimento', $codCpf) }} - Data de Nascimento *</label>
        <input
            name="dt_nascimento"
            type="date"
            class="{{ $classes[2] }} {{ array_search('dt_nascimento', $codCpf) }} form-control {{ $errors->has('dt_nascimento') ? 'is-invalid' : '' }}"
            value="{{ empty(old('dt_nascimento')) && isset($resultado->pessoaFisica->dt_nascimento) ? $resultado->pessoaFisica->dt_nascimento : old('dt_nascimento') }}"
        />
        @if($errors->has('dt_nascimento'))
        <div class="invalid-feedback">
            {{ $errors->first('dt_nascimento') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="estado_civil">{{ array_search('estado_civil', $codCpf) }} - Estado Civil *</label>
        <select 
            name="estado_civil" 
            class="{{ $classes[2] }} {{ array_search('estado_civil', $codCpf) }} form-control {{ $errors->has('estado_civil') ? 'is-invalid' : '' }}" 
        >
            <option value="">Selecione a opção...</option>
        @foreach(estados_civis() as $estado_civil)
            @if(!empty(old('estado_civil')))
            <option value="{{ $estado_civil }}" {{ old('estado_civil') == $estado_civil ? 'selected' : '' }}>{{ $estado_civil }}</option>
            @elseif(isset($resultado->pessoaFisica->estado_civil))
            <option value="{{ $estado_civil }}" {{ $estado_civil == $resultado->pessoaFisica->estado_civil ? 'selected' : '' }}>{{ $estado_civil }}</option>
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
        <label for="nacionalidade">{{ array_search('nacionalidade', $codCpf) }} - Nacionalidade *</label>
        <select 
            name="nacionalidade" 
            class="{{ $classes[2] }} {{ array_search('nacionalidade', $codCpf) }} form-control {{ $errors->has('nacionalidade') ? 'is-invalid' : '' }}" 
        >
            <option value="">Selecione a opção...</option>
        @foreach(nacionalidades() as $nacionalidade)
            @if(!empty(old('nacionalidade')))
            <option value="{{ $nacionalidade }}" {{ old('nacionalidade') == $nacionalidade ? 'selected' : '' }}>{{ $nacionalidade }}</option>
            @elseif(isset($resultado->pessoaFisica->nacionalidade))
            <option value="{{ $nacionalidade }}" {{ $nacionalidade == $resultado->pessoaFisica->nacionalidade ? 'selected' : '' }}>{{ $nacionalidade }}</option>
            @else
            <option value="{{ $nacionalidade }}">{{ $nacionalidade }}</option>
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
        <label for="naturalidade">{{ array_search('naturalidade', $codCpf) }} - Naturalidade *</label>
        <select 
            name="naturalidade" 
            class="{{ $classes[2] }} {{ array_search('naturalidade', $codCpf) }} form-control {{ $errors->has('naturalidade') ? 'is-invalid' : '' }}" 
        >
            <option value="">Selecione a opção...</option>
        @foreach(estados() as $key => $naturalidade)
            @if(!empty(old('naturalidade')))
            <option value="{{ $key }}" {{ old('naturalidade') == $naturalidade ? 'selected' : '' }}>{{ $naturalidade }}</option>
            @elseif(isset($resultado->pessoaFisica->naturalidade))
            <option value="{{ $key }}" {{ $key == $resultado->pessoaFisica->naturalidade ? 'selected' : '' }}>{{ $naturalidade }}</option>
            @else
            <option value="{{ $key }}">{{ $naturalidade }}</option>
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
        <label for="nome_mae">{{ array_search('nome_mae', $codCpf) }} - Nome da Mãe *</label>
        <input
            name="nome_mae"
            type="text"
            class="{{ $classes[2] }} {{ array_search('nome_mae', $codCpf) }} form-control {{ $errors->has('nome_mae') ? 'is-invalid' : '' }}"
            value="{{ empty(old('nome_mae')) && isset($resultado->pessoaFisica->nome_mae) ? $resultado->pessoaFisica->nome_mae : old('nome_mae') }}"
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
        <label for="nome_pai">{{ array_search('nome_pai', $codCpf) }} - Nome do Pai *</label>
        <input
            name="nome_pai"
            type="text"
            class="{{ $classes[2] }} {{ array_search('nome_pai', $codCpf) }} form-control {{ $errors->has('nome_pai') ? 'is-invalid' : '' }}"
            value="{{ empty(old('nome_pai')) && isset($resultado->pessoaFisica->nome_pai) ? $resultado->pessoaFisica->nome_pai : old('nome_pai') }}"
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
        <label for="identidade">{{ array_search('identidade', $codCpf) }} - N° RG *</label>
        <input
            name="identidade"
            type="text"
            id="rg"
            class="{{ $classes[2] }} {{ array_search('identidade', $codCpf) }} form-control rgInput {{ $errors->has('identidade') ? 'is-invalid' : '' }}"
            value="{{ empty(old('identidade')) && isset($resultado->pessoaFisica->identidade) ? $resultado->pessoaFisica->identidade : old('identidade') }}"
            placeholder=""
            maxlength="20"
        />
        @if($errors->has('identidade'))
        <div class="invalid-feedback">
            {{ $errors->first('identidade') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="orgao_emissor">{{ array_search('orgao_emissor', $codCpf) }} - Órgão Emissor *</label>
        <input
            name="orgao_emissor"
            type="text"
            class="{{ $classes[2] }} {{ array_search('orgao_emissor', $codCpf) }} form-control {{ $errors->has('orgao_emissor') ? 'is-invalid' : '' }}"
            value="{{ empty(old('orgao_emissor')) && isset($resultado->pessoaFisica->orgao_emissor) ? $resultado->pessoaFisica->orgao_emissor : old('orgao_emissor') }}"
            placeholder=""
        />
        @if($errors->has('orgao_emissor'))
        <div class="invalid-feedback">
            {{ $errors->first('orgao_emissor') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="dt_expedicao">{{ array_search('dt_expedicao', $codCpf) }} - Data de Expedição *</label>
        <input
            name="dt_expedicao"
            type="date"
            class="{{ $classes[2] }} {{ array_search('dt_expedicao', $codCpf) }} form-control {{ $errors->has('dt_expedicao') ? 'is-invalid' : '' }}"
            value="{{ empty(old('dt_expedicao')) && isset($resultado->pessoaFisica->dt_expedicao) ? $resultado->pessoaFisica->dt_expedicao : old('dt_expedicao') }}"
        />
        @if($errors->has('dt_expedicao'))
        <div class="invalid-feedback">
            {{ $errors->first('dt_expedicao') }}
        </div>
        @endif
    </div>
</div>

@elseif(strlen($resultado->userExterno->cpf_cnpj) == 14)

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="razao_social">{{ array_search('razao_social', $codCnpj) }} - Razão Social *</label>
        <input
            name="razao_social"
            type="text"
            class="{{ $classes[3] }} {{ array_search('razao_social', $codCnpj) }} form-control {{ $errors->has('razao_social') ? 'is-invalid' : '' }}"
            value="{{ empty(old('razao_social')) && isset($resultado->pessoaJuridica->razao_social) ? $resultado->pessoaJuridica->razao_social : old('razao_social') }}"
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
        <label for="capital_social">{{ array_search('capital_social', $codCnpj) }} - Capital Social em R$ *</label>
        <input
            type="text"
            name="capital_social"
            class="{{ $classes[3] }} {{ array_search('capital_social', $codCnpj) }} form-control capitalSocial {{ $errors->has('capital_social') ? 'is-invalid' : '' }}"
            placeholder="1.000,00"
            value="{{ empty(old('capital_social')) && isset($resultado->pessoaJuridica->capital_social) ? $resultado->pessoaJuridica->capital_social : old('capital_social') }}"
        />
        @if($errors->has('capital_social'))
        <div class="invalid-feedback">
            {{ $errors->first('capital_social') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="nire">{{ array_search('nire', $codCnpj) }} - NIRE *</label>
        <input
            type="text"
            name="nire"
            class="{{ $classes[3] }} {{ array_search('nire', $codCnpj) }} form-control {{ $errors->has('nire') ? 'is-invalid' : '' }}"
            placeholder="NIRE"
            value="{{ empty(old('nire')) && isset($resultado->pessoaJuridica->nire) ? $resultado->pessoaJuridica->nire : old('nire') }}"
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
        <label for="tipo_empresa">{{ array_search('tipo_empresa', $codCnpj) }} - Tipo da Empresa *</label><br>
        @foreach(tipos_empresa() as $tipo)
        <div class="form-check-inline">
            <label class="form-check-label">
                @if(!empty(old('tipo_empresa')))
                <input type="radio" class="{{ $classes[3] }} {{ array_search('tipo_empresa', $codCnpj) }} form-check-input" name="tipo_empresa" value="{{ $tipo }}" {{ old('tipo_empresa') == $tipo ? 'checked' : '' }} />{{ $tipo }}
                @elseif(isset($resultado->pessoaJuridica->tipo_empresa))
                <input type="radio" class="{{ $classes[3] }} {{ array_search('tipo_empresa', $codCnpj) }} form-check-input" name="tipo_empresa" value="{{ $tipo }}" {{ $tipo == $resultado->pessoaJuridica->tipo_empresa ? 'checked' : '' }} />{{ $tipo }}
                @else
                <input type="radio" class="{{ $classes[3] }} {{ array_search('tipo_empresa', $codCnpj) }} form-check-input" name="tipo_empresa" value="{{ $tipo }}" />{{ $tipo }}
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
        <label for="dt_inicio_atividade">{{ array_search('dt_inicio_atividade', $codCnpj) }} - Data início da atividade *</label>
        <input
            type="date"
            name="dt_inicio_atividade"
            class="{{ $classes[3] }} {{ array_search('dt_inicio_atividade', $codCnpj) }} form-control {{ $errors->has('dt_inicio_atividade') ? 'is-invalid' : '' }}"
            value="{{ empty(old('dt_inicio_atividade')) && isset($resultado->pessoaJuridica->dt_inicio_atividade) ? $resultado->pessoaJuridica->dt_inicio_atividade : old('dt_inicio_atividade') }}"
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
        <label for="inscricao_municipal">{{ array_search('inscricao_municipal', $codCnpj) }} - Inscrição Municipal *</label>
        <input
            type="text"
            name="inscricao_municipal"
            class="{{ $classes[3] }} {{ array_search('inscricao_municipal', $codCnpj) }} form-control {{ $errors->has('inscricao_municipal') ? 'is-invalid' : '' }}"
            placeholder=""
            value="{{ empty(old('inscricao_municipal')) && isset($resultado->pessoaJuridica->inscricao_municipal) ? $resultado->pessoaJuridica->inscricao_municipal : old('inscricao_municipal') }}"
        />
        @if($errors->has('inscricao_municipal'))
        <div class="invalid-feedback">
            {{ $errors->first('inscricao_municipal') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="inscricao_estadual">{{ array_search('inscricao_estadual', $codCnpj) }} - Inscrição Estadual *</label>
        <input
            type="text"
            name="inscricao_estadual"
            class="{{ $classes[3] }} {{ array_search('inscricao_estadual', $codCnpj) }} form-control {{ $errors->has('inscricao_estadual') ? 'is-invalid' : '' }}"
            placeholder=""
            value="{{ empty(old('inscricao_estadual')) && isset($resultado->pessoaJuridica->inscricao_estadual) ? $resultado->pessoaJuridica->inscricao_estadual : old('inscricao_estadual') }}"
        />
        @if($errors->has('inscricao_estadual'))
        <div class="invalid-feedback">
            {{ $errors->first('inscricao_estadual') }}
        </div>
        @endif
    </div>
</div>
@endif

<div class="linha-lg-mini"></div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="ramo_atividade">{{ array_search('ramo_atividade', $codPre) }} - Ramo de Atividade *</label>
        <input
            name="ramo_atividade"
            type="text"
            class="{{ $classes[4] }} {{ array_search('ramo_atividade', $codPre) }} form-control {{ $errors->has('ramo_atividade') || isset($justificativas) ? 'is-invalid' : '' }}"
            value="{{ empty(old('ramo_atividade')) && isset($resultado->ramo_atividade) ? $resultado->ramo_atividade : old('ramo_atividade') }}"
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
        <label for="segmento">{{ array_search('segmento', $codPre) }} - Segmento *</label>
        <select 
            name="segmento" 
            class="{{ $classes[4] }} {{ array_search('segmento', $codPre) }} form-control {{ $errors->has('segmento') || isset($justificativas) ? 'is-invalid' : '' }}" 
        >
            <option value="">Selecione a opção...</option>
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
        <label for="idregional">{{ array_search('idregional', $codPre) }} - Região de Atuação *</label>
        <select 
            name="idregional" 
            class="{{ $classes[4] }} {{ array_search('idregional', $codPre) }} form-control {{ $errors->has('idregional') ? 'is-invalid' : '' }}" 
        >
            <option value="">Selecione a opção...</option>
        @foreach($regionais as $regional)
            @if(!empty(old('idregional')))
            <option value="{{ $regional->idregional }}" {{ old('idregional') == $regional->idregional ? 'selected' : '' }}>{{ $regional->regional }}</option>
            @elseif(isset($resultado->idregional))
            <option value="{{ $regional->idregional }}" {{ $regional->idregional == $resultado->idregional ? 'selected' : '' }}>{{ $regional->regional }}</option>
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