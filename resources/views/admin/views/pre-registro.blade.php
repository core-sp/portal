<div class="card-body">

    @if($errors->any())
    <div class="alert alert-dismissible alert-danger">
        <ul class="m-0 p-0">
        @foreach ($errors->all() as $error)
            @if(strlen($error) > 0)
            <li class="list-unstyled"><i class="icon fa fa-ban"></i>{{ $error }}</li>
            @endif
        @endforeach
        </ul>
    </div>
    @endif

    <p>
        <i class="fas fa-exclamation-circle text-primary"></i>
        &nbsp;Após 1 mês da última atualização com status <span class="badge badge-success">Aprovado</span>, os anexos serão <strong>excluídos</strong> automaticamente do sistema.
    </p>
    <p>
        <i class="fas fa-exclamation-circle text-primary"></i>
        &nbsp;Após atualizar o status para <span class="badge badge-danger">Negado</span>, os anexos serão <strong>excluídos</strong> automaticamente do sistema.
    </p>
    <hr class="border border-info"/>
    
    @if($resultado->status == $resultado::STATUS_NEGADO)
    <p class="mb-4">
        <strong class="text-danger">Justificativa:</strong>
        {{ $resultado->getJustificativaNegado() }}
    </p>
    <hr class="border border-info"/>
    @endif
    
    <div class="mt-4 mb-4">
        <ul class="nav nav-pills border border-secondary rounded bg-light" role="tablist">
            <button class="btn btn-link hide_menu"><i class="fas fa-eye-slash"></i></button>
            <li class="nav-item text-uppercase font-weight-bolder">
                <a class="nav-link" data-toggle="pill" href="#historico_status">
                    Histórico do status
                </a>
            </li>
            <li class="nav-item text-uppercase font-weight-bolder">
                <a class="nav-link" data-toggle="pill" href="#anexar_doc">
                    Anexar Documentos
                </a>
            </li>
            <li class="nav-item text-uppercase font-weight-bolder">
                <a class="nav-link" data-toggle="pill" href="#docs_anexados">
                    Documentos Anexados
                </a>
            </li>
        </ul>
    </div>

    <div class="tab-content">

        <div id="historico_status" class="tab-pane">
        @if(isset($resultado->historico_status))
            <small>
                <ul class="list-group list-group-flush mb-2">
                @php
                    $array_justificativas_hist = $resultado->getHistoricoJustificativas();
                @endphp
            
                @foreach($resultado->getHistoricoStatus() as $data => $status)
                    <li class="list-group-item pl-0">
                        <span class="rounded p-1 bg{{ $resultado->getLabelStatus($status) }}">{{ $status }}</span> - {{ organizaData($data) }}
                        @if(($status == $resultado::STATUS_CORRECAO) && isset($array_justificativas_hist[$data]))
                        <p class="mt-2 ml-0 mr-0 mb-0">&nbsp;&nbsp;&nbsp;&nbsp;<strong>
                            <i class="fas fa-user-edit text-success"></i> Justificativas:</strong>
                            @foreach($array_justificativas_hist[$data] as $chave => $campo)
                            <button 
                                class="btn btn-link btn-sm pb-0 pt-0 textoJustHist" 
                                value="{{ route('externo.preregistro.justificativa.view', ['preRegistro' => $resultado->id, 'campo' => $campo, 'data_hora' => urlencode($data)]) }}"
                            >
                                <strong>{{ $chave }}</strong>
                            </button>
                            {{ $loop->last ? '' : '|' }} 
                            @endforeach
                        </p>
                        @endif
                    </li>
                @endforeach
                </ul>
            </small>
        @endif
        </div>

        <div id="anexar_doc" class="tab-pane">
        @if($resultado->isAprovado())
            <p>
                <i class="fas fa-exclamation-circle text-primary"></i>
                &nbsp;<i>Após anexar, o documento ficará disponível para o solicitante realizar download na área restrita.</i>
            </p>

            <form class="ml-1" action="{{ route('preregistro.upload.doc', $resultado->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <div class="col">
                        <label class="mr-2 mb-0">Tipo de documento a ser anexado:</label>
                        @foreach($tipos_doc as $tipo)
                        <div class="form-check-inline">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="tipo" value="{{ $tipo }}">{{ ucfirst($tipo) }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="form-group mt-2">
                    <div class="col">
                        <label>Anexar novo documento <i>(irá substituir caso já exista)</i>:</label>
                        <div class="custom-file">
                            <input
                                type="file"
                                name="file"
                                class="custom-file-input {{ $errors->has('file') ? 'is-invalid' : '' }}"
                                id="doc_pre_registro"
                                accept="application/pdf"
                                role="button"
                            >
                            <label class="custom-file-label" for="doc_pre_registro">Selecionar arquivo...</label>
                        </div>
                    </div>
                    <div class="col mt-2">
                        <button type="button" id="form-anexo-docs" class="btn btn-sm btn-primary">Enviar</button>
                    </div>
                </div>
            </form>
        @else
            <p><i><b>Pré-registro não está aprovado.</b></i></p>
        @endif
        </div>

        <div id="docs_anexados" class="tab-pane">
        @if($docs_atendimento->isNotEmpty())
            @foreach($docs_atendimento as $doc)
            <div class="mt-2">
                <p>
                    <i class="fas fa-paperclip"></i>
                    <span class="font-weight-bolder ml-1">{{ ucfirst($doc->tipo) }}: </span>
                    <a href="{{ route('preregistro.anexo.download', ['idPreRegistro' => $resultado->id, 'id' => $doc->id]) }}" 
                        class="ml-2" 
                        target="_blank" 
                    >
                        <u>{{ $doc->id }} - {{ $doc->nome_original }}</u>
                    </a>
                    <a href="{{ route('preregistro.anexo.download', ['idPreRegistro' => $resultado->id, 'id' => $doc->id]) }}" 
                        class="btn btn-sm btn-primary ml-2" 
                        download
                    >
                        <i class="fas fa-download"></i>
                    </a>
                    <span class="ml-2"><small><i>Última atualização:</i> {{ formataData($doc->updated_at) }}</small></span>
                </p>
            </div>
            {!! $loop->last ? '' : '<hr />' !!}

            @endforeach
        @else
            <p><i><b>Sem documento anexado.</b></i></p>
        @endif
        </div>
    </div>

    <hr class="border border-info"/>

    <h5 class="font-weight-bolder mb-3">Dados da solicitação:</h5>

    <p>
        <b><i>Legenda:</i></b>
        <i class="fas fa-circle text-danger ml-1 mr-1"></i> = <span class="badge badge-sm badge-danger ml-1 mr-1">Campos alterados</span>
        |
        <i class="fas fa-circle text-warning ml-1 mr-1"></i> = <span class="badge badge-sm badge-warning ml-1 mr-1">Justificado</span>
        |
        <i class="fas fa-circle text-success ml-1 mr-1"></i> = <span class="badge badge-sm badge-success ml-1 mr-1">Novo anexo</span>
    </p>

    <div id="accordionPreRegistro" class="mt-4 mb-4">
        <input type="hidden" name="idPreRegistro" value="{{ $resultado->id }}" />

        <!-- Nav pills -->
        <ul class="nav nav-pills border border-secondary rounded bg-light" role="tablist">
            <button class="btn btn-link hide_menu"><i class="fas fa-eye-slash"></i></button>
            <li class="nav-item text-center text-uppercase font-weight-bolder menuPR">
                <a class="nav-link" data-toggle="pill" href="#parte_contabilidade">
                    1. {{ $abas[0] }}
                </a>
                @if(!empty(array_intersect_key($codigos[0], $resultado->getCamposEditados())))
                <i class="fas fa-circle text-danger mr-1"></i>
                @endif
            </li>
            <li class="nav-item text-center text-uppercase font-weight-bolder menuPR">
                <a class="nav-link" data-toggle="pill" href="#parte_dados_gerais">
                    2. {{ $abas[1] }}
                </a>
                @if(!empty(array_intersect_key($codigos[1], $resultado->getCamposEditados())))
                <i class="fas fa-circle text-danger mr-1"></i>
                @endif
            </li>
            <li class="nav-item text-center text-uppercase font-weight-bolder menuPR">
                <a class="nav-link" data-toggle="pill" href="#parte_endereco">
                    3. {{ $abas[2] }}
                </a>
                @if(!empty(array_intersect_key($codigos[2], $resultado->getCamposEditados())))
                <i class="fas fa-circle text-danger mr-1"></i>
                @endif
            </li>

            @if(!$resultado->userExterno->isPessoaFisica())
            <li class="nav-item text-center text-uppercase font-weight-bolder menuPR">
                <a class="nav-link" data-toggle="pill" href="#parte_contato_rt">
                    4. {{ $abas[3] }}
                </a>
                @if(!empty(array_intersect_key($codigos[3], $resultado->getCamposEditados())))
                <i class="fas fa-circle text-danger mr-1"></i>
                @endif
            </li>
            <li class="nav-item text-center text-uppercase font-weight-bolder menuPR">
                <a class="nav-link" data-toggle="pill" href="#parte_socios">
                    5. {{ $abas[4] }}
                </a>
                @if(!empty(array_filter($resultado->getCamposEditados(), function($key){
                    return strpos($key, '_socio') !== false;
                }, ARRAY_FILTER_USE_KEY)))
                <i class="fas fa-circle text-danger mr-1"></i>
                @endif
            </li>
            @endif

            <li class="nav-item text-center text-uppercase font-weight-bolder menuPR">
                <a class="nav-link" data-toggle="pill" href="#parte_canal_relacionamento">
                    6. {{ $abas[5] }}                    
                </a>
                @if(!empty(array_intersect_key($codigos[5], $resultado->getCamposEditados())))
                <i class="fas fa-circle text-danger mr-1"></i>
                @endif
            </li>
            <li class="nav-item text-center text-uppercase font-weight-bolder menuPR">
                <a class="nav-link" data-toggle="pill" href="#parte_anexos">
                    7. {{ $abas[6] }}
                </a>
                @if(!empty(array_intersect_key($codigos[6], $resultado->getCamposEditados())))
                <i class="fas fa-circle text-success mr-1"></i>
                @endif
            </li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">

            <div id="parte_contabilidade" class="tab-pane"><br>
                @include('admin.inc.pre-registro-contabilidade', ['nome_campos' => $codigos[0]])
            </div>
            <div id="parte_dados_gerais" class="tab-pane fade"><br>
                @include('admin.inc.pre-registro-dados-gerais', ['nome_campos' => $codigos[1]])
            </div>
            <div id="parte_endereco" class="tab-pane fade"><br>
                @include('admin.inc.pre-registro-endereco', ['nome_campos' => $codigos[2]])
            </div>

            @if(!$resultado->userExterno->isPessoaFisica())
            <div id="parte_contato_rt" class="tab-pane fade"><br>
                @include('admin.inc.pre-registro-contato-rt', ['nome_campos' => $codigos[3]])
            </div>
            <div id="parte_socios" class="tab-pane fade"><br>
                @include('admin.inc.pre-registro-socios', ['nome_campos' => $codigos[4]])
            </div>
            @endif

            <div id="parte_canal_relacionamento" class="tab-pane fade"><br>
                @include('admin.inc.pre-registro-canal-relacionamento', ['nome_campos' => $codigos[5]])
            </div>
            <div id="parte_anexos" class="tab-pane fade"><br>
                @include('admin.inc.pre-registro-anexos', ['nome_campos' => $codigos[6]])
            </div>
        </div>

    </div>

</div>

<div class="card-footer">
    <div class="row ml-0">

    @if($resultado->atendentePodeEditar())
        <form method="POST" action="{{ route('preregistro.update.status', $resultado->id) }}" class="">
            @csrf
            @method('PUT')
            <input type="hidden" name="situacao" value="aprovar">
            <button type="submit" class="btn btn-success" {{ isset($resultado->justificativa) ? '' : 'disabled' }} id="submitAprovarPR">
                <i class="fas fa-check"></i> Aprovar
            </button>
        </form>

        <form method="POST" action="{{ route('preregistro.update.status', $resultado->id) }}" class="ml-3">
            @csrf
            @method('PUT')
            <input type="hidden" name="situacao" value="corrigir">
            <button type="submit" class="btn btn-warning" id="submitCorrigirPR">
                <i class="fas fa-times"></i> Enviar para correção
            </button>
        </form>

        <form method="POST" action="{{ route('preregistro.update.status', $resultado->id) }}" class="ml-3">
            @csrf
            @method('PUT')
            <input type="hidden" name="situacao" value="negar">
            <button type="button" class="btn btn-danger" id="submitNegarPR">
                <i class="fas fa-ban"></i> Negar
            </button>
        </form>
        @endif

        <div class="col d-flex align-items-end justify-content-end">
            <span class="font-weight-bolder">Atualizado por:&nbsp;</span>
            <span id="userPreRegistro">{{ isset($resultado->user->nome) ? $resultado->user->nome : '--------' }}</span>
            <span class="font-weight-bolder">, no dia&nbsp;</span>
            <span id="atualizacaoPreRegistro">{{ $resultado->updated_at->format('d\/m\/Y, \à\s H:i:s') }}</span>
        </div>
    </div>

</div>

<div class="modal" id="modalJustificativaPreRegistro">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title"><i class="fas fa-edit"></i><span id="titulo"></span></h4>
            </div>
                
            <!-- Modal body -->
            <div class="modal-body">
                <div class="form-group">
                    <textarea class="form-control" rows="5" maxlength="500" value=""></textarea>
                    <small class="text-muted">
                        <span id="contChar"></span> caracteres restantes
                    </small>
                </div>
            </div>
                
            <!-- Modal footer -->
            <div class="modal-footer">
                @if($resultado->atendentePodeEditar())
                <button type="button" class="btn btn-success" id="submitJustificativaPreRegistro" value="">Salvar</button>
                @endif
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- The Modal Loading acionado via ajax -->
<div class="modal" id="modalLoadingPreRegistro">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal body -->
            <div id="modalLoadingBody" class="modal-body text-center"></div>
        </div>
    </div>
</div>

<script type="module" src="{{ asset('/js/interno/modulos/pre-registro.js?'.hashScriptJs()) }}" id="modulo-pre-registro" class="modulo-editar"></script>

