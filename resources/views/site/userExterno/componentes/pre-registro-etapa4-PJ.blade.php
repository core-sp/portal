<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="nome_contato">R44 - Nome Completo *</label>
        <input
            name="nome_contato"
            type="text"
            class="form-control {{ $errors->has('nome_contato') ? 'is-invalid' : '' }}"
            value="{{-- $user->nome --}}"
            placeholder="Nome Completo"
            minlength="5"
            maxlength="191"
        />
        @if($errors->has('nome_contato'))
        <div class="invalid-feedback">
            {{ $errors->first('nome_contato') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="nome_social_contato">R45 - Nome Social</label>
        <input
            name="nome_social_contato"
            type="text"
            class="form-control {{ $errors->has('nome_social_contato') ? 'is-invalid' : '' }}"
            value="{{-- $user->nome --}}"
            placeholder="Nome Social"
        />
        @if($errors->has('nome_social_contato'))
        <div class="invalid-feedback">
            {{ $errors->first('nome_social_contato') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="dt_nasc_contato">R46 - Data de Nascimento *</label>
        <input
            name="dt_nasc_contato"
            type="date"
            class="form-control {{ $errors->has('dt_nasc_contato') ? 'is-invalid' : '' }}"
            value="{{-- $user->nome --}}"
        />
        @if($errors->has('dt_nasc_contato'))
        <div class="invalid-feedback">
            {{ $errors->first('dt_nasc_contato') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="sexo_contato">R47 - Sexo *</label><br>
        <div class="form-check-inline">
            <label class="form-check-label">
                <input type="radio" 
                    class="form-check-input" 
                    name="sexo_contato" 
                    value="F" {{ (!empty(old('sexo_contato')) && (old('sexo_contato') == 'F')) || (isset($resultado->sexo) && $resultado->sexo == 'F') ? 'checked' : '' }}
                />
                Feminino
            </label>
        </div>
        <div class="form-check-inline">
            <label class="form-check-label">
                <input type="radio" 
                    class="form-check-input" 
                    name="sexo_contato" 
                    value="M" {{ (!empty(old('sexo_contato')) && (old('sexo_contato') == 'M')) || (isset($resultado->sexo) && $resultado->sexo == 'M') ? 'checked' : '' }}
                />
                Masculino
            </label>
        </div>
        @if($errors->has('sexo_contato'))
        <div class="invalid-feedback">
            {{ $errors->first('sexo_contato') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="cpf_cnpj_contato">R48 - CPF/CNPJ *</label>
        <input
            type="text"
            class="form-control cpfOuCnpj {{ $errors->has('cpf_cnpj_contato') ? ' is-invalid' : '' }}"
            name="cpf_cnpj_contato"
            value="{{-- Session::get('cpf_cnpj') ? apenasNumeros(Session::get('cpf_cnpj')) : old('cpf_cnpj_contato') --}}"
            placeholder="CPF ou CNPJ"
        />
        @if($errors->has('cpf_cnpj_contato'))
        <div class="invalid-feedback">
            {{ $errors->first('cpf_cnpj_contato') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="rg_contato">R49 - N° RG *</label>
        <input
            name="rg_contato"
            type="text"
            id="rg"
            class="form-control rgInput {{ $errors->has('rg_contato') ? 'is-invalid' : '' }}"
            value="{{-- $user->nome --}}"
            placeholder="RG"
            maxlength="20"
        />
        @if($errors->has('rg_contato'))
        <div class="invalid-feedback">
            {{ $errors->first('rg_contato') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="emissor_contato">R50 - Órgão Emissor *</label>
        <input
            name="emissor_contato"
            type="text"
            class="form-control {{ $errors->has('emissor_contato') ? 'is-invalid' : '' }}"
            value="{{-- $user->nome --}}"
            placeholder="Emissor"
            maxlength="10"
        />
        @if($errors->has('emissor_contato'))
        <div class="invalid-feedback">
            {{ $errors->first('emissor_contato') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm-4 mb-2-576">
        <label for="cep_contato">R51 - CEP *</label>
        <input
            type="text"
            name="cep_contato"
            class="form-control cep {{ $errors->has('cep_contato') ? 'is-invalid' : '' }}"
            id="cep"
            placeholder="CEP"
            value="{{-- isset($resultado->cep) && explode(';', $resultado->cep)[0] ? explode(';', $resultado->cep)[0] : old('cep_contato') --}}"
        />
        @if($errors->has('cep_contato'))
        <div class="invalid-feedback">
            {{ $errors->first('cep_contato') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="bairro_contato">R52 - Bairro *</label>
        <input
            type="text"
            name="bairro_contato"
            class="form-control {{ $errors->has('bairro_contato') ? 'is-invalid' : '' }}"
            id="bairro"
            placeholder="Bairro"
            value="{{-- isset($resultado->bairro) && explode(';', $resultado->bairro)[0] ? explode(';', $resultado->bairro)[0] : old('bairro_contato') --}}"
        />
        @if($errors->has('bairro_contato'))
        <div class="invalid-feedback">
            {{ $errors->first('bairro_contato') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="rua_contato">R53 - Logradouro *</label>
        <input
            type="text"
            name="rua_contato"
            class="form-control {{ $errors->has('rua_contato') ? 'is-invalid' : '' }}"
            id="rua"
            placeholder="Logradouro"
            value="{{-- isset($resultado->logradouro) && explode(';', $resultado->logradouro)[0] ? explode(';', $resultado->logradouro)[0] : old('rua_contato') --}}"
        />
        @if($errors->has('rua_contato'))
        <div class="invalid-feedback">
            {{ $errors->first('rua_contato') }}
        </div>
        @endif
    </div>
    <div class="col-sm-2 mb-2-576">
        <label for="numero_contato">R54 - Número *</label>
        <input
            type="text"
            name="numero_contato"
            class="form-control numero {{ $errors->has('numero_contato') ? 'is-invalid' : '' }}"
            id="numero"
            placeholder="Número"
            value="{{-- isset($resultado->numero) && explode(';', $resultado->numero)[0] ? explode(';', $resultado->numero)[0] : old('numero_contato') --}}"
        />
        @if($errors->has('numero_contato'))
        <div class="invalid-feedback">
            {{ $errors->first('numero_contato') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm-3 mb-2-576">
        <label for="compl_contato">R55 - Complemento</label>
        <input
            type="text"
            name="compl_contato"
            class="form-control {{ $errors->has('compl_contato') ? 'is-invalid' : '' }}"
            id="complemento"
            placeholder="Complemento"
            value="{{-- isset($resultado->complemento) && explode(';', $resultado->complemento)[0] ? explode(';', $resultado->complemento)[0] : old('compl_contato') --}}"
        />
        @if($errors->has('compl_contato'))
        <div class="invalid-feedback">
            {{ $errors->first('compl_contato') }}
        </div>
        @endif
    </div>
    <div class="col-sm-5 mb-2-576">
        <label for="cidade_contato">R56 - Município *</label>
        <input
            type="text"
            name="cidade_contato"
            id="cidade"
            class="form-control {{ $errors->has('cidade_contato') ? 'is-invalid' : '' }}"
            placeholder="Município"
            value="{{-- isset($resultado->municipio) && explode(';', $resultado->municipio)[0] ? explode(';', $resultado->municipio)[0] : old('cidade_contato') --}}"
        />
        @if($errors->has('cidade_contato'))
        <div class="invalid-feedback">
            {{ $errors->first('cidade_contato') }}
        </div>
        @endif
    </div>
    <div class="col-sm-4 mb-2-576">
        <label for="uf_contato">R57 - Estado *</label>
        <select 
            name="uf_contato" 
            id="uf" 
            class="form-control {{ $errors->has('uf_contato') ? 'is-invalid' : '' }}"
        >
        @foreach(estados() as $key => $estado)
            @if(!empty(old('uf_contato')))
            <option value="{{ $key }}" {{ old('uf_contato') == $key ? 'selected' : '' }}>{{ $estado }}</option>
            @elseif(isset($resultado->estado) && explode(';', $resultado->estado)[0])
            <option value="{{ $key }}" {{ $key == explode(';', $resultado->estado)[0] ? 'selected' : '' }}>{{ $estado }}</option>
            @else
            <option value="{{ $key }}">{{ $estado }}</option>
            @endif
        @endforeach
        </select>
        @if($errors->has('uf_contato'))
        <div class="invalid-feedback">
            {{ $errors->first('uf_contato') }}
        </div>
        @endif
    </div>
</div>

<div class="form-row mb-2">
    <div class="col-sm mb-2-576">
        <label for="nome_mae_contato">R58 - Nome da Mãe *</label>
        <input
            name="nome_mae_contato"
            type="text"
            class="form-control {{ $errors->has('nome_mae_contato') ? 'is-invalid' : '' }}"
            value="{{-- $user->nome --}}"
            placeholder="Nome da Mãe"
            minlength="5"
            maxlength="191"
        />
        @if($errors->has('nome_mae_contato'))
        <div class="invalid-feedback">
            {{ $errors->first('nome_mae_contato') }}
        </div>
        @endif
    </div>
    <div class="col-sm mb-2-576">
        <label for="nome_pai_contato">R59 - Nome do Pai *</label>
        <input
            name="nome_pai_contato"
            type="text"
            class="form-control {{ $errors->has('nome_pai_contato') ? 'is-invalid' : '' }}"
            value="{{-- $user->nome --}}"
            placeholder="Nome do Pai"
            minlength="5"
            maxlength="191"
        />
        @if($errors->has('nome_pai_contato'))
        <div class="invalid-feedback">
            {{ $errors->first('nome_pai_contato') }}
        </div>
        @endif
    </div>
</div>