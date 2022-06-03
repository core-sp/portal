<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="cpf_rt">{{ array_search('cpf', $codRT) }} - CPF *</label>
        <input
            type="text"
            class="{{ $classes[5] }} {{ array_search('cpf', $codRT) }} form-control cpfInput {{ $errors->has('cpf_rt') ? ' is-invalid' : '' }}"
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

    <div class="col-sm mb-2-576">
        <label for="registro">{{ array_search('registro', $codRT) }} - Registro</label>
        <input
            id="registro_core"
            name="registro"
            type="text"
            class="{{ $classes[5] }} {{ array_search('registro', $codRT) }} form-control {{ $errors->has('registro') ? 'is-invalid' : '' }}"
            value="{{ empty(old('registro')) && isset($resultado->pessoaJuridica->responsavelTecnico->registro) ? $resultado->pessoaJuridica->responsavelTecnico->registro : old('registro') }}"
            {{ isset($resultado->pessoaJuridica->responsavelTecnico->cpf) ? '' : 'disabled' }}
        />
        @if($errors->has('registro'))
        <div class="invalid-feedback">
            {{ $errors->first('registro') }}
        </div>
        @endif
    </div>
</div>

<fieldset id="campos_rt" {{ isset($resultado->pessoaJuridica->responsavelTecnico->cpf) ? '' : 'disabled' }}>
    <div class="form-row mb-2">
        <div class="col-sm mb-2-576">
            <label for="nome_rt">{{ array_search('nome', $codRT) }} - Nome Completo *</label>
            <input
                name="nome_rt"
                type="text"
                class="{{ $classes[5] }} {{ array_search('nome', $codRT) }} form-control {{ $errors->has('nome_rt') ? 'is-invalid' : '' }}"
                value="{{ empty(old('nome_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->nome) ? $resultado->pessoaJuridica->responsavelTecnico->nome : old('nome_rt') }}"
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
            <label for="nome_social_rt">{{ array_search('nome_social', $codRT) }} - Nome Social</label>
            <input
                name="nome_social_rt"
                type="text"
                class="{{ $classes[5] }} {{ array_search('nome_social', $codRT) }} form-control {{ $errors->has('nome_social_rt') ? 'is-invalid' : '' }}"
                value="{{ empty(old('nome_social_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->nome_social) ? $resultado->pessoaJuridica->responsavelTecnico->nome_social : old('nome_social_rt') }}"
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
            <label for="dt_nascimento_rt">{{ array_search('dt_nascimento', $codRT) }} - Data de Nascimento *</label>
            <input
                name="dt_nascimento_rt"
                type="date"
                class="{{ $classes[5] }} {{ array_search('dt_nascimento', $codRT) }} form-control {{ $errors->has('dt_nascimento_rt') ? 'is-invalid' : '' }}"
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
            <label for="sexo_rt">{{ array_search('sexo', $codRT) }} - Sexo *</label><br>
            <div class="form-check-inline">
                <label class="form-check-label">
                    <input type="radio" 
                        class="{{ $classes[5] }} {{ array_search('sexo', $codRT) }} form-check-input {{ $errors->has('sexo_rt') ? 'is-invalid' : '' }}" 
                        name="sexo_rt" 
                        value="F" 
                        {{ (!empty(old('sexo_rt')) && (old('sexo_rt') == 'F')) || (isset($resultado->pessoaJuridica->responsavelTecnico->sexo) && ($resultado->pessoaJuridica->responsavelTecnico->sexo == 'F')) ? 'checked' : '' }}
                    />
                    Feminino
                    
                    @if($errors->has('sexo_rt'))
                    <div class="invalid-feedback">
                        {{ $errors->first('sexo_rt') }}
                    </div>
                    @endif
                </label>
            </div>
            <div class="form-check-inline">
                <label class="form-check-label">
                    <input type="radio" 
                        class="{{ $classes[5] }} {{ array_search('sexo', $codRT) }} form-check-input {{ $errors->has('sexo_rt') ? 'is-invalid' : '' }}" 
                        name="sexo_rt" 
                        value="M" 
                        {{ (!empty(old('sexo_rt')) && (old('sexo_rt') == 'M')) || (isset($resultado->pessoaJuridica->responsavelTecnico->sexo) && ($resultado->pessoaJuridica->responsavelTecnico->sexo == 'M')) ? 'checked' : '' }}
                    />
                    Masculino
                </label>
            </div>
        </div>
    </div>

    <div class="form-row mb-2">
        <div class="col-sm mb-2-576">
            <label for="identidade_rt">{{ array_search('identidade', $codRT) }} - N° RG *</label>
            <input
                name="identidade_rt"
                type="text"
                id="rg"
                class="{{ $classes[5] }} {{ array_search('identidade', $codRT) }} form-control rgInput {{ $errors->has('identidade_rt') ? 'is-invalid' : '' }}"
                value="{{ empty(old('identidade_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->identidade) ? $resultado->pessoaJuridica->responsavelTecnico->identidade : old('identidade_rt') }}"
                maxlength="20"
            />
            @if($errors->has('identidade_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('identidade_rt') }}
            </div>
            @endif
        </div>
        <div class="col-sm mb-2-576">
            <label for="orgao_emissor_rt">{{ array_search('orgao_emissor', $codRT) }} - Órgão Emissor *</label>
            <input
                name="orgao_emissor_rt"
                type="text"
                class="{{ $classes[5] }} {{ array_search('orgao_emissor', $codRT) }} form-control {{ $errors->has('orgao_emissor_rt') ? 'is-invalid' : '' }}"
                value="{{ empty(old('orgao_emissor_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->orgao_emissor) ? $resultado->pessoaJuridica->responsavelTecnico->orgao_emissor : old('orgao_emissor_rt') }}"
            />
            @if($errors->has('orgao_emissor_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('orgao_emissor_rt') }}
            </div>
            @endif
        </div>
        <div class="col-sm mb-2-576">
            <label for="dt_expedicao_rt">{{ array_search('dt_expedicao', $codRT) }} - Data de Expedição *</label>
            <input
                name="dt_expedicao_rt"
                type="date"
                class="{{ $classes[5] }} {{ array_search('dt_expedicao', $codRT) }} form-control {{ $errors->has('dt_expedicao_rt') ? 'is-invalid' : '' }}"
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

    <div class="linha-lg-mini mt-3 mb-3"></div>

    <div class="form-row mb-2">
        <div class="col-sm-4 mb-2-576">
            <label for="cep_rt">{{ array_search('cep', $codRT) }} - CEP *</label>
            <input
                type="text"
                name="cep_rt"
                class="{{ $classes[5] }} {{ array_search('cep', $codRT) }} form-control cep {{ $errors->has('cep_rt') ? 'is-invalid' : '' }}"
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
            <label for="bairro_rt">{{ array_search('bairro', $codRT) }} - Bairro *</label>
            <input
                type="text"
                name="bairro_rt"
                class="{{ $classes[5] }} {{ array_search('bairro', $codRT) }} form-control {{ $errors->has('bairro_rt') ? 'is-invalid' : '' }}"
                id="bairro_rt"
                value="{{ empty(old('bairro_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->bairro) ? $resultado->pessoaJuridica->responsavelTecnico->bairro : old('bairro_rt') }}"
            />
            @if($errors->has('bairro_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('bairro_rt') }}
            </div>
            @endif
        </div>
    </div>

    <div class="form-row mb-2">
        <div class="col-sm mb-2-576">
            <label for="logradouro_rt">{{ array_search('logradouro', $codRT) }} - Logradouro *</label>
            <input
                type="text"
                name="logradouro_rt"
                class="{{ $classes[5] }} {{ array_search('logradouro', $codRT) }} form-control {{ $errors->has('logradouro_rt') ? 'is-invalid' : '' }}"
                id="rua_rt"
                value="{{ empty(old('logradouro_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->logradouro) ? $resultado->pessoaJuridica->responsavelTecnico->logradouro : old('logradouro_rt') }}"
            />
            @if($errors->has('logradouro_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('logradouro_rt') }}
            </div>
            @endif
        </div>
        <div class="col-sm-2 mb-2-576">
            <label for="numero_rt">{{ array_search('numero', $codRT) }} - Número *</label>
            <input
                type="text"
                name="numero_rt"
                class="{{ $classes[5] }} {{ array_search('numero', $codRT) }} form-control {{ $errors->has('numero_rt') ? 'is-invalid' : '' }}"
                id="numero_rt"
                value="{{ empty(old('numero_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->numero) ? $resultado->pessoaJuridica->responsavelTecnico->numero : old('numero_rt') }}"
            />
            @if($errors->has('numero_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('numero_rt') }}
            </div>
            @endif
        </div>
    </div>

    <div class="form-row mb-2">
        <div class="col-sm-3 mb-2-576">
            <label for="complemento_rt">{{ array_search('complemento', $codRT) }} - Complemento</label>
            <input
                type="text"
                name="complemento_rt"
                class="{{ $classes[5] }} {{ array_search('complemento', $codRT) }} form-control {{ $errors->has('complemento_rt') ? 'is-invalid' : '' }}"
                id="complemento_rt"
                value="{{ empty(old('complemento_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->complemento) ? $resultado->pessoaJuridica->responsavelTecnico->complemento : old('complemento_rt') }}"
            />
            @if($errors->has('complemento_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('complemento_rt') }}
            </div>
            @endif
        </div>
        <div class="col-sm-5 mb-2-576">
            <label for="cidade_rt">{{ array_search('cidade', $codRT) }} - Município *</label>
            <input
                type="text"
                name="cidade_rt"
                id="cidade_rt"
                class="{{ $classes[5] }} {{ array_search('cidade', $codRT) }} form-control {{ $errors->has('cidade_rt') ? 'is-invalid' : '' }}"
                value="{{ empty(old('cidade_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->cidade) ? $resultado->pessoaJuridica->responsavelTecnico->cidade : old('cidade_rt') }}"
            />
            @if($errors->has('cidade_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('cidade_rt') }}
            </div>
            @endif
        </div>
        <div class="col-sm-4 mb-2-576">
            <label for="uf_rt">{{ array_search('uf', $codRT) }} - Estado *</label>
            <select 
                name="uf_rt" 
                id="uf_rt" 
                class="{{ $classes[5] }} {{ array_search('uf', $codRT) }} form-control {{ $errors->has('uf_rt') ? 'is-invalid' : '' }}"
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
        <div class="col-sm mb-2-576">
            <label for="nome_mae_rt">{{ array_search('nome_mae', $codRT) }} - Nome da Mãe *</label>
            <input
                name="nome_mae_rt"
                type="text"
                class="{{ $classes[5] }} {{ array_search('nome_mae', $codRT) }} form-control {{ $errors->has('nome_mae_rt') ? 'is-invalid' : '' }}"
                value="{{ empty(old('nome_mae_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->nome_mae) ? $resultado->pessoaJuridica->responsavelTecnico->nome_mae : old('nome_mae_rt') }}"
            />
            @if($errors->has('nome_mae_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('nome_mae_rt') }}
            </div>
            @endif
        </div>
        <div class="col-sm mb-2-576">
            <label for="nome_pai_rt">{{ array_search('nome_pai', $codRT) }} - Nome do Pai</label>
            <input
                name="nome_pai_rt"
                type="text"
                class="{{ $classes[5] }} {{ array_search('nome_pai', $codRT) }} form-control {{ $errors->has('nome_pai_rt') ? 'is-invalid' : '' }}"
                value="{{ empty(old('nome_pai_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->nome_pai) ? $resultado->pessoaJuridica->responsavelTecnico->nome_pai : old('nome_pai_rt') }}"
            />
            @if($errors->has('nome_pai_rt'))
            <div class="invalid-feedback">
                {{ $errors->first('nome_pai_rt') }}
            </div>
            @endif
        </div>
    </div>
</fieldset>