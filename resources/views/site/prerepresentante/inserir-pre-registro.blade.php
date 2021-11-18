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
                @foreach($tipos as $key => $tipo)
                    @if(strlen($prerep->cpf_cnpj) == 14)
                    <div class="form-check-inline {{ (isset($resultado->tipo) && $resultado->tipo == $key) || (!isset($resultado->tipo) && ($key == 'PJ')) ? 'checked' : 'disabled' }}">
                        <label class="form-check-label">
                            <input type="radio" class="form-check-input" value="{{ $key }}" name="tipo" {{ (isset($resultado->tipo) && $resultado->tipo == $key) || (!isset($resultado->tipo) && ($key == 'PJ')) ? 'checked' : 'disabled' }}>{{ $tipo }}
                        </label>
                    </div>
                    @else
                        @if(isset($resultado->tipo) && $key != 'PJ')
                        <div class="form-check-inline {{ $resultado->tipo == $key ? 'checked' : '' }}">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" value="{{ $key }}" name="tipo" {{ $resultado->tipo == $key ? 'checked' : '' }}>{{ $tipo }}
                            </label>
                        </div>
                        @else
                        <div class="form-check-inline {{ $key == 'PJ' ? 'disabled' : '' }}">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" value="{{ $key }}" name="tipo" {{ $key == 'PJ' ? 'disabled' : '' }}>{{ $tipo }}
                            </label>
                        </div>
                        @endif
                    @endif
                @endforeach

                <div class="linha-lg-mini"></div>

                <div class="form-row mb-2">
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
                <div class="form-row mb-2">
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
                <div class="form-row mb-2">
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

                <div class="linha-lg-mini"></div>

                <div class="form-row mb-2">
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
                <div class="form-row mb-2">
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

                <div class="linha-lg-mini"></div>

                <label for="copia_identidade">Cópia de identidade ou CNH *</label>
                <!-- Carrega os arquivos do bd com seus botoes de controle -->	
                @if(isset($resultado->copia_identidade))
                <div class="ArquivoBD_cp_identidade">
                    <div class="form-row mb-2">
                        <div class="input-group col-sm mb-2-576">
                            <input 
                                type="text" 
                                class="form-control" 
                                value="{{-- $resultado->copia_identidade --}}"
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
                <div class="Arquivo_cp_identidade">
                    <div class="form-row mb-2">
                        <div class="input-group col-sm mb-2-576">
                            <div class="custom-file">
                                <input 
                                    type="file" 
                                    class="custom-file-input files {{ $errors->has('copia_identidade') ? 'is-invalid' : '' }}" 
                                    id="copia_identidade"
                                    name="copia_identidade"
                                    value="{{ isset($resultado->copia_identidade) ? $resultado->copia_identidade : old('copia_identidade') }}"
                                >
                                <label class="custom-file-label ml-0" for="customFile"><span class="text-secondary">Escolher arquivo</span></label>
                            </div>
                            <div class="input-group-append">
                                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </div>
                        @if($errors->has('copia_identidade'))
                        <div class="invalid-feedback">
                            {{ $errors->first('copia_identidade') }}
                        </div>
                        @endif
                    </div>
                </div>

                <label for="copia_cpf">Cópia do CPF *</label>
                <!-- Carrega os arquivos do bd com seus botoes de controle -->	
                @if(isset($resultado->copia_cpf))
                <div class="ArquivoBD_cp_cpf">
                    <div class="form-row mb-2">
                        <div class="input-group col-sm mb-2-576">
                            <input 
                                type="text" 
                                class="form-control" 
                                value="{{-- $resultado->copia_cpf --}}"
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
                <div class="Arquivo_cp_cpf">
                    <div class="form-row mb-2">
                        <div class="input-group col-sm mb-2-576">
                            <div class="custom-file">
                                <input 
                                    type="file" 
                                    class="custom-file-input files {{ $errors->has('copia_cpf') ? 'is-invalid' : '' }}" 
                                    id="copia_cpf"
                                    name="copia_cpf"
                                    value="{{ isset($resultado->copia_cpf) ? $resultado->copia_cpf : old('copia_cpf') }}"
                                >
                                <label class="custom-file-label ml-0" for="customFile"><span class="text-secondary">Escolher arquivo</span></label>
                            </div>
                            <div class="input-group-append">
                                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </div>
                        @if($errors->has('copia_cpf'))
                        <div class="invalid-feedback">
                            {{ $errors->first('copia_cpf') }}
                        </div>
                        @endif
                    </div>
                </div>

                <div class="linha-lg-mini"></div>

                <div class="form-row mb-2">
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
                    <div class="col-sm-6 mb-2-576">
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
                <div class="form-row mb-2">
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
                <div class="form-row mb-2">
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
                <h5 class="bold mb-2">Endereço da empresa</h5>
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

                <div class="linha-lg-mini"></div>

                <h5 class="bold mb-2">Endereço de correspondência</h5>
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

                <div class="linha-lg-mini"></div>

                <label for="compr_residencia">Comprovante de residência *</label>
                <!-- Carrega os arquivos do bd com seus botoes de controle -->	
                @if(isset($resultado->compr_residencia))
                <div class="ArquivoBD_resid">
                    <div class="form-row mb-2">
                        <div class="input-group col-sm mb-2-576">
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
                    <div class="form-row mb-2">
                        <div class="input-group col-sm mb-2-576">
                            <div class="custom-file">
                                <input 
                                    type="file" 
                                    class="custom-file-input files {{ $errors->has('compr_residencia') ? 'is-invalid' : '' }}" 
                                    id="compr_residencia"
                                    name="compr_residencia"
                                    value="{{ isset($resultado->compr_residencia) ? $resultado->compr_residencia : old('compr_residencia') }}"
                                >
                                <label class="custom-file-label ml-0" for="customFile"><span class="text-secondary">Escolher arquivo</span></label>
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

                <label for="certidao_tse">Certidão de quitação eleitoral *</label>
                <!-- Carrega os arquivos do bd com seus botoes de controle -->	
                @if(isset($resultado->certidao_tse))
                <div class="ArquivoBD_tse">
                    <div class="form-row mb-2">
                        <div class="input-group col-sm mb-2-576 mb-1 mt-2">
                            <input 
                                type="text" 
                                class="form-control" 
                                value="{{-- $resultado->certidao_tse --}}"
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
                <div class="Arquivo_tse">
                    <div class="form-row mb-2">
                        <div class="input-group col-sm mb-2-576">
                            <div class="custom-file">
                                <input 
                                    type="file" 
                                    class="custom-file-input files {{ $errors->has('certidao_tse') ? 'is-invalid' : '' }}" 
                                    id="certidao_tse"
                                    name="certidao_tse"
                                    value="{{ isset($resultado->certidao_tse) ? $resultado->certidao_tse : old('certidao_tse') }}"
                                >
                                <label class="custom-file-label ml-0" for="customFile"><span class="text-secondary">Escolher arquivo</span></label>
                            </div>
                            <div class="input-group-append">
                                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </div>
                        @if($errors->has('certidao_tse'))
                        <div class="invalid-feedback">
                            {{ $errors->first('certidao_tse') }}
                        </div>
                        @endif
                    </div>
                </div>

                <label for="reservista">Cerificado de reservista ou dispensa *</label>
                <!-- Carrega os arquivos do bd com seus botoes de controle -->	
                @if(isset($resultado->reservista))
                <div class="ArquivoBD_reservista">
                    <div class="form-row mb-2">
                        <div class="input-group col-sm mb-2-576">
                            <input 
                                type="text" 
                                class="form-control" 
                                value="{{-- $resultado->reservista --}}"
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
                <div class="Arquivo_reservista">
                    <div class="form-row mb-2">
                        <div class="input-group col-sm mb-2-576">
                            <div class="custom-file">
                                <input 
                                    type="file" 
                                    class="custom-file-input files {{ $errors->has('reservista') ? 'is-invalid' : '' }}" 
                                    id="reservista"
                                    name="reservista"
                                    value="{{ isset($resultado->reservista) ? $resultado->reservista : old('reservista') }}"
                                >
                                <label class="custom-file-label ml-0" for="customFile"><span class="text-secondary">Escolher arquivo</span></label>
                            </div>
                            <div class="input-group-append">
                                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </div>
                        @if($errors->has('reservista'))
                        <div class="invalid-feedback">
                            {{ $errors->first('reservista') }}
                        </div>
                        @endif
                    </div>
                </div>
                <div id="campo_anexo_indica_rt" style="{{ isset($resultado->tipo) && $resultado->tipo == 'RT' ? '' : 'display: none;' }}">
                    <label for="compr_indica_rt">Declaração de indicação do responsável técnico *</label>
                    <!-- Carrega os arquivos do bd com seus botoes de controle -->	
                    @if(isset($resultado->compr_indica_rt))
                    <div class="ArquivoBD_indica_rt">
                        <div class="form-row mb-2">
                            <div class="input-group col-sm mb-2-576">
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    value="{{-- $resultado->compr_indica_rt --}}"
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
                    <div class="Arquivo_indica_rt">
                        <div class="form-row mb-2">
                            <div class="input-group col-sm mb-2-576">
                                <div class="custom-file">
                                    <input 
                                        type="file" 
                                        class="custom-file-input files {{ $errors->has('compr_indica_rt') ? 'is-invalid' : '' }}" 
                                        id="compr_indica_rt"
                                        name="compr_indica_rt"
                                        value="{{ isset($resultado->compr_indica_rt) ? $resultado->compr_indica_rt : old('compr_indica_rt') }}"
                                    >
                                    <label class="custom-file-label ml-0" for="customFile"><span class="text-secondary">Escolher arquivo</span></label>
                                </div>
                                <div class="input-group-append">
                                    <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            </div>
                            @if($errors->has('compr_indica_rt'))
                            <div class="invalid-feedback">
                                {{ $errors->first('compr_indica_rt') }}
                            </div>
                            @endif
                        </div>
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

                <div class="linha-lg-mini"></div>
                
                <h5 class="bold mb-2">Contato</h5>
                <div class="form-row mb-2">
                    <div class="col-sm-8 mb-2-576">
                        <label for="contato_nome">Nome *</label>
                        <input
                            type="text"
                            name="contato_nome"
                            class="form-control {{ $errors->has('contato_nome') ? 'is-invalid' : '' }}"
                            id="contato_nome"
                            value="{{ isset($resultado->contato_nome) ? $resultado->contato_nome : old('contato_nome') }}"
                            placeholder="Nome"
                        >
                        @if($errors->has('contato_nome'))
                        <div class="invalid-feedback">
                            {{ $errors->first('contato_nome') }}
                        </div>
                        @endif
                    </div>
                    <div class="col-sm-4 mb-2-576">
                        <label for="contato_celular">Celular *</label>
                        <input type="text"
                            class="form-control celularInput {{ $errors->has('contato_celular') ? 'is-invalid' : '' }}"
                            name="contato_celular"
                            value="{{ isset($resultado->contato_celular) ? $resultado->contato_celular : old('contato_celular') }}"
                            placeholder="Celular"
                        />
                        @if($errors->has('contato_celular'))
                        <div class="invalid-feedback">
                            {{ $errors->first('contato_celular') }}
                        </div>
                        @endif
                    </div>
                </div>

                <label for="contrato_social">Contrato Social *</label>
                <!-- Carrega os arquivos do bd com seus botoes de controle -->	
                @if(isset($resultado->contrato_social))
                <div class="ArquivoBD_contrato_social">
                    <div class="form-row mb-2">
                        <div class="input-group col-sm mb-2-576">
                            <input 
                                type="text" 
                                class="form-control" 
                                value="{{-- $resultado->contrato_social --}}"
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
                <div class="Arquivo_contrato_social">
                    <div class="form-row mb-2">
                        <div class="input-group col-sm mb-2-576">
                            <div class="custom-file">
                                <input 
                                    type="file" 
                                    class="custom-file-input files {{ $errors->has('contrato_social') ? 'is-invalid' : '' }}" 
                                    id="contrato_social"
                                    name="contrato_social"
                                    value="{{ isset($resultado->contrato_social) ? $resultado->contrato_social : old('contrato_social') }}"
                                >
                                <label class="custom-file-label ml-0" for="customFile"><span class="text-secondary">Escolher arquivo</span></label>
                            </div>
                            <div class="input-group-append">
                                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </div>
                        @if($errors->has('contrato_social'))
                        <div class="invalid-feedback">
                            {{ $errors->first('contrato_social') }}
                        </div>
                        @endif
                    </div>
                </div>

                <label for="compr_indica_rt_pj">Requerimento de indicação do Responsável Técnico *</label>
                <!-- Carrega os arquivos do bd com seus botoes de controle -->	
                @if(isset($resultado->compr_indica_rt_pj))
                <div class="ArquivoBD_indica_rt_pj">
                    <div class="form-row mb-2">
                        <div class="input-group col-sm mb-2-576">
                            <input 
                                type="text" 
                                class="form-control" 
                                value="{{-- $resultado->compr_indica_rt_pj --}}"
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
                <div class="Arquivo_indica_rt_pj">
                    <div class="form-row mb-2">
                        <div class="input-group col-sm mb-2-576">
                            <div class="custom-file">
                                <input 
                                    type="file" 
                                    class="custom-file-input files {{ $errors->has('compr_indica_rt_pj') ? 'is-invalid' : '' }}" 
                                    id="compr_indica_rt_pj"
                                    name="compr_indica_rt_pj"
                                    value="{{ isset($resultado->compr_indica_rt_pj) ? $resultado->compr_indica_rt_pj : old('compr_indica_rt_pj') }}"
                                >
                                <label class="custom-file-label ml-0" for="customFile"><span class="text-secondary">Escolher arquivo</span></label>
                            </div>
                            <div class="input-group-append">
                                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </div>
                        @if($errors->has('compr_indica_rt_pj'))
                        <div class="invalid-feedback">
                            {{ $errors->first('compr_indica_rt_pj') }}
                        </div>
                        @endif
                    </div>
                </div>

                <label for="compr_inscr_cnpj">Comprovante de inscrição CNPJ *</label>
                <!-- Carrega os arquivos do bd com seus botoes de controle -->	
                @if(isset($resultado->compr_inscr_cnpj))
                <div class="ArquivoBD_inscr_cnpj">
                    <div class="form-row mb-2">
                        <div class="input-group col-sm mb-2-576">
                            <input 
                                type="text" 
                                class="form-control" 
                                value="{{-- $resultado->compr_inscr_cnpj --}}"
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
                <div class="Arquivo_inscr_cnpj">
                    <div class="form-row mb-2">
                        <div class="input-group col-sm mb-2-576">
                            <div class="custom-file">
                                <input 
                                    type="file" 
                                    class="custom-file-input files {{ $errors->has('compr_inscr_cnpj') ? 'is-invalid' : '' }}" 
                                    id="compr_indica_rt_pj"
                                    name="compr_indica_rt_pj"
                                    value="{{ isset($resultado->compr_inscr_cnpj) ? $resultado->compr_inscr_cnpj : old('compr_inscr_cnpj') }}"
                                >
                                <label class="custom-file-label ml-0" for="customFile"><span class="text-secondary">Escolher arquivo</span></label>
                            </div>
                            <div class="input-group-append">
                                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </div>
                        @if($errors->has('compr_inscr_cnpj'))
                        <div class="invalid-feedback">
                            {{ $errors->first('compr_inscr_cnpj') }}
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <div class="mt-3 float-right">
                    <button class="btn btn-primary" type="button">Salvar</button>
                    <a href="{{ route('prerepresentante.preregistro.view') }}" class="btn btn-default text-dark text-decoration-none ml-2">
                        Cancelar
                    </a>
                </div>

                <!-- se PJ, o botão enviar será na última aba -->
                @if(strlen($prerep->cpf_cnpj) == 11)
                <div class="linha-lg-mini"></div>
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
                        <div class="input-group col-sm mb-2-576">
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
                    <div class="form-row mb-2">
                        <div class="input-group col-sm mb-2-576 pl-0">
                            <div class="custom-file">
                                <input 
                                    type="file" 
                                    class="custom-file-input files {{-- $errors->has('anexos_socios') ? 'is-invalid' : '' --}}" 
                                    name="anexos_socios[]"
                                    value="{{-- old('compr_residencia') --}}"
                                >
                                <label class="custom-file-label"><span class="text-secondary">Escolher arquivo</span></label>
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

                <div class="linha-lg-mini"></div>

                <h5 class="bold mb-2">Comprovante de residência de todos os sócios *</h5>
                <!-- Carrega os arquivos do bd com seus botoes de controle -->	
                @if(isset($resultado->anexos_resid_socios))
                <div class="ArquivoBD_resid_socios">
                    <div class="form-row mb-2">
                        <div class="input-group col-sm mb-2-576">
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
                    <div class="form-row mb-2">
                        <div class="input-group col-sm mb-2-576 pl-0">
                            <div class="custom-file">
                                <input 
                                    type="file" 
                                    class="custom-file-input files {{-- $errors->has('anexos_resid_socios') ? 'is-invalid' : '' --}}" 
                                    name="anexos_resid_socios[]"
                                    value="{{-- old('compr_residencia') --}}"
                                >
                                <label class="custom-file-label"><span class="text-secondary">Escolher arquivo</span></label>
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
                <div class="linha-lg-mini"></div>
                <button class="btn btn-success" type="submit">Enviar</button>
            </div>
            @endif
        </div>
    </form>

</div>

@endsection