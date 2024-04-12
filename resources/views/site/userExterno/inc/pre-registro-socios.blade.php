@component('components.justificativa_pre_registro', [
    'resultado' => $resultado,
    'correcoes' => $resultado->getCodigosJustificadosByAba($nome_campos)
])
@endcomponent

<small class="text-muted text-left">
    <em>Após inserir um CPF / CNPJ válido aguarde 24h para trocar</em>
</small>

<div class="form-row mb-2 mt-2">
    <div class="col-lg mb-2-576">
        <input type="hidden" name="id_socio" value="1">
        <label for="cpf_cnpj_socio">{{ $nome_campos['cpf_cnpj_socio'] }} - CPF / CNPJ <span class="text-danger">*</span></label>
        <input
            type="text"
            id="cpf_cnpj_socio"
            class="{{ $classe }} form-control cpfOuCnpj {{ $errors->has('cpf_cnpj_socio') ? ' is-invalid' : '' }} obrigatorio"
            name="cpf_cnpj_socio"
            value="{{-- empty(old('cpf_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->cpf) ? $resultado->pessoaJuridica->responsavelTecnico->cpf : old('cpf_rt') --}}"
        />
        @if($errors->has('cpf_cnpj_socio'))
        <div class="invalid-feedback">
            {{ $errors->first('cpf_cnpj_socio') }}
        </div>
        @endif
    </div>

    <div class="col-lg mb-2-576">
        <label for="registro_socio">{{ $nome_campos['registro_socio'] }} - Registro</label>
        <input
            type="text"
            class="{{ $classe }} form-control"
            id="registro_socio"
            value="{{-- isset($resultado->pessoaJuridica->responsavelTecnico->registro) ? $resultado->pessoaJuridica->responsavelTecnico->registro : '' --}}"
            disabled
            readonly
        />
    </div>
</div>

<fieldset id="campos_socio" {{-- isset($resultado->pessoaJuridica->responsavelTecnico->cpf) ? '' : 'disabled' --}}>
    <div class="form-row mb-2">
        <div class="col-sm mb-2-576">
            <label for="nome_socio">{{ $nome_campos['nome_socio'] }} - Nome Completo <span class="text-danger">*</span></label>
            <input
                name="nome_socio"
                id="nome_socio"
                type="text"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('nome_socio') ? 'is-invalid' : '' }} obrigatorio"
                value="{{-- empty(old('nome_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->nome) ? $resultado->pessoaJuridica->responsavelTecnico->nome : old('nome_rt') --}}"
                maxlength="191"
            />
            @if($errors->has('nome_socio'))
            <div class="invalid-feedback">
                {{ $errors->first('nome_socio') }}
            </div>
            @endif
        </div>
    </div>

    <div class="form-row mb-2">
        <div class="col-sm mb-2-576">
            <label for="nome_social_socio">{{ $nome_campos['nome_social_socio'] }} - Nome Social</label>
            <input
                name="nome_social_socio"
                id="nome_social_socio"
                type="text"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('nome_social_socio') ? 'is-invalid' : '' }}"
                value="{{-- empty(old('nome_social_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->nome_social) ? $resultado->pessoaJuridica->responsavelTecnico->nome_social : old('nome_social_rt') --}}"
                maxlength="191"
            />
            @if($errors->has('nome_social_socio'))
            <div class="invalid-feedback">
                {{ $errors->first('nome_social_socio') }}
            </div>
            @endif
        </div>
    </div>

    <div class="form-row mb-2">
        <div class="col-sm mb-2-576">
            <label for="dt_nascimento_socio">{{ $nome_campos['dt_nascimento_socio'] }} - Data de Nascimento <span class="text-danger">*</span></label>
            <input
                name="dt_nascimento_socio"
                id="dt_nascimento_socio"
                type="date"
                class="{{ $classe }} form-control {{ $errors->has('dt_nascimento_socio') ? 'is-invalid' : '' }} obrigatorio"
                value="{{-- empty(old('dt_nascimento_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->dt_nascimento) ? $resultado->pessoaJuridica->responsavelTecnico->dt_nascimento : old('dt_nascimento_rt') --}}"
                max="{{ Carbon\Carbon::today()->subYears(18)->format('Y-m-d') }}"
            />
            @if($errors->has('dt_nascimento_socio'))
            <div class="invalid-feedback">
                {{ $errors->first('dt_nascimento_socio') }}
            </div>
            @endif
        </div>

        <div class="col-md mb-2-576">
            <label>{{ $nome_campos['identidade_socio'] }} - </label>
            <label for="identidade_socio">N° do documento de identidade <span class="text-danger">*</span></label>
            <input
                name="identidade_socio"
                type="text"
                id="identidade_socio"
                class="{{ $classe }} form-control text-uppercase {{ $errors->has('identidade_socio') ? 'is-invalid' : '' }} obrigatorio"
                value="{{-- empty(old('identidade_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->identidade) ? $resultado->pessoaJuridica->responsavelTecnico->identidade : old('identidade_rt') --}}"
                maxlength="30"
            />
            @if($errors->has('identidade_socio'))
            <div class="invalid-feedback">
                {{ $errors->first('identidade_socio') }}
            </div>
            @endif
        </div>

        <div class="col-sm mb-2-576">
            <label for="orgao_emissor_socio">{{ $nome_campos['orgao_emissor_socio'] }} - Órgão Emissor <span class="text-danger">*</span></label>
            <input
                name="orgao_emissor_socio"
                id="orgao_emissor_socio"
                type="text"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('orgao_emissor_socio') ? 'is-invalid' : '' }} obrigatorio"
                value="{{-- empty(old('orgao_emissor_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->orgao_emissor) ? $resultado->pessoaJuridica->responsavelTecnico->orgao_emissor : old('orgao_emissor_rt') --}}"
                maxlength="191"
            />
            @if($errors->has('orgao_emissor_socio'))
            <div class="invalid-feedback">
                {{ $errors->first('orgao_emissor_socio') }}
            </div>
            @endif
        </div>
    </div>

    <div class="linha-lg-mini mt-3 mb-3"></div>

    <div class="form-row mb-2">
        <div class="col-sm-4 mb-2-576">
            <label for="cep_socio">{{ $nome_campos['cep_socio'] }} - CEP <span class="text-danger">*</span></label>
            <input
                type="text"
                name="cep_socio"
                class="{{ $classe }} form-control cep {{ $errors->has('cep_socio') ? 'is-invalid' : '' }} obrigatorio"
                id="cep_socio"
                value="{{-- empty(old('cep_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->cep) ? $resultado->pessoaJuridica->responsavelTecnico->cep : old('cep_rt') --}}"
            />
            @if($errors->has('cep_socio'))
            <div class="invalid-feedback">
                {{ $errors->first('cep_socio') }}
            </div>
            @endif
        </div>

        <div class="col-sm mb-2-576">
            <label for="bairro_socio">{{ $nome_campos['bairro_socio'] }} - Bairro <span class="text-danger">*</span></label>
            <input
                type="text"
                name="bairro_socio"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('bairro_socio') ? 'is-invalid' : '' }} obrigatorio"
                id="bairro_socio"
                value="{{-- empty(old('bairro_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->bairro) ? $resultado->pessoaJuridica->responsavelTecnico->bairro : old('bairro_rt') --}}"
                maxlength="191"
            />
            @if($errors->has('bairro_socio'))
            <div class="invalid-feedback">
                {{ $errors->first('bairro_socio') }}
            </div>
            @endif
        </div>
    </div>

    <div class="form-row mb-2">
        <div class="col-md col-lg mb-2-576">
            <label for="rua_socio">{{ $nome_campos['logradouro_socio'] }} - Logradouro <span class="text-danger">*</span></label>
            <input
                type="text"
                name="logradouro_socio"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('logradouro_socio') ? 'is-invalid' : '' }} obrigatorio"
                id="rua_socio"
                value="{{-- empty(old('logradouro_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->logradouro) ? $resultado->pessoaJuridica->responsavelTecnico->logradouro : old('logradouro_rt') --}}"
                maxlength="191"
            />
            @if($errors->has('logradouro_socio'))
            <div class="invalid-feedback">
                {{ $errors->first('logradouro_socio') }}
            </div>
            @endif
        </div>

        <div class="col-md-3 col-lg-2 mb-2-576">
            <label for="numero_socio">{{ $nome_campos['numero_socio'] }} - Número <span class="text-danger">*</span></label>
            <input
                type="text"
                name="numero_socio"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('numero_socio') ? 'is-invalid' : '' }} obrigatorio"
                id="numero_socio"
                value="{{-- empty(old('numero_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->numero) ? $resultado->pessoaJuridica->responsavelTecnico->numero : old('numero_rt') --}}"
                maxlength="10"
            />
            @if($errors->has('numero_socio'))
            <div class="invalid-feedback">
                {{ $errors->first('numero_socio') }}
            </div>
            @endif
        </div>
    </div>

    <div class="form-row mb-2">
        <div class="col-md-3 col-lg-3 col-xl-3 mb-2-576">
            <label for="complemento_socio">{{ $nome_campos['complemento_socio'] }} - Complemento</label>
            <input
                type="text"
                name="complemento_socio"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('complemento_socio') ? 'is-invalid' : '' }}"
                id="complemento_socio"
                value="{{-- empty(old('complemento_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->complemento) ? $resultado->pessoaJuridica->responsavelTecnico->complemento : old('complemento_rt') --}}"
                maxlength="50"
            />
            @if($errors->has('complemento_socio'))
            <div class="invalid-feedback">
                {{ $errors->first('complemento_socio') }}
            </div>
            @endif
        </div>

        <div class="col-md col-lg-5 col-xl-5 mb-2-576">
            <label for="cidade_socio">{{ $nome_campos['cidade_socio'] }} - Município <span class="text-danger">*</span></label>
            <input
                type="text"
                name="cidade_socio"
                id="cidade_socio"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('cidade_socio') ? 'is-invalid' : '' }} obrigatorio"
                value="{{-- empty(old('cidade_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->cidade) ? $resultado->pessoaJuridica->responsavelTecnico->cidade : old('cidade_rt') --}}"
                maxlength="191"
            />
            @if($errors->has('cidade_socio'))
            <div class="invalid-feedback">
                {{ $errors->first('cidade_socio') }}
            </div>
            @endif
        </div>

        <div class="col-lg-4 col-xl-4 mb-2-576">
            <label for="uf_socio">{{ $nome_campos['uf_socio'] }} - Estado <span class="text-danger">*</span></label>
            <select 
                name="uf_socio" 
                id="uf_socio" 
                class="{{ $classe }} form-control {{ $errors->has('uf_socio') ? 'is-invalid' : '' }} obrigatorio"
            >
                <option value="">Selecione a opção...</option>
            @foreach(estados() as $key => $estado)
                @if(!empty(old('uf_socio')))
                <option value="{{ $key }}" {{ old('uf_socio') == $key ? 'selected' : '' }}>{{ $estado }}</option>
                @elseif(isset($resultado->pessoaJuridica->responsavelTecnico->uf))
                <option value="{{ $key }}" {{ $key == $resultado->pessoaJuridica->responsavelTecnico->uf ? 'selected' : '' }}>{{ $estado }}</option>
                @else
                <option value="{{ $key }}">{{ $estado }}</option>
                @endif
            @endforeach
            </select>
            @if($errors->has('uf_socio'))
            <div class="invalid-feedback">
                {{ $errors->first('uf_socio') }}
            </div>
            @endif
        </div>
    </div>

    <div class="linha-lg-mini mt-3 mb-3"></div>

    <div class="form-row mb-2">
        <div class="col-lg mb-2-576">
            <label for="nome_mae_socio">{{ $nome_campos['nome_mae_socio'] }} - Nome da Mãe <span class="text-danger">*</span></label>
            <input
                name="nome_mae_socio"
                id="nome_mae_socio"
                type="text"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('nome_mae_socio') ? 'is-invalid' : '' }} obrigatorio"
                value="{{-- empty(old('nome_mae_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->nome_mae) ? $resultado->pessoaJuridica->responsavelTecnico->nome_mae : old('nome_mae_rt') --}}"
                maxlength="191"
            />
            @if($errors->has('nome_mae_socio'))
            <div class="invalid-feedback">
                {{ $errors->first('nome_mae_socio') }}
            </div>
            @endif
        </div>

        <div class="col-lg mb-2-576">
            <label for="nome_pai_socio">{{ $nome_campos['nome_pai_socio'] }} - Nome do Pai <span class="text-danger">*</span></label>
            <input
                name="nome_pai_socio"
                id="nome_pai_socio"
                type="text"
                class="{{ $classe }} text-uppercase form-control {{ $errors->has('nome_pai_socio') ? 'is-invalid' : '' }}"
                value="{{-- empty(old('nome_pai_rt')) && isset($resultado->pessoaJuridica->responsavelTecnico->nome_pai) ? $resultado->pessoaJuridica->responsavelTecnico->nome_pai : old('nome_pai_rt') --}}"
                maxlength="191"
            />
            @if($errors->has('nome_pai_socio'))
            <div class="invalid-feedback">
                {{ $errors->first('nome_pai_socio') }}
            </div>
            @endif
        </div>
    </div>

    <div class="form-row mb-2">
        <div class="col-sm mb-2-576">
            <label for="nacionalidade_socio">{{ $nome_campos['nacionalidade_socio'] }} - Nacionalidade <span class="text-danger">*</span></label>
            <select 
                name="nacionalidade_socio" 
                id="nacionalidade_socio"
                class="{{ $classe }} form-control {{ $errors->has('nacionalidade_socio') ? 'is-invalid' : '' }} obrigatorio" 
            >
                <option value="">Selecione a opção...</option>
            @foreach(nacionalidades() as $nacionalidade)
                @if(!empty(old('nacionalidade_socio')))
                <option value="{{ $nacionalidade }}" {{ old('nacionalidade_socio') == $nacionalidade ? 'selected' : '' }}>{{ $nacionalidade }}</option>
                @elseif(isset($resultado->pessoaFisica->nacionalidade))
                <option value="{{ $nacionalidade }}" {{ mb_strtoupper($nacionalidade, 'UTF-8') == $resultado->pessoaFisica->nacionalidade ? 'selected' : '' }}>{{ $nacionalidade }}</option>
                @else
                <option value="{{ $nacionalidade }}">{{ $nacionalidade }}</option>
                @endif
            @endforeach
            </select>
            @if($errors->has('nacionalidade_socio'))
            <div class="invalid-feedback">
                {{ $errors->first('nacionalidade_socio') }}
            </div>
            @endif
        </div>

        <div class="col-sm mb-2-576">
            <label for="naturalidade_estado_socio">{{ $nome_campos['naturalidade_estado_socio'] }} - Naturalidade - Estado <span class="text-danger">*</span></label>
            <select 
                name="naturalidade_estado_socio" 
                id="naturalidade_estado_socio"
                class="{{ $classe }} form-control {{ $errors->has('naturalidade_estado_socio') ? 'is-invalid' : '' }} obrigatorio" 
                {{ isset($resultado->pessoaFisica->nacionalidade) && ($resultado->pessoaFisica->nacionalidade != 'BRASILEIRA') ? 'disabled' : '' }}
            >
                <option value="">Selecione a opção...</option>
            @foreach(estados() as $key => $naturalidade)
                @if(!empty(old('naturalidade_estado_socio')))
                <option value="{{ $key }}" {{ old('naturalidade_estado_socio') == $key ? 'selected' : '' }}>{{ $naturalidade }}</option>
                @elseif(isset($resultado->pessoaFisica->naturalidade_estado))
                <option value="{{ $key }}" {{ mb_strtoupper($key, 'UTF-8') == $resultado->pessoaFisica->naturalidade_estado ? 'selected' : '' }}>{{ $naturalidade }}</option>
                @else
                <option value="{{ $key }}">{{ $naturalidade }}</option>
                @endif
            @endforeach
            </select>
            @if($errors->has('naturalidade_estado_socio'))
            <div class="invalid-feedback">
                {{ $errors->first('naturalidade_estado_socio') }}
            </div>
            @endif
        </div>
    </div>
</fieldset>