@php
    $correcoes = $resultado->getTextosJustificadosByAba($codigos[1]);
@endphp
@if($resultado->userPodeCorrigir() && !empty($correcoes))
    <div class="d-block w-100">
        <div class="alert alert-warning">
            <span class="bold">Justificativa(s):</span>
            <br>
        @foreach($correcoes as $key => $texto)
            <p>
                <span class="bold">{{ $key . ': ' }}</span>{{ $texto }}
            </p>
        @endforeach
        </div>
    </div>
@endif

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="cpf_cnpj">{{ $resultado->userExterno->isPessoaFisica() ? 'CPF' : 'CNPJ' }} <span class="text-danger">*</span></label>
        <input
            type="text"
            id="cpf_cnpj"
            class="form-control cpfOuCnpj {{ $errors->has('cpf_cnpj') ? 'is-invalid' : '' }}"
            value="{{ $resultado->userExterno->cpf_cnpj }}"
            readonly
            disabled
        />
    </div>
</div>

@if($resultado->userExterno->isPessoaFisica())
<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="nome_pr">Nome Completo <span class="text-danger">*</span></label>
        <input
            type="text"
            id="nome_pr"
            class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
            value="{{ $resultado->userExterno->nome }}"
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
        <label for="nome_social">{{ $codigos[1]['nome_social'] }} - Nome Social</label>
        <input
            name="nome_social"
            id="nome_social"
            type="text"
            class="{{ $classes[2] }} text-uppercase form-control {{ $errors->has('nome_social') ? 'is-invalid' : '' }}"
            value="{{ empty(old('nome_social')) && isset($resultado->pessoaFisica->nome_social) ? $resultado->pessoaFisica->nome_social : old('nome_social') }}"
            minlength="5"
            maxlength="191"
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
        <label for="sexo">{{ $codigos[1]['sexo'] }} - Gênero <span class="text-danger">*</span></label>
        <br>
        <select 
            name="sexo"
            id="sexo"
            class="{{ $classes[2] }} form-control {{ $errors->has('sexo') ? 'is-invalid' : '' }} obrigatorio"
        >
            <option value="">Selecione a opção...</option>
        @foreach(generos() as $key => $genero)
            @if(!empty(old('sexo')))
            <option value="{{ $key }}" {{ old('sexo') == $key ? 'selected' : '' }}>{{ $genero }}</option>
            @elseif(isset($resultado->pessoaFisica->sexo))
            <option value="{{ $key }}" {{ $key == $resultado->pessoaFisica->sexo ? 'selected' : '' }}>{{ $genero }}</option>
            @else
            <option value="{{ $key }}">{{ $genero }}</option>
            @endif
        @endforeach
        </select>
        @if($errors->has('sexo'))
        <div class="invalid-feedback">
            {{ $errors->first('sexo') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="dt_nascimento">{{ $codigos[1]['dt_nascimento'] }} - Data de Nascimento <span class="text-danger">*</span></label>
        <input
            name="dt_nascimento"
            id="dt_nascimento"
            type="date"
            class="{{ $classes[2] }} form-control {{ $errors->has('dt_nascimento') ? 'is-invalid' : '' }} obrigatorio"
            value="{{ empty(old('dt_nascimento')) && isset($resultado->pessoaFisica->dt_nascimento) ? $resultado->pessoaFisica->dt_nascimento : old('dt_nascimento') }}"
            max="{{ Carbon\Carbon::today()->subYears(18)->format('Y-m-d') }}"
        />
        @if($errors->has('dt_nascimento'))
        <div class="invalid-feedback">
            {{ $errors->first('dt_nascimento') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="estado_civil">{{ $codigos[1]['estado_civil'] }} - Estado Civil</label>
        <select 
            name="estado_civil" 
            id="estado_civil"
            class="{{ $classes[2] }} form-control {{ $errors->has('estado_civil') ? 'is-invalid' : '' }}" 
        >
            <option value="">Selecione a opção...</option>
        @foreach(estados_civis() as $estado_civil)
            @if(!empty(old('estado_civil')))
            <option value="{{ $estado_civil }}" {{ old('estado_civil') == $estado_civil ? 'selected' : '' }}>{{ $estado_civil }}</option>
            @elseif(isset($resultado->pessoaFisica->estado_civil))
            <option value="{{ $estado_civil }}" {{ mb_strtoupper($estado_civil, 'UTF-8') == $resultado->pessoaFisica->estado_civil ? 'selected' : '' }}>{{ $estado_civil }}</option>
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
        <label for="nacionalidade">{{ $codigos[1]['nacionalidade'] }} - Nacionalidade <span class="text-danger">*</span></label>
        <select 
            name="nacionalidade" 
            id="nacionalidade"
            class="{{ $classes[2] }} form-control {{ $errors->has('nacionalidade') ? 'is-invalid' : '' }} obrigatorio" 
        >
            <option value="">Selecione a opção...</option>
        @foreach(nacionalidades() as $nacionalidade)
            @if(!empty(old('nacionalidade')))
            <option value="{{ $nacionalidade }}" {{ old('nacionalidade') == $nacionalidade ? 'selected' : '' }}>{{ $nacionalidade }}</option>
            @elseif(isset($resultado->pessoaFisica->nacionalidade))
            <option value="{{ $nacionalidade }}" {{ mb_strtoupper($nacionalidade, 'UTF-8') == $resultado->pessoaFisica->nacionalidade ? 'selected' : '' }}>{{ $nacionalidade }}</option>
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
</div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="naturalidade_cidade">{{ $codigos[1]['naturalidade_cidade'] }} - Naturalidade - Cidade <span class="text-danger">*</span></label>
        <input
            name="naturalidade_cidade"
            id="naturalidade_cidade"
            type="text"
            class="{{ $classes[2] }} text-uppercase form-control {{ $errors->has('naturalidade_cidade') ? 'is-invalid' : '' }} obrigatorio"
            value="{{ empty(old('naturalidade_cidade')) && isset($resultado->pessoaFisica->naturalidade_cidade) ? $resultado->pessoaFisica->naturalidade_cidade : old('naturalidade_cidade') }}"
            minlength="4"
            maxlength="191"
            {{ isset($resultado->pessoaFisica->nacionalidade) && ($resultado->pessoaFisica->nacionalidade != 'BRASILEIRA') ? 'disabled' : '' }}
        />
        @if($errors->has('naturalidade_cidade'))
        <div class="invalid-feedback">
            {{ $errors->first('naturalidade_cidade') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="naturalidade_estado">{{ $codigos[1]['naturalidade_estado'] }} - Naturalidade - Estado <span class="text-danger">*</span></label>
        <select 
            name="naturalidade_estado" 
            id="naturalidade_estado"
            class="{{ $classes[2] }} form-control {{ $errors->has('naturalidade_estado') ? 'is-invalid' : '' }} obrigatorio" 
            {{ isset($resultado->pessoaFisica->nacionalidade) && ($resultado->pessoaFisica->nacionalidade != 'BRASILEIRA') ? 'disabled' : '' }}
        >
            <option value="">Selecione a opção...</option>
        @foreach(estados() as $key => $naturalidade)
            @if(!empty(old('naturalidade_estado')))
            <option value="{{ $key }}" {{ old('naturalidade_estado') == $key ? 'selected' : '' }}>{{ $naturalidade }}</option>
            @elseif(isset($resultado->pessoaFisica->naturalidade_estado))
            <option value="{{ $key }}" {{ mb_strtoupper($key, 'UTF-8') == $resultado->pessoaFisica->naturalidade_estado ? 'selected' : '' }}>{{ $naturalidade }}</option>
            @else
            <option value="{{ $key }}">{{ $naturalidade }}</option>
            @endif
        @endforeach
        </select>
        @if($errors->has('naturalidade_estado'))
        <div class="invalid-feedback">
            {{ $errors->first('naturalidade_estado') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="nome_mae">{{ $codigos[1]['nome_mae'] }} - Nome da Mãe <span class="text-danger">*</span></label>
        <input
            name="nome_mae"
            id="nome_mae"
            type="text"
            class="{{ $classes[2] }} text-uppercase form-control {{ $errors->has('nome_mae') ? 'is-invalid' : '' }} obrigatorio"
            value="{{ empty(old('nome_mae')) && isset($resultado->pessoaFisica->nome_mae) ? $resultado->pessoaFisica->nome_mae : old('nome_mae') }}"
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
        <label for="nome_pai">{{ $codigos[1]['nome_pai'] }} - Nome do Pai</label>
        <input
            name="nome_pai"
            id="nome_pai"
            type="text"
            class="{{ $classes[2] }} text-uppercase form-control {{ $errors->has('nome_pai') ? 'is-invalid' : '' }}"
            value="{{ empty(old('nome_pai')) && isset($resultado->pessoaFisica->nome_pai) ? $resultado->pessoaFisica->nome_pai : old('nome_pai') }}"
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
        <label for="tipo_identidade">{{ $codigos[1]['tipo_identidade'] }} - Tipo do documento de identidade <span class="text-danger">*</span></label><br>
        <select 
            name="tipo_identidade" 
            id="tipo_identidade"
            class="{{ $classes[2] }} form-control {{ $errors->has('tipo_identidade') ? 'is-invalid' : '' }} obrigatorio"
        >
            <option value="">Selecione a opção...</option>
        @foreach(tipos_identidade() as $tipo)
            @if(!empty(old('tipo_identidade')))
            <option value="{{ $tipo }}" {{ old('tipo_identidade') == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
            @elseif(isset($resultado->pessoaFisica->tipo_identidade))
            <option value="{{ $tipo }}" {{ mb_strtoupper($tipo, 'UTF-8') == $resultado->pessoaFisica->tipo_identidade ? 'selected' : '' }}>{{ $tipo }}</option>
            @else
            <option value="{{ $tipo }}">{{ $tipo }}</option>
            @endif
        @endforeach
        </select>
        @if($errors->has('tipo_identidade'))
        <div class="invalid-feedback">
            {{ $errors->first('tipo_identidade') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="identidade">{{ $codigos[1]['identidade'] }} - N° do documento de identidade <span class="text-danger">*</span></label>
        <input
            name="identidade"
            id="identidade"
            type="text"
            class="{{ $classes[2] }} text-uppercase form-control {{ $errors->has('identidade') ? 'is-invalid' : '' }} obrigatorio"
            value="{{ empty(old('identidade')) && isset($resultado->pessoaFisica->identidade) ? $resultado->pessoaFisica->identidade : old('identidade') }}"
            minlength="4"
            maxlength="30"
        />
        @if($errors->has('identidade'))
        <div class="invalid-feedback">
            {{ $errors->first('identidade') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="orgao_emissor">{{ $codigos[1]['orgao_emissor'] }} - Órgão Emissor <span class="text-danger">*</span></label>
        <input
            name="orgao_emissor"
            id="orgao_emissor"
            type="text"
            class="{{ $classes[2] }} form-control text-uppercase {{ $errors->has('orgao_emissor') ? 'is-invalid' : '' }} obrigatorio"
            value="{{ empty(old('orgao_emissor')) && isset($resultado->pessoaFisica->orgao_emissor) ? $resultado->pessoaFisica->orgao_emissor : old('orgao_emissor') }}"
            minlength="3"
            maxlength="191"
        />
        @if($errors->has('orgao_emissor'))
        <div class="invalid-feedback">
            {{ $errors->first('orgao_emissor') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="dt_expedicao">{{ $codigos[1]['dt_expedicao'] }} - Data de Expedição <span class="text-danger">*</span></label>
        <input
            name="dt_expedicao"
            id="dt_expedicao"
            type="date"
            class="{{ $classes[2] }} form-control {{ $errors->has('dt_expedicao') ? 'is-invalid' : '' }} obrigatorio"
            value="{{ empty(old('dt_expedicao')) && isset($resultado->pessoaFisica->dt_expedicao) ? $resultado->pessoaFisica->dt_expedicao : old('dt_expedicao') }}"
            max="{{ date('Y-m-d') }}"
        />
        @if($errors->has('dt_expedicao'))
        <div class="invalid-feedback">
            {{ $errors->first('dt_expedicao') }}
        </div>
        @endif
    </div>
</div>

@else

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="razao_social">{{ $codigos[1]['razao_social'] }} - Razão Social <span class="text-danger">*</span></label>
        <input
            name="razao_social"
            cccccc
            type="text"
            class="{{ $classes[3] }} text-uppercase form-control {{ $errors->has('razao_social') ? 'is-invalid' : '' }} obrigatorio"
            value="{{ empty(old('razao_social')) && isset($resultado->pessoaJuridica->razao_social) ? $resultado->pessoaJuridica->razao_social : old('razao_social') }}"
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
        <label for="capital_social">{{ $codigos[1]['capital_social'] }} - Capital Social em R$ <span class="text-danger">*</span></label>
        <input
            type="text"
            name="capital_social"
            id="capital_social"
            class="{{ $classes[3] }} form-control capitalSocial {{ $errors->has('capital_social') ? 'is-invalid' : '' }} obrigatorio"
            placeholder="1.000,00"
            value="{{ empty(old('capital_social')) && isset($resultado->pessoaJuridica->capital_social) ? $resultado->pessoaJuridica->capital_social : old('capital_social') }}"
            minlength="4"
            maxlength="16"
        />
        @if($errors->has('capital_social'))
        <div class="invalid-feedback">
            {{ $errors->first('capital_social') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="nire">{{ $codigos[1]['nire'] }} - NIRE</label>
        <input
            type="text"
            name="nire"
            id="nire"
            class="{{ $classes[3] }} text-uppercase form-control {{ $errors->has('nire') ? 'is-invalid' : '' }}"
            placeholder="NIRE"
            value="{{ empty(old('nire')) && isset($resultado->pessoaJuridica->nire) ? $resultado->pessoaJuridica->nire : old('nire') }}"
            minlength="1"
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
        <label for="tipo_empresa">{{ $codigos[1]['tipo_empresa'] }} - Tipo da Empresa <span class="text-danger">*</span></label>
        <br>
        <select 
            name="tipo_empresa" 
            id="tipo_empresa"
            class="{{ $classes[3] }} form-control {{ $errors->has('tipo_empresa') ? 'is-invalid' : '' }} obrigatorio"
        >
            <option value="">Selecione a opção...</option>
        @foreach(tipos_empresa() as $tipo)
            @if(!empty(old('tipo_empresa')))
            <option value="{{ $tipo }}" {{ old('tipo_empresa') == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
            @elseif(isset($resultado->pessoaJuridica->tipo_empresa))
            <option value="{{ $tipo }}" {{ mb_strtoupper($tipo, 'UTF-8') == $resultado->pessoaJuridica->tipo_empresa ? 'selected' : '' }}>{{ $tipo }}</option>
            @else
            <option value="{{ $tipo }}">{{ $tipo }}</option>
            @endif
        @endforeach
        </select>
        @if($errors->has('tipo_empresa'))
        <div class="invalid-feedback">
            {{ $errors->first('tipo_empresa') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="dt_inicio_atividade">{{ $codigos[1]['dt_inicio_atividade'] }} - Data início da atividade <span class="text-danger">*</span></label>
        <input
            type="date"
            name="dt_inicio_atividade"
            id="dt_inicio_atividade"
            class="{{ $classes[3] }} form-control {{ $errors->has('dt_inicio_atividade') ? 'is-invalid' : '' }} obrigatorio"
            value="{{ empty(old('dt_inicio_atividade')) && isset($resultado->pessoaJuridica->dt_inicio_atividade) ? $resultado->pessoaJuridica->dt_inicio_atividade : old('dt_inicio_atividade') }}"
            max="{{ date('Y-m-d') }}"
        />
        @if($errors->has('dt_inicio_atividade'))
        <div class="invalid-feedback">
            {{ $errors->first('dt_inicio_atividade') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="inscricao_municipal">{{ $codigos[1]['inscricao_municipal'] }} - Inscrição Municipal</label>
        <input
            type="text"
            name="inscricao_municipal"
            id="inscricao_municipal"
            class="{{ $classes[3] }} text-uppercase form-control {{ $errors->has('inscricao_municipal') ? 'is-invalid' : '' }}"
            placeholder=""
            value="{{ empty(old('inscricao_municipal')) && isset($resultado->pessoaJuridica->inscricao_municipal) ? $resultado->pessoaJuridica->inscricao_municipal : old('inscricao_municipal') }}"
            minlength="5"
            maxlength="30"
        />
        @if($errors->has('inscricao_municipal'))
        <div class="invalid-feedback">
            {{ $errors->first('inscricao_municipal') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="inscricao_estadual">{{ $codigos[1]['inscricao_estadual'] }} - Inscrição Estadual</label>
        <input
            type="text"
            name="inscricao_estadual"
            id="inscricao_estadual"
            class="{{ $classes[3] }} text-uppercase form-control {{ $errors->has('inscricao_estadual') ? 'is-invalid' : '' }}"
            placeholder=""
            value="{{ empty(old('inscricao_estadual')) && isset($resultado->pessoaJuridica->inscricao_estadual) ? $resultado->pessoaJuridica->inscricao_estadual : old('inscricao_estadual') }}"
            minlength="5"
            maxlength="30"
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
        <label for="segmento">{{ $codigos[1]['segmento'] }} - Segmento</label>
        <select 
            name="segmento" 
            id="segmento"
            class="{{ $classes[4] }} form-control {{ $errors->has('segmento') || isset($justificativas) ? 'is-invalid' : '' }}" 
        >
            <option value="">Selecione a opção...</option>
        @foreach(segmentos() as $segmento)
            @if(!empty(old('segmento')))
            <option value="{{ $segmento }}" {{ old('segmento') == $segmento ? 'selected' : '' }}>{{ $segmento }}</option>
            @elseif(isset($resultado->segmento))
            <option value="{{ $segmento }}" {{ mb_strtoupper($segmento, 'UTF-8') == $resultado->segmento ? 'selected' : '' }}>{{ $segmento }}</option>
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
        <label for="idregional_pr">{{ $codigos[1]['idregional'] }} - Região de Atuação <span class="text-danger">*</span></label>
        <select 
            name="idregional" 
            id="idregional_pr"
            class="{{ $classes[4] }} form-control {{ $errors->has('idregional') ? 'is-invalid' : '' }} obrigatorio" 
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

{{-- Deixar sempre no final do form a pergunta para não afetar a ordenação para o atendente no admin --}}
<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="pergunta">{{ $codigos[1]['pergunta'] }} - Quanto tempo possui de experiência no ramo de vendas?</label>
        <input
            type="text"
            name="pergunta"
            id="pergunta"
            class="{{ $classes[4] }} text-uppercase form-control {{ $errors->has('pergunta') ? 'is-invalid' : '' }}"
            placeholder=""
            minlength="1"
            maxlength="191"
            {{ $resultado->status != $resultado::STATUS_CRIADO ? 'readonly' : '' }}
            {{-- por não salvar no bd, para não passar nula para o próximo request de envio --}}
            @if($resultado->status != $resultado::STATUS_CRIADO)
            value="**********"
            @else
            value="{{ empty(old('pergunta')) ? request()->pergunta : old('pergunta') }}"
            @endif
        />
        @if($errors->has('pergunta'))
        <div class="invalid-feedback">
            {{ $errors->first('pergunta') }}
        </div>
        @endif
    </div>
</div>