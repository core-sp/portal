@component('components.justificativa_pre_registro', [
    'resultado' => $resultado,
    'correcoes' => $resultado->getCodigosJustificadosByAba($nome_campos)
])
@endcomponent

<small class="text-muted text-left">
    <em>
        <span class="font-weight-bolder">Atenção!</span> Estes dados são relacionados exclusivamente ao Representante Comercial
    </em>
</small>

<div class="form-row mb-2 mt-2">
    <div class="col-lg mb-2-576">
        <label for="cpf_rt">{{ $nome_campos['cpf_rt'] }} - CPF <span class="text-danger">*</span></label>
        <small class="text-muted text-left ml-2">
            <em>
                Após inserir um CPF válido aguarde {{ $resultado->pessoaJuridica::TOTAL_HIST_DIAS_UPDATE * 24 }}h para trocar
            </em>
        </small>
        <input
            type="text"
            id="cpf_rt"
            class="{{ $classe }} form-control cpfInput {{ $errors->has('cpf_rt') ? ' is-invalid' : '' }} obrigatorio"
            name="cpf_rt"
            value="{{ empty(old('cpf_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->cpf) ? $resultado->pessoaJuridica->responsavelTecnico->cpf : old('cpf_rt') }}"
            placeholder="999.999.999-99"
        />
        @if($errors->has('cpf_rt'))
        <div class="invalid-feedback">
            {{ $errors->first('cpf_rt') }}
        </div>
        @endif
    </div>

    <div class="col-lg mb-2-576">
        <label for="registro_preRegistro">{{ $nome_campos['registro'] }} - Registro</label>
        <input
            type="text"
            class="{{ $classe }} form-control registro_core_format"
            id="registro_preRegistro"
            value="{{ isset($resultado->pessoaJuridica->responsavelTecnico->registro) ? $resultado->pessoaJuridica->responsavelTecnico->registro : '' }}"
            disabled
            readonly
        />
    </div>
</div>

<fieldset id="campos_rt" {{ isset($resultado->pessoaJuridica->responsavelTecnico->cpf) ? '' : 'disabled' }}>
    <div class="form-row mb-2">
        <div class="col-sm mb-2-576">
            <label for="nome_rt">{{ $nome_campos['nome_rt'] }} - Nome Completo <span class="text-danger">*</span></label>
            <input
                name="nome_rt"
                id="nome_rt"
                type="text"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('nome_rt') ? 'is-invalid' : '' }} obrigatorio"
                value="{{ empty(old('nome_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->nome) ? $resultado->pessoaJuridica->responsavelTecnico->nome : old('nome_rt') }}"
                maxlength="191"
            />
            @if($errors->has('nome_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('nome_rt') }}
            </div>
            @endif
        </div>
    </div>

    <div class="form-row mb-2">
        <div class="col-sm mb-2-576">
            <label for="nome_social_rt">{{ $nome_campos['nome_social_rt'] }} - Nome Social</label>
            <input
                name="nome_social_rt"
                id="nome_social_rt"
                type="text"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('nome_social_rt') ? 'is-invalid' : '' }}"
                value="{{ empty(old('nome_social_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->nome_social) ? $resultado->pessoaJuridica->responsavelTecnico->nome_social : old('nome_social_rt') }}"
                maxlength="191"
            />
            @if($errors->has('nome_social_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('nome_social_rt') }}
            </div>
            @endif
        </div>
    </div>

    <div class="form-row mb-2">
        <div class="col-sm mb-2-576">
            <label for="dt_nascimento_rt">{{ $nome_campos['dt_nascimento_rt'] }} - Data de Nascimento <span class="text-danger">*</span></label>
            <input
                name="dt_nascimento_rt"
                id="dt_nascimento_rt"
                type="date"
                class="{{ $classe }} form-control {{ $errors->has('dt_nascimento_rt') ? 'is-invalid' : '' }} obrigatorio"
                value="{{ empty(old('dt_nascimento_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->dt_nascimento) ? $resultado->pessoaJuridica->responsavelTecnico->dt_nascimento : old('dt_nascimento_rt') }}"
                max="{{ Carbon\Carbon::today()->subYears(18)->format('Y-m-d') }}"
            />
            @if($errors->has('dt_nascimento_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('dt_nascimento_rt') }}
            </div>
            @endif
        </div>
        <div class="col-sm mb-2-576">
            <label for="sexo_rt">{{ $nome_campos['sexo_rt'] }} - Gênero <span class="text-danger">*</span></label><br>
            <select 
                name="sexo_rt" 
                id="sexo_rt"
                class="{{ $classe }} form-control {{ $errors->has('sexo_rt') ? 'is-invalid' : '' }} obrigatorio"
            >
                <option value="">Selecione a opção...</option>
            @foreach(generos() as $key => $genero)
                @if(!empty(old('sexo_rt')))
                <option value="{{ $key }}" {{ old('sexo_rt') == $key ? 'selected' : '' }}>{{ $genero }}</option>
                @elseif(isset($resultado->pessoaJuridica->responsavelTecnico->sexo))
                <option value="{{ $key }}" {{ $key == $resultado->pessoaJuridica->responsavelTecnico->sexo ? 'selected' : '' }}>{{ $genero }}</option>
                @else
                <option value="{{ $key }}">{{ $genero }}</option>
                @endif
            @endforeach
            </select>
            @if($errors->has('sexo_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('sexo_rt') }}
            </div>
            @endif
        </div>
    </div>

    <div class="form-row mb-2">
        <div class="col-md mb-2-576">
            <label for="tipo_identidade_rt">{{ $nome_campos['tipo_identidade_rt'] }} - Tipo do documento de identidade <span class="text-danger">*</span></label><br>
            <select 
                name="tipo_identidade_rt" 
                id="tipo_identidade_rt"
                class="{{ $classe }} form-control {{ $errors->has('tipo_identidade_rt') ? 'is-invalid' : '' }} obrigatorio"
            >
                <option value="">Selecione a opção...</option>
            @foreach(tipos_identidade() as $tipo)
                @if(!empty(old('tipo_identidade_rt')))
                <option value="{{ $tipo }}" {{ old('tipo_identidade_rt') == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                @elseif(isset($resultado->pessoaJuridica->responsavelTecnico->tipo_identidade))
                <option value="{{ $tipo }}" {{ mb_strtoupper($tipo, 'UTF-8') == $resultado->pessoaJuridica->responsavelTecnico->tipo_identidade ? 'selected' : '' }}>{{ $tipo }}</option>
                @else
                <option value="{{ $tipo }}">{{ $tipo }}</option>
                @endif
            @endforeach
            </select>
            @if($errors->has('tipo_identidade_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('tipo_identidade_rt') }}
            </div>
            @endif
        </div>
        <div class="col-md mb-2-576">
            <label>{{ $nome_campos['identidade_rt'] }} - </label>
            <label for="identidade_rt">N° do documento de identidade <span class="text-danger">*</span></label>
            <input
                name="identidade_rt"
                type="text"
                id="identidade_rt"
                class="{{ $classe }} form-control text-uppercase {{ $errors->has('identidade_rt') ? 'is-invalid' : '' }} obrigatorio"
                value="{{ empty(old('identidade_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->identidade) ? $resultado->pessoaJuridica->responsavelTecnico->identidade : old('identidade_rt') }}"
                maxlength="30"
            />
            @if($errors->has('identidade_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('identidade_rt') }}
            </div>
            @endif
        </div>
    </div>

    <div class="form-row mb-2">
        <div class="col-sm mb-2-576">
            <label for="orgao_emissor_rt">{{ $nome_campos['orgao_emissor_rt'] }} - Órgão Emissor <span class="text-danger">*</span></label>
            <input
                name="orgao_emissor_rt"
                id="orgao_emissor_rt"
                type="text"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('orgao_emissor_rt') ? 'is-invalid' : '' }} obrigatorio"
                value="{{ empty(old('orgao_emissor_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->orgao_emissor) ? $resultado->pessoaJuridica->responsavelTecnico->orgao_emissor : old('orgao_emissor_rt') }}"
                maxlength="191"
            />
            @if($errors->has('orgao_emissor_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('orgao_emissor_rt') }}
            </div>
            @endif
        </div>
        <div class="col-sm mb-2-576">
            <label for="dt_expedicao_rt">{{ $nome_campos['dt_expedicao_rt'] }} - Data de Expedição <span class="text-danger">*</span></label>
            <input
                name="dt_expedicao_rt"
                id="dt_expedicao_rt"
                type="date"
                class="{{ $classe }} form-control {{ $errors->has('dt_expedicao_rt') ? 'is-invalid' : '' }} obrigatorio"
                value="{{ empty(old('dt_expedicao_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->dt_expedicao) ? $resultado->pessoaJuridica->responsavelTecnico->dt_expedicao : old('dt_expedicao_rt') }}"
                max="{{ date('Y-m-d') }}"
            />
            @if($errors->has('dt_expedicao_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('dt_expedicao_rt') }}
            </div>
            @endif
        </div>
    </div>

    <div class="linha-lg-mini"></div>

    <div class="form-row mb-2">
        <div class="col-sm mb-2-576">
            <label for="titulo_eleitor_rt">{{ $nome_campos['titulo_eleitor_rt'] }} - Título de Eleitor <span class="text-danger">*</span></label>
            <input
                name="titulo_eleitor_rt"
                id="titulo_eleitor_rt"
                type="text"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('titulo_eleitor_rt') ? 'is-invalid' : '' }} obrigatorio"
                value="{{ empty(old('titulo_eleitor_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->titulo_eleitor) ? $resultado->pessoaJuridica->responsavelTecnico->titulo_eleitor : old('titulo_eleitor_rt') }}"
                maxlength="15"
            />
            @if($errors->has('titulo_eleitor_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('titulo_eleitor_rt') }}
            </div>
            @endif
        </div>
        <div class="col-sm mb-2-576">
            <label for="zona_rt">{{ $nome_campos['zona_rt'] }} - Zona Eleitoral <span class="text-danger">*</span></label>
            <input
                name="zona_rt"
                id="zona_rt"
                type="text"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('zona_rt') ? 'is-invalid' : '' }} obrigatorio"
                value="{{ empty(old('zona_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->zona) ? $resultado->pessoaJuridica->responsavelTecnico->zona : old('zona_rt') }}"
                maxlength="6"
            />
            @if($errors->has('zona_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('zona_rt') }}
            </div>
            @endif
        </div>
    </div>

    <div class="form-row mb-2">
        <div class="col-sm mb-2-576">
            <label for="secao_rt">{{ $nome_campos['secao_rt'] }} - Seção Eleitoral <span class="text-danger">*</span></label>
            <input
                name="secao_rt"
                id="secao_rt"
                type="text"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('secao_rt') ? 'is-invalid' : '' }} obrigatorio"
                value="{{ empty(old('secao_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->secao) ? $resultado->pessoaJuridica->responsavelTecnico->secao : old('secao_rt') }}"
                maxlength="8"
            />
            @if($errors->has('secao_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('secao_rt') }}
            </div>
            @endif
        </div>
        <div class="col-sm mb-2-576">
            <label for="ra_reservista_rt">{{ $nome_campos['ra_reservista_rt'] }} - RA Reservista <span class="text-danger">*</span> 
            <small><em>(obrigatório para gênero Masculino até 45 anos)</em></small></label>
            <input
                name="ra_reservista_rt"
                id="ra_reservista_rt"
                type="text"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('ra_reservista_rt') ? 'is-invalid' : '' }} obrigatorio"
                value="{{ empty(old('ra_reservista_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->ra_reservista) ? $resultado->pessoaJuridica->responsavelTecnico->ra_reservista : old('ra_reservista_rt') }}"
                maxlength="15"
            />
            @if($errors->has('ra_reservista_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('ra_reservista_rt') }}
            </div>
            @endif
        </div>
    </div>

    <div class="linha-lg-mini mt-3 mb-3"></div>

    <div class="form-row mb-2">
        <div class="col-sm-4 mb-2-576">
            <label for="cep_rt">{{ $nome_campos['cep_rt'] }} - CEP <span class="text-danger">*</span></label>
            <input
                type="text"
                name="cep_rt"
                class="{{ $classe }} form-control cep {{ $errors->has('cep_rt') ? 'is-invalid' : '' }} obrigatorio"
                id="cep_rt"
                value="{{ empty(old('cep_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->cep) ? $resultado->pessoaJuridica->responsavelTecnico->cep : old('cep_rt') }}"
            />
            @if($errors->has('cep_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('cep_rt') }}
            </div>
            @endif
        </div>
        <div class="col-sm mb-2-576">
            <label for="bairro_rt">{{ $nome_campos['bairro_rt'] }} - Bairro <span class="text-danger">*</span></label>
            <input
                type="text"
                name="bairro_rt"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('bairro_rt') ? 'is-invalid' : '' }} obrigatorio"
                id="bairro_rt"
                value="{{ empty(old('bairro_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->bairro) ? $resultado->pessoaJuridica->responsavelTecnico->bairro : old('bairro_rt') }}"
                maxlength="191"
            />
            @if($errors->has('bairro_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('bairro_rt') }}
            </div>
            @endif
        </div>
    </div>

    <div class="form-row mb-2">
        <div class="col-md col-lg mb-2-576">
            <label for="rua_rt">{{ $nome_campos['logradouro_rt'] }} - Logradouro <span class="text-danger">*</span></label>
            <input
                type="text"
                name="logradouro_rt"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('logradouro_rt') ? 'is-invalid' : '' }} obrigatorio"
                id="rua_rt"
                value="{{ empty(old('logradouro_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->logradouro) ? $resultado->pessoaJuridica->responsavelTecnico->logradouro : old('logradouro_rt') }}"
                maxlength="191"
            />
            @if($errors->has('logradouro_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('logradouro_rt') }}
            </div>
            @endif
        </div>
        <div class="col-md-3 col-lg-2 mb-2-576">
            <label for="numero_rt">{{ $nome_campos['numero_rt'] }} - Número <span class="text-danger">*</span></label>
            <input
                type="text"
                name="numero_rt"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('numero_rt') ? 'is-invalid' : '' }} obrigatorio"
                id="numero_rt"
                value="{{ empty(old('numero_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->numero) ? $resultado->pessoaJuridica->responsavelTecnico->numero : old('numero_rt') }}"
                maxlength="10"
            />
            @if($errors->has('numero_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('numero_rt') }}
            </div>
            @endif
        </div>
    </div>

    <div class="form-row mb-2">
        <div class="col-md-3 col-lg-3 col-xl-3 mb-2-576">
            <label for="complemento_rt">{{ $nome_campos['complemento_rt'] }} - Complemento</label>
            <input
                type="text"
                name="complemento_rt"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('complemento_rt') ? 'is-invalid' : '' }}"
                id="complemento_rt"
                value="{{ empty(old('complemento_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->complemento) ? $resultado->pessoaJuridica->responsavelTecnico->complemento : old('complemento_rt') }}"
                maxlength="50"
            />
            @if($errors->has('complemento_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('complemento_rt') }}
            </div>
            @endif
        </div>
        <div class="col-md col-lg-5 col-xl-5 mb-2-576">
            <label for="cidade_rt">{{ $nome_campos['cidade_rt'] }} - Município <span class="text-danger">*</span></label>
            <input
                type="text"
                name="cidade_rt"
                id="cidade_rt"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('cidade_rt') ? 'is-invalid' : '' }} obrigatorio"
                value="{{ empty(old('cidade_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->cidade) ? $resultado->pessoaJuridica->responsavelTecnico->cidade : old('cidade_rt') }}"
                maxlength="191"
            />
            @if($errors->has('cidade_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('cidade_rt') }}
            </div>
            @endif
        </div>
        <div class="col-lg-4 col-xl-4 mb-2-576">
            <label for="uf_rt">{{ $nome_campos['uf_rt'] }} - Estado <span class="text-danger">*</span></label>
            <select 
                name="uf_rt" 
                id="uf_rt" 
                class="{{ $classe }} form-control {{ $errors->has('uf_rt') ? 'is-invalid' : '' }} obrigatorio"
            >
                <option value="">Selecione a opção...</option>
            @foreach(estados() as $key => $estado)
                @if(!empty(old('uf_rt')))
                <option value="{{ $key }}" {{ old('uf_rt') == $key ? 'selected' : '' }}>{{ $estado }}</option>
                @elseif(isset($resultado->pessoaJuridica->responsavelTecnico->uf))
                <option value="{{ $key }}" {{ $key == $resultado->pessoaJuridica->responsavelTecnico->uf ? 'selected' : '' }}>{{ $estado }}</option>
                @else
                <option value="{{ $key }}">{{ $estado }}</option>
                @endif
            @endforeach
            </select>
            @if($errors->has('uf_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('uf_rt') }}
            </div>
            @endif
        </div>
    </div>

    <div class="linha-lg-mini mt-3 mb-3"></div>

    <div class="form-row mb-2">
        <div class="col-lg mb-2-576">
            <label for="nome_mae_rt">{{ $nome_campos['nome_mae_rt'] }} - Nome da Mãe <span class="text-danger">*</span></label>
            <input
                name="nome_mae_rt"
                id="nome_mae_rt"
                type="text"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('nome_mae_rt') ? 'is-invalid' : '' }} obrigatorio"
                value="{{ empty(old('nome_mae_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->nome_mae) ? $resultado->pessoaJuridica->responsavelTecnico->nome_mae : old('nome_mae_rt') }}"
                maxlength="191"
            />
            @if($errors->has('nome_mae_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('nome_mae_rt') }}
            </div>
            @endif
        </div>
        <div class="col-lg mb-2-576">
            <label for="nome_pai_rt">{{ $nome_campos['nome_pai_rt'] }} - Nome do Pai</label>
            <input
                name="nome_pai_rt"
                id="nome_pai_rt"
                type="text"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('nome_pai_rt') ? 'is-invalid' : '' }}"
                value="{{ empty(old('nome_pai_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->nome_pai) ? $resultado->pessoaJuridica->responsavelTecnico->nome_pai : old('nome_pai_rt') }}"
                maxlength="191"
            />
            @if($errors->has('nome_pai_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('nome_pai_rt') }}
            </div>
            @endif
        </div>
    </div>
</fieldset>