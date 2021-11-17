@extends('site.prerepresentante.app')

@section('content-prerepresentante')

@if(Session::has('message'))
    <div class="d-block w-100">
        <p class="alert {{ Session::get('class') }}">{{ Session::get('message') }}</p>
    </div>
@endif

<div class="representante-content w-100">
        <p>Nomes de abas temporários</p>
    <!-- Nav tabs -->
    <ul class="nav nav-tabs nav-justified" role="tablist">
        <li class="nav-item text-primary">
            <a class="nav-link active" data-toggle="tab" href="#parte1">
                Parte 1 - PF e PJ
            </a>
        </li>
        <li class="nav-item text-primary">
            <a class="nav-link" data-toggle="tab" href="#parte2">
                Parte 2 - PF e PJ
            </a>
        </li>
        <li class="nav-item text-primary">
            <a class="nav-link" data-toggle="tab" href="#parte3">
                Parte 3 - PF ou PJ
            </a>
        </li>
        @if(strlen($prerep->cpf_cnpj) == 14)
        <li class="nav-item text-primary">
            <a class="nav-link" data-toggle="tab" href="#parte4">
                Parte 4 - PJ
            </a>
        </li>
        @endif
    </ul>

    <form action="{{-- route('prerepresentante.editar') --}}" method="POST" class="cadastroRepresentante">
        @csrf

        <!-- Tab panes -->
        <div class="tab-content">
            <!-- Tab 1 -->
            <div id="parte1" class="container tab-pane active"><br>
                <label>Tipo de registro *</label><br>
                <div class="form-check-inline {{ strlen($prerep->cpf_cnpj) == 14 ? 'disabled' : '' }}">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" value="PF" name="tipo" {{ strlen($prerep->cpf_cnpj) == 14 ? 'disabled' : '' }}>Pessoa Física
                    </label>
                </div>
                <div class="form-check-inline {{ strlen($prerep->cpf_cnpj) == 14 ? 'disabled' : '' }}">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" value="RT" name="tipo" {{ strlen($prerep->cpf_cnpj) == 14 ? 'disabled' : '' }}>Responsável Técnico
                    </label>
                </div>
                <div class="form-check-inline {{ strlen($prerep->cpf_cnpj) == 11 ? 'disabled' : '' }}">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" value="PJ" name="tipo" {{ strlen($prerep->cpf_cnpj) == 11 ? 'disabled' : 'checked' }}>Pessoa Jurídica
                    </label>
                </div>

                <div class="linha-lg-mini"></div>

                <div class="form-row">
                    <div class="col-sm mb-2-576">
                        <label for="nome">Nome Completo *</label>
                        <input
                            type="text"
                            id="nome"
                            class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
                            value="{{ $prerep->nome }}"
                            placeholder="Nome Completo"
                            required
                            readonly
                        >
                        @if($errors->has('nome'))
                        <div class="invalid-feedback">
                            {{ $errors->first('nome') }}
                        </div>
                        @endif
                    </div>
                </div>
                <div class="form-row mt-2">
                    <div class="col-sm mb-2-576">
                        <label for="cpf_cnpj">CPF ou CNPJ *</label>
                        <input
                            type="text"
                            class="form-control cpfOuCnpj {{ $errors->has('cpf_cnpj') ? 'is-invalid' : '' }}"
                            id="cpf_cnpj"
                            value="{{ $prerep->cpf_cnpj }}"
                            placeholder="CPF ou CNPJ"
                            required
                            readonly
                        >
                    </div>
                    <div class="col-sm mb-2-576">
                        <label for="dt_inicio_atividade">Data início de atividade *</label>
                        <input
                            type="date"
                            name="dt_inicio_atividade"
                            class="form-control {{ $errors->has('dt_inicio_atividade') ? 'is-invalid' : '' }}"
                            id="dt_inicio_atividade"
                            value="{{ isset($resultado->dt_inicio_atividade) ? $resultado->dt_inicio_atividade : old('dt_inicio_atividade') }}"
                        >
                        @if($errors->has('dt_inicio_atividade'))
                        <div class="invalid-feedback">
                            {{ $errors->first('dt_inicio_atividade') }}
                        </div>
                        @endif
                    </div>
                    <div class="col-sm mb-2-576">
                        <label for="registro_secundario">Registro Secundário ????</label>
                        <input
                            type="text"
                            class="form-control {{ $errors->has('registro_secundario') ? 'is-invalid' : '' }}"
                            id="registro_secundario"
                            value=""
                            placeholder="Registro Secundário ????"
                        >
                    </div>
                </div>
                <div class="form-row mt-2">
                    <div class="col-sm-6 mb-2-576">
                        <label for="segmento">
                            Segmento *
                        </label>
                        <select 
                            name="segmento" 
                            class="form-control" 
                            id="segmento"
                        >
                        @foreach(segmentos() as $segmento)
                            @if(!empty(old('segmento')))
                                <option value="{{ $segmento }}" {{ old('segmento') == $segmento ? 'selected' : '' }}>{{ $segmento }}</option>
                            @else
                                @if(isset($resultado->segmento))
                                    <option value="{{ $segmento }}" {{ $segmento == $resultado->segmento ? 'selected' : '' }}>{{ $segmento }}</option>
                                @else
                                <option value="{{ $segmento }}">{{ $segmento }}</option>
                                @endif
                            @endif
                        @endforeach
                        </select>
                        @if($errors->has('segmento'))
                        <div class="invalid-feedback">
                            {{ $errors->first('segmento') }}
                        </div>
                        @endif
                    </div>
                </div>

                <div class="linha-lg-mini mt-4"></div>

                <div class="form-row">
                    <div class="col-sm mb-2-576">
                        <label for="nome_mae">Nome da mãe *</label>
                        <input
                            type="text"
                            name="nome_mae"
                            class="form-control {{ $errors->has('nome_mae') ? 'is-invalid' : '' }}"
                            value="{{ isset($resultado->nome_mae) ? $resultado->nome_mae : old('nome_mae') }}"
                            placeholder="Nome da mãe"
                        >
                        @if($errors->has('nome_mae'))
                        <div class="invalid-feedback">
                            {{ $errors->first('nome_mae') }}
                        </div>
                        @endif
                    </div>
                </div>
                <div class="form-row mt-2">
                    <div class="col-sm mb-2-576">
                        <label for="nome_pai">Nome do pai *</label>
                        <input
                            type="text"
                            name="nome_pai"
                            class="form-control {{ $errors->has('nome_pai') ? 'is-invalid' : '' }}"
                            value="{{ isset($resultado->nome_pai) ? $resultado->nome_pai : old('nome_pai') }}"
                            placeholder="Nome do pai"
                        >
                        @if($errors->has('nome_pai'))
                        <div class="invalid-feedback">
                            {{ $errors->first('nome_pai') }}
                        </div>
                        @endif
                    </div>
                </div>

                <div class="linha-lg-mini mt-4"></div>

                <div class="form-row">
                    <div class="col-sm-6 mb-2-576">
                        <label for="estado_civil">
                            Estado Civil *
                        </label>
                        <select 
                            name="estado_civil" 
                            class="form-control" 
                            id="estado_civil"
                        >
                        @foreach($estados_civil as $estado_civil)
                            @if(!empty(old('estado_civil')))
                                <option value="{{ $estado_civil }}" {{ old('estado_civil') == $estado_civil ? 'selected' : '' }}>{{ $estado_civil }}</option>
                            @else
                                @if(isset($resultado->estado_civil))
                                    <option value="{{ $estado_civil }}" {{ $estado_civil == $resultado->estado_civil ? 'selected' : '' }}>{{ $estado_civil }}</option>
                                @else
                                <option value="{{ $estado_civil }}">{{ $estado_civil }}</option>
                                @endif
                            @endif
                        @endforeach
                        </select>
                        @if($errors->has('estado_civil'))
                        <div class="invalid-feedback">
                            {{ $errors->first('estado_civil') }}
                        </div>
                        @endif
                    </div>
                    <div class="col-sm-6 mt-2-768">
                        <label for="celular">Celular *</label>
                        <input type="text"
                        class="form-control celularInput {{ $errors->has('celular') ? 'is-invalid' : '' }}"
                        name="celular"
                        value="{{ isset($resultado->celular) ? $resultado->celular : old('celular') }}"
                        placeholder="Celular"
                        />
                        @if($errors->has('celular'))
                        <div class="invalid-feedback">
                            {{ $errors->first('celular') }}
                        </div>
                        @endif
                    </div>
                </div>
                <div class="form-row mt-2">
                    <div class="col-sm mb-2-576">
                        <label for="email">E-mail *</label>
                        <input
                            type="email"
                            class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                            value="{{ $prerep->email }}"
                            required
                            readonly
                        >
                        @if($errors->has('email'))
                        <div class="invalid-feedback">
                            {{ $errors->first('email') }}
                        </div>
                        @endif
                    </div>
                </div>
                <div class="form-row mt-2">
                    <div class="col-sm mb-2-576">
                        <label for="email2">E-mail<small> (opcional)</small></label>
                        <input
                            type="email"
                            class="form-control {{ $errors->has('email2') ? 'is-invalid' : '' }}"
                            value="{{ isset($resultado->email) ? $resultado->email : old('email2') }}"
                        >
                        @if($errors->has('email2'))
                        <div class="invalid-feedback">
                            {{ $errors->first('email2') }}
                        </div>
                        @endif
                    </div>
                </div>
                <div class="mt-3 float-right">
                    <button class="btn btn-primary" type="button">Salvar</button>
                    <a href="{{ route('prerepresentante.preregistro.view') }}" class="btn btn-default text-dark text-decoration-none ml-2">
                        Cancelar
                    </a>
                </div>
            </div>

            <!-- Tab 2 -->
            <div id="parte2" class="container tab-pane fade"><br>
                <h5 class="bold mb-2">Endereço da empresa *</h5>
                <div class="form-row mb-2">
                    <div class="col-sm-4 mb-2-576">
                        <label for="cep">CEP *</label>
                        <input
                            type="text"
                            name="cep"
                            class="form-control cep {{ $errors->has('cep') ? 'is-invalid' : '' }}"
                            id="cep"
                            placeholder="CEP"
                            value="{{ isset($resultado->cep) && explode(';', $resultado->cep)[0] ? explode(';', $resultado->cep)[0] : old('cep') }}"
                        >
                        @if($errors->has('cep'))
                        <div class="invalid-feedback">
                            {{ $errors->first('cep') }}
                        </div>
                        @endif
                    </div>
                    <div class="col-sm mb-2-576">
                        <label for="bairro">Bairro *</label>
                        <input
                            type="text"
                            name="bairro"
                            class="form-control {{ $errors->has('bairro') ? 'is-invalid' : '' }}"
                            id="bairro"
                            placeholder="Bairro"
                            value="{{ isset($resultado->bairro) && explode(';', $resultado->bairro)[0] ? explode(';', $resultado->bairro)[0] : old('bairro') }}"
                        >
                        @if($errors->has('bairro'))
                        <div class="invalid-feedback">
                            {{ $errors->first('bairro') }}
                        </div>
                        @endif
                    </div>
                </div>
                <div class="form-row mb-2">
                    <div class="col-sm mb-2-576">
                        <label for="rua">Logradouro *</label>
                        <input
                            type="text"
                            name="logradouro"
                            class="form-control {{ $errors->has('logradouro') ? 'is-invalid' : '' }}"
                            id="rua"
                            placeholder="Logradouro"
                            value="{{ isset($resultado->logradouro) && explode(';', $resultado->logradouro)[0] ? explode(';', $resultado->logradouro)[0] : old('logradouro') }}"
                        >
                        @if($errors->has('logradouro'))
                        <div class="invalid-feedback">
                            {{ $errors->first('logradouro') }}
                        </div>
                        @endif
                    </div>
                    <div class="col-sm-2 mb-2-576">
                        <label for="numero">Número *</label>
                        <input
                            type="text"
                            name="numero"
                            class="form-control numero {{ $errors->has('numero') ? 'is-invalid' : '' }}"
                            id="numero"
                            placeholder="Número"
                            value="{{ isset($resultado->numero) && explode(';', $resultado->numero)[0] ? explode(';', $resultado->numero)[0] : old('numero') }}"
                        >
                        @if($errors->has('numero'))
                        <div class="invalid-feedback">
                            {{ $errors->first('numero') }}
                        </div>
                        @endif
                    </div>
                </div>
                <div class="form-row mb-2">
                    <div class="col-sm-3 mb-2-576">
                        <label for="complemento">Complemento</label>
                        <input
                            type="text"
                            name="complemento"
                            class="form-control {{ $errors->has('complemento') ? 'is-invalid' : '' }}"
                            id="complemento"
                            placeholder="Complemento"
                            value="{{ isset($resultado->complemento) && explode(';', $resultado->complemento)[0] ? explode(';', $resultado->complemento)[0] : old('complemento') }}"
                        >
                        @if($errors->has('complemento'))
                        <div class="invalid-feedback">
                            {{ $errors->first('complemento') }}
                        </div>
                        @endif
                    </div>
                    <div class="col-sm-5 mb-2-576">
                        <label for="municipio">Município *</label>
                        <input
                            type="text"
                            name="municipio"
                            id="municipio"
                            class="form-control {{ $errors->has('municipio') ? 'is-invalid' : '' }}"
                            placeholder="Município"
                            value="{{ isset($resultado->municipio) && explode(';', $resultado->municipio)[0] ? explode(';', $resultado->municipio)[0] : old('municipio') }}"
                        >
                        @if($errors->has('municipio'))
                        <div class="invalid-feedback">
                            {{ $errors->first('municipio') }}
                        </div>
                        @endif
                    </div>
                    <div class="col-sm-4 mb-2-576">
                        <label for="estado">Estado *</label>
                        <select 
                            name="estado" 
                            id="estado" 
                            class="form-control {{ $errors->has('estado') ? 'is-invalid' : '' }}"
                        >
                        @foreach(estados() as $key => $estado)
                            @if(!empty(old('estado')))
                                <option value="{{ $key }}" {{ old('estado') == $key ? 'selected' : '' }}>{{ $estado }}</option>
                            @else
                                @if(isset($resultado->estado) && explode(';', $resultado->estado)[0])
                                    <option value="{{ $key }}" {{ $key == explode(';', $resultado->estado)[0] ? 'selected' : '' }}>{{ $estado }}</option>
                                @else
                                <option value="{{ $key }}">{{ $estado }}</option>
                                @endif
                            @endif
                        @endforeach
                        </select>
                        @if($errors->has('estado'))
                        <div class="invalid-feedback">
                            {{ $errors->first('estado') }}
                        </div>
                        @endif
                    </div>
                </div>

                <div class="linha-lg-mini mt-4"></div>

                <h5 class="bold mb-2">Endereço de correspondência *</h5>
                <div class="form-row mb-2">
                    <div class="col-sm-4 mb-2-576">
                        <label for="cep_cor">CEP *</label>
                        <input
                            type="text"
                            name="cep_cor"
                            class="form-control cep {{ $errors->has('cep_cor') ? 'is-invalid' : '' }}"
                            id="cep_cor"
                            placeholder="CEP"
                            value="{{ isset($resultado->cep) && explode(';', $resultado->cep)[1] ? explode(';', $resultado->cep)[1] : old('cep_cor') }}"
                        >
                        @if($errors->has('cep_cor'))
                        <div class="invalid-feedback">
                            {{ $errors->first('cep_cor') }}
                        </div>
                        @endif
                    </div>
                    <div class="col-sm mb-2-576">
                        <label for="bairro_cor">Bairro *</label>
                        <input
                            type="text"
                            name="bairro_cor"
                            class="form-control {{ $errors->has('bairro_cor') ? 'is-invalid' : '' }}"
                            id="bairro_cor"
                            placeholder="Bairro"
                            value="{{ isset($resultado->bairro) && explode(';', $resultado->bairro)[1] ? explode(';', $resultado->bairro)[1] : old('bairro_cor') }}"
                        >
                        @if($errors->has('bairro_cor'))
                        <div class="invalid-feedback">
                            {{ $errors->first('bairro_cor') }}
                        </div>
                        @endif
                    </div>
                </div>
                <div class="form-row mb-2">
                    <div class="col-sm mb-2-576">
                        <label for="logradouro_cor">Logradouro *</label>
                        <input
                            type="text"
                            name="logradouro_cor"
                            class="form-control {{ $errors->has('logradouro_cor') ? 'is-invalid' : '' }}"
                            id="logradouro_cor"
                            placeholder="Logradouro"
                            value="{{ isset($resultado->logradouro) && explode(';', $resultado->logradouro)[1] ? explode(';', $resultado->logradouro)[1] : old('logradouro_cor') }}"
                        >
                        @if($errors->has('logradouro_cor'))
                        <div class="invalid-feedback">
                            {{ $errors->first('logradouro_cor') }}
                        </div>
                        @endif
                    </div>
                    <div class="col-sm-2 mb-2-576">
                        <label for="numero_cor">Número *</label>
                        <input
                            type="text"
                            name="numero_cor"
                            class="form-control numero {{ $errors->has('numero_cor') ? 'is-invalid' : '' }}"
                            id="numero_cor"
                            placeholder="Número"
                            value="{{ isset($resultado->numero) && explode(';', $resultado->numero)[1] ? explode(';', $resultado->numero)[1] : old('numero_cor') }}"
                        >
                        @if($errors->has('numero_cor'))
                        <div class="invalid-feedback">
                            {{ $errors->first('numero_cor') }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="form-row mb-2">
                    <div class="col-sm-3 mb-2-576">
                        <label for="complemento_cor">Complemento</label>
                        <input
                            type="text"
                            name="complemento_cor"
                            class="form-control {{ $errors->has('complemento_cor') ? 'is-invalid' : '' }}"
                            id="complemento_cor"
                            placeholder="Complemento"
                            value="{{ isset($resultado->complemento) && explode(';', $resultado->complemento)[1] ? explode(';', $resultado->complemento)[1] : old('complemento_cor') }}"
                        >
                        @if($errors->has('complemento_cor'))
                        <div class="invalid-feedback">
                            {{ $errors->first('complemento_cor') }}
                        </div>
                        @endif
                    </div>
                    <div class="col-sm-5 mb-2-576">
                        <label for="municipio_cor">Município *</label>
                        <input
                            type="text"
                            name="municipio_cor"
                            id="municipio_cor"
                            class="form-control {{ $errors->has('municipio_cor') ? 'is-invalid' : '' }}"
                            placeholder="Município"
                            value="{{ isset($resultado->municipio) && explode(';', $resultado->municipio)[1] ? explode(';', $resultado->municipio)[1] : old('municipio_cor') }}"
                        >
                        @if($errors->has('municipio_cor'))
                        <div class="invalid-feedback">
                            {{ $errors->first('municipio_cor') }}
                        </div>
                        @endif
                    </div>
                    <div class="col-sm-4 mb-2-576">
                        <label for="estado_cor">Estado *</label>
                        <select 
                            name="estado_cor" 
                            id="estado_cor" 
                            class="form-control {{ $errors->has('estado_cor') ? 'is-invalid' : '' }}"
                        >
                        @foreach(estados() as $key => $estado)
                            @if(!empty(old('estado_cor')))
                            <option value="{{ $key }}" {{ old('estado_cor') == $key ? 'selected' : '' }}>{{ $estado }}</option>
                            @else
                                @if(isset($resultado->estado) && explode(';', $resultado->estado)[1])
                                <option value="{{ $key }}" {{ $key == explode(';', $resultado->estado)[1] ? 'selected' : '' }}>{{ $estado }}</option>
                                @else
                                <option value="{{ $key }}">{{ $estado }}</option>
                                @endif
                            @endif
                        @endforeach
                        </select>
                        @if($errors->has('estado_cor'))
                        <div class="invalid-feedback">
                            {{ $errors->first('estado_cor') }}
                        </div>
                        @endif
                    </div>
                </div>

                <div class="linha-lg-mini mt-4"></div>

                <label for="compr_residencia">Comprovante de residência *</label>

                <!-- Carrega os arquivos do bd com seus botoes de controle -->	
                @if(isset($resultado->compr_residencia))
                <div class="ArquivoBD_resid">
                    <div class="form-row mb-2">
                        <div class="input-group col-sm mb-2-576 mb-1 mt-2">
                            <input 
                                type="text" 
                                class="form-control" 
                                value="{{-- $resultado->compr_residencia --}}"
                                readonly
                            >
                            <div class="input-group-append">
                                <a href="/downloadFile/" class="btn btn-primary Arquivo-Download" value="" ><i class="fas fa-download"></i></a>
                                <a href="/excluirFile/" class="btn btn-danger Arquivo-Excluir" type="button" data-target=".myModal"><i class="fas fa-trash-alt"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Input do arquivo -->
                <div class="Arquivo_resid">
                    <div class="form-row">
                        <div class="input-group col-sm mb-2-576 mb-3">
                            <div class="custom-file">
                                <input 
                                    type="file" 
                                    class="custom-file-input files {{ $errors->has('compr_residencia') ? 'is-invalid' : '' }}" 
                                    id="compr_residencia"
                                    name="compr_residencia"
                                    value="{{ isset($resultado->compr_residencia) ? $resultado->compr_residencia : old('compr_residencia') }}"
                                >
                                <label class="custom-file-label ml-0" for="customFile">Escolher arquivo</label>
                            </div>
                            <div class="input-group-append">
                                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </div>
                        @if($errors->has('compr_residencia'))
                        <div class="invalid-feedback">
                            {{ $errors->first('compr_residencia') }}
                        </div>
                        @endif
                    </div>
                </div>

                <div class="mt-3 float-right">
                    <button class="btn btn-primary" type="button">Salvar</button>
                    <a href="{{ route('prerepresentante.preregistro.view') }}" class="btn btn-default text-dark text-decoration-none ml-2">
                        Cancelar
                    </a>
                </div>
            </div>

            <!-- Tab 3 -->
            <div id="parte3" class="container tab-pane fade"><br>
                @if(strlen($prerep->cpf_cnpj) == 11)
                <label>Gênero *</label><br>
                <div class="form-check-inline {{ isset($resultado->genero) && $resultado->genero == 'F' ? 'selected' : '' }}">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" value="F" name="tipo" {{ isset($resultado->genero) && $resultado->genero == 'F' ? 'selected' : '' }}>Feminino
                    </label>
                </div>
                <div class="form-check-inline {{ isset($resultado->genero) && $resultado->genero == 'M' ? 'selected' : '' }}">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" value="M" name="tipo" {{ isset($resultado->genero) && $resultado->genero == 'F' ? 'selected' : '' }}>Masculino
                    </label>
                </div>

                <div class="linha-lg-mini"></div>
                <!-- Mácara do RG foi feita em outro projeto, aguardar o merge -->
                <div class="form-row mb-2">
                    <div class="col-sm mb-2-576">
                        <label for="rg">RG *</label>
                        <input
                            type="text"
                            name="rg"
                            class="form-control {{ $errors->has('rg') ? 'is-invalid' : '' }}"
                            id="rg"
                            placeholder="RG"
                            value="{{ isset($resultado->rg) ? $resultado->rg : old('rg') }}"
                        >
                        @if($errors->has('rg'))
                        <div class="invalid-feedback">
                            {{ $errors->first('rg') }}
                        </div>
                        @endif
                    </div>
                    <div class="col-sm-2 mb-2-576">
                        <label for="emissor">Órgão Emissor *</label>
                        <input
                            type="text"
                            name="emissor"
                            class="form-control {{ $errors->has('emissor') ? 'is-invalid' : '' }}"
                            id="emissor"
                            value="{{ isset($resultado->emissor) ? $resultado->emissor : old('emissor') }}"
                        >
                        @if($errors->has('emissor'))
                        <div class="invalid-feedback">
                            {{ $errors->first('emissor') }}
                            </div>
                        @endif
                    </div>
                    <div class="col-sm-5 mb-2-576">
                        <label for="nacionalidade">Nacionalidade *</label>
                        <select 
                            name="nacionalidade" 
                            id="nacionalidade" 
                            class="form-control {{ $errors->has('nacionalidade') ? 'is-invalid' : '' }}"
                        >
                        @foreach($nacionalidades as $nacionalidade)
                            @if(!empty(old('nacionalidade')))
                            <option value="{{ $nacionalidade }}" {{ old('nacionalidade') == $nacionalidade ? 'selected' : '' }}>{{ $nacionalidade }}</option>
                            @else
                                @if(isset($resultado->nacionalidade))
                                <option value="{{ $nacionalidade }}" {{ $nacionalidade == $resultado->nacionalidade ? 'selected' : '' }}>{{ $nacionalidade }}</option>
                                @else
                                <option value="{{ $nacionalidade }}">{{ $nacionalidade }}</option>
                                @endif
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
                        <label for="certidao_tse">Certidão TSE ??? *</label>
                        <input
                            type="text"
                            name="certidao_tse"
                            class="form-control {{ $errors->has('certidao_tse') ? 'is-invalid' : '' }}"
                            id="certidao_tse"
                            placeholder="Certidão TSE ????"
                            value="{{ isset($resultado->certidao_tse) ? $resultado->certidao_tse : old('certidao_tse') }}"
                        >
                        @if($errors->has('certidao_tse'))
                        <div class="invalid-feedback">
                            {{ $errors->first('certidao_tse') }}
                        </div>
                        @endif
                    </div>
                    <div class="col-sm mb-2-576">
                        <label for="reservista">Reservista ??? *</label>
                        <input
                            type="text"
                            name="reservista"
                            class="form-control {{ $errors->has('reservista') ? 'is-invalid' : '' }}"
                            id="reservista"
                            placeholder="Reservista ????"
                            value="{{ isset($resultado->reservista) ? $resultado->reservista : old('reservista') }}"
                        >
                        @if($errors->has('reservista'))
                        <div class="invalid-feedback">
                            {{ $errors->first('reservista') }}
                        </div>
                        @endif
                    </div>
                </div>
                @else
                <div class="form-row mb-2">
                    <div class="col-sm mb-2-576">
                        <label for="razao_social">Razão Social *</label>
                        <input
                            type="text"
                            name="razao_social"
                            class="form-control {{ $errors->has('razao_social') ? 'is-invalid' : '' }}"
                            id="razao_social"
                            placeholder="Razão Social"
                            value="{{ isset($resultado->razao_social) ? $resultado->razao_social : old('razao_social') }}"
                        >
                        @if($errors->has('razao_social'))
                        <div class="invalid-feedback">
                            {{ $errors->first('razao_social') }}
                        </div>
                        @endif
                    </div>
                    <div class="col-sm-4 mb-2-576">
                        <label for="capital_social">Capital Social *</label>
                        <input
                            type="text"
                            name="capital_social"
                            class="form-control {{ $errors->has('capital_social') ? 'is-invalid' : '' }}"
                            id="capital_social"
                            placeholder="R$ 1.000,00"
                            value="{{ isset($resultado->capital_social) ? $resultado->capital_social : old('capital_social') }}"
                        >
                        @if($errors->has('capital_social'))
                        <div class="invalid-feedback">
                            {{ $errors->first('capital_social') }}
                        </div>
                        @endif
                    </div>
                </div>
                <div class="form-row mb-2">
                    <div class="col-sm mb-2-576">
                        <label for="dt_jucesp">Data de registro na JUCESP *</label>
                        <input
                            type="date"
                            name="dt_jucesp"
                            class="form-control {{ $errors->has('dt_jucesp') ? 'is-invalid' : '' }}"
                            id="dt_jucesp"
                            value="{{ isset($resultado->dt_jucesp) ? $resultado->dt_jucesp : old('dt_jucesp') }}"
                        >
                        @if($errors->has('dt_jucesp'))
                        <div class="invalid-feedback">
                            {{ $errors->first('dt_jucesp') }}
                        </div>
                        @endif
                    </div>
                    <div class="col-sm mb-2-576">
                        <label for="nire">NIRE *</label>
                        <input
                            type="text"
                            name="nire"
                            class="form-control {{ $errors->has('nire') ? 'is-invalid' : '' }}"
                            id="nire"
                            placeholder="NIRE"
                            value="{{ isset($resultado->nire) ? $resultado->nire : old('nire') }}"
                            maxlength="11"
                        >
                        @if($errors->has('nire'))
                        <div class="invalid-feedback">
                            {{ $errors->first('nire') }}
                        </div>
                        @endif
                    </div>
                </div>
                <p><b>Falta campos: </b></p>
                <p>Contato</p>
                <p>Contrato Social é um anexo?</p>
                <p>Requerimento de Indicação do RT é um anexo?</p>
                <p>Comprovante de Inscrição CNPJ é um anexo?</p>
                @endif
                <div class="mt-3 float-right">
                    <button class="btn btn-primary" type="button">Salvar</button>
                    <a href="{{ route('prerepresentante.preregistro.view') }}" class="btn btn-default text-dark text-decoration-none ml-2">
                        Cancelar
                    </a>
                </div>

                <!-- se PJ, o botão enviar será na última aba -->
                @if(strlen($prerep->cpf_cnpj) == 11)
                <div class="linha-lg-mini mt-4"></div>
                <button class="btn btn-success" type="submit">Enviar</button>
                @endif
            </div>

            <!-- Tab 4 -->
            @if(strlen($prerep->cpf_cnpj) == 14)
            <div id="parte4" class="container tab-pane fade"><br>
                <p class="text-dark mb-3"><i class="fas fa-info-circle"></i> Limite de até {{ $totalFiles }} anexos para cada</p>

                <h5 class="bold mb-2">Documento de todos os sócios *</h5>

                <!-- Carrega os arquivos do bd com seus botoes de controle -->	
                @if(isset($resultado->anexos_socios))
                <div class="ArquivoBD_doc">
                    <div class="form-row mb-2">
                        <div class="input-group col-sm mb-2-576 mb-1 mt-2">
                            <input 
                                type="text" 
                                class="form-control" 
                                value="{{-- $teste --}}"
                                readonly
                            >
                            <div class="input-group-append">
                                <a href="/downloadFile/" class="btn btn-primary Arquivo-Download" value="" ><i class="fas fa-download"></i></a>
                                <a href="/excluirFile/" class="btn btn-danger Arquivo-Excluir" type="button" data-target=".myModal"><i class="fas fa-trash-alt"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Inputs para anexar o arquivo -->	
                <div class="Arquivo_doc">
                    <label for="anexos_socios">Anexo RG ou CNH *</label>
                    <div class="form-row">
                        <div class="input-group col-sm mb-2-576 pl-0">
                            <div class="custom-file">
                                <input 
                                    type="file" 
                                    class="custom-file-input files {{-- $errors->has('anexos_socios') ? 'is-invalid' : '' --}}" 
                                    name="anexos_socios[]"
                                    value="{{-- old('compr_residencia') --}}"
                                >
                                <label class="custom-file-label">Escolher arquivo</label>
                            </div>
                            <div class="input-group-append">
                                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </div>
                        @if($errors->has('anexos_socios'))
                        <div class="invalid-feedback">
                            {{ $errors->first('anexos_socios') }}
                        </div>
                        @endif
                    </div>
                </div>

                <div class="linha-lg-mini mt-4"></div>

                <h5 class="bold mb-2">Comprovante de residência de todos os sócios *</h5>

                <!-- Carrega os arquivos do bd com seus botoes de controle -->	
                @if(isset($resultado->anexos_resid_socios))
                <div class="ArquivoBD_resid_socios">
                    <div class="form-row mb-2">
                        <div class="input-group col-sm mb-2-576 mb-1 mt-2">
                            <input 
                                type="text" 
                                class="form-control" 
                                value="{{-- $teste --}}"
                                readonly
                            >
                            <div class="input-group-append">
                                <a href="/downloadFile/" class="btn btn-primary Arquivo-Download" value="" ><i class="fas fa-download"></i></a>
                                <a href="/excluirFile/" class="btn btn-danger Arquivo-Excluir" type="button" data-target=".myModal"><i class="fas fa-trash-alt"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Inputs para anexar o arquivo -->	
                <div class="Arquivo_resid_socios">
                    <label for="anexos_socios">Anexo *</label>
                    <div class="form-row">
                        <div class="input-group col-sm mb-2-576 mb-2 pl-0">
                            <div class="custom-file">
                                <input 
                                    type="file" 
                                    class="custom-file-input files {{-- $errors->has('anexos_resid_socios') ? 'is-invalid' : '' --}}" 
                                    name="anexos_resid_socios[]"
                                    value="{{-- old('compr_residencia') --}}"
                                >
                                <label class="custom-file-label">Escolher arquivo</label>
                            </div>
                            <div class="input-group-append">
                                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </div>
                        @if($errors->has('anexos_resid_socios')) 
                        <div class="invalid-feedback">
                            {{ $errors->first('anexos_resid_socios') }}
                        </div>
                        @endif
                    </div>
                </div>

                <div class="mt-3 float-right">
                    <button class="btn btn-primary" type="button">Salvar</button>
                    <a href="{{ route('prerepresentante.preregistro.view') }}" class="btn btn-default text-dark text-decoration-none ml-2">
                        Cancelar
                    </a>
                </div>
                <div class="linha-lg-mini mt-4"></div>
                <button class="btn btn-success" type="submit">Enviar</button>
            </div>
            @endif
        </div>
    </form>

</div>

@endsection