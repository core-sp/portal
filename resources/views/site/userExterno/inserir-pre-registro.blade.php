@extends('site.userExterno.app')

@section('content-user-externo')

@if(Session::has('message'))
    <div class="d-block w-100">
        <p class="alert {{ Session::get('class') }}">{{ Session::get('message') }}</p>
    </div>
@endif

<div class="representante-content w-100">

    <!-- Nav tabs -->
    <ul class="menu-registro nav nav-pills flex-column">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#parte1">
                Contabilidade - <i class="icon fa fa-check text-success"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte2">
                Dados Gerais - <i class="icon fa fa-times text-danger"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte3">
                Temporário 3
            </a>
        </li>
        @if(strlen($user->cpf_cnpj) == 14)
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte4">
                Temporário 4
            </a>
        </li>
        @endif
    </ul>

    <hr class="mb-0">

    <form method="POST" id="inserirRegistro" class="cadastroRepresentante">
        @csrf
        @if(isset($resultado->id))
            @method('PUT')
        @endif

        <!-- Tab panes -->
        <div class="tab-content">
            <!-- Tab 1 -->
            <div id="parte1" class="tab-pane container active"><br>
                <!-- Lembrar de passar o array com as variáveis -->
                @component('site.userExterno.componentes.pre-registro-etapa1')
                @endcomponent
            </div>
                
            <!-- Tab 2 -->
            <div id="parte2" class="tab-pane container fade"><br>
                @component('site.userExterno.componentes.pre-registro-etapa2', ['user' => $user, 'regionais' => $regionais])
                @endcomponent

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
            </div>

            <!-- Tab 3 -->
            <div id="parte3" class="tab-pane container fade"><br>
                @component('site.userExterno.componentes.pre-registro-etapa3')
                @endcomponent

                <div class="linha-lg-mini"></div>

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

                <!-- se PJ, o botão enviar será na última aba -->
                @if(strlen($user->cpf_cnpj) == 11)
                <div class="linha-lg-mini"></div>
                <button class="btn btn-success" type="submit">Enviar</button>
                @endif
            </div>

            <!-- Tab 4 -->
            @if(strlen($user->cpf_cnpj) == 14)
            <div id="parte4" class="tab-pane container fade"><br>

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
                            value="{{ $user->email }}"
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
                
                <p class="text-dark mb-3"><i class="fas fa-info-circle"></i> Limite de até {{ $totalFiles }} anexos para cada</p>

                <h5 class="bold mb-2">Documento de identidade de todos os sócios</h5>
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
                    <label for="anexos_socios">Cópia RG ou CNH *</label>
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

                <h5 class="bold mb-2">Comprovante de residência de todos os sócios</h5>
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

                <div class="linha-lg-mini"></div>
                <button class="btn btn-success" type="submit">Enviar para análise</button>
            </div>
            @endif
        </div>
    </form>
</div>

@endsection