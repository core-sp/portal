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
    <hr />
    
    @if($resultado->status == $resultado::STATUS_NEGADO)
    <p class="mb-4">
        <strong class="text-danger">Justificativa:</strong>
        {{ $resultado->getJustificativaNegado() }}
    </p>
    <hr />
    @endif

    @if(isset($resultado->historico_status))
    <p class="font-weight-bolder mb-0">Histórico de atualizações de status:</p>
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
    
    <hr />

    <p class="font-weight-bolder">Documentos gerenciados pelo atendimento após aprovação:</p>
    @if($resultado->isAprovado())
        <p>
            <i class="fas fa-exclamation-circle text-primary"></i>
            &nbsp;<i>Após anexar, o documento ficará disponível para o solicitante realizar download na área restrita.</i>
        </p>

    <fieldset class="border border-primary pb-2 pl-3 pr-3 mt-2">
        <legend class="w-auto pr-2">Anexar Documentos</legend>

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
                    <button type="submit" class="btn btn-sm btn-primary">Enviar</button>
                </div>
            </div>
        </form>

    </fieldset>

    <fieldset class="border border-primary pb-2 pl-3 pr-3 mt-2">
        <legend class="w-auto pr-2">Documentos Anexados</legend>

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
            <p>Sem documento anexado.</p>
        @endif

    </fieldset>

    @else
    <p><i>Pré-registro não está aprovado.</i></p>
    @endif

    <hr />

    <div id="accordionPreRegistro" class="mt-3">
        <input type="hidden" name="idPreRegistro" value="{{ $resultado->id }}" />

        <div class="card">
            <a class="card-link" data-toggle="collapse" href="#parte_contabilidade">
                <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">
                    1. {{ $abas[0] }}
                    @if(!empty(array_intersect_key($codigos[0], $resultado->getCamposEditados())))
                    <span class="badge badge-danger ml-2">Campos alterados</span>
                    @endif
                </div>
            </a>
            <div id="parte_contabilidade" class="collapse" data-parent="#accordionPreRegistro">
                @include('admin.inc.pre-registro-contabilidade', ['nome_campos' => $codigos[0]])
            </div>
        </div>

        <div class="card">
            <a class="card-link" data-toggle="collapse" href="#parte_dados_gerais">
                <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">
                    2. {{ $abas[1] }}
                    @if(!empty(array_intersect_key($codigos[1], $resultado->getCamposEditados())))
                    <span class="badge badge-danger ml-2">Campos alterados</span>
                    @endif
                </div>
            </a>
            <div id="parte_dados_gerais" class="collapse" data-parent="#accordionPreRegistro">
                @include('admin.inc.pre-registro-dados-gerais', ['nome_campos' => $codigos[1]])
            </div>
        </div>
        
        <div class="card">
            <a class="card-link" data-toggle="collapse" href="#parte_endereco">
                <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">
                    3. {{ $abas[2] }}
                    @if(!empty(array_intersect_key($codigos[2], $resultado->getCamposEditados())))
                    <span class="badge badge-danger ml-2">Campos alterados</span>
                    @endif
                </div>
            </a>
            <div id="parte_endereco" class="collapse" data-parent="#accordionPreRegistro">
                @include('admin.inc.pre-registro-endereco', ['nome_campos' => $codigos[2]])
            </div>
        </div>
        
        @if(!$resultado->userExterno->isPessoaFisica())
        <div class="card">
            <a class="card-link" data-toggle="collapse" href="#parte_contato_rt">
                <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">
                    4. {{ $abas[3] }}
                    @if(!empty(array_intersect_key($codigos[3], $resultado->getCamposEditados())))
                    <span class="badge badge-danger ml-2">Campos alterados</span>
                    @endif
                </div>
            </a>
            <div id="parte_contato_rt" class="collapse" data-parent="#accordionPreRegistro">
                @include('admin.inc.pre-registro-contato-rt', ['nome_campos' => $codigos[3]])
            </div>
        </div>

        <!-- socios -->
        <div class="card">
            <a class="card-link" data-toggle="collapse" href="#parte_socios">
                <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">
                    5. {{ $abas[4] }}
                    @if(!empty(array_filter($resultado->getCamposEditados(), function($key){
                        return strpos($key, '_socio') !== false;
                    }, ARRAY_FILTER_USE_KEY)))
                    <span class="badge badge-danger ml-2">Campos alterados</span>
                    @endif
                </div>
            </a>
            <div id="parte_socios" class="collapse" data-parent="#accordionPreRegistro">
                @include('admin.inc.pre-registro-socios', ['nome_campos' => $codigos[4]])
            </div>
        </div>
        @endif
        
        <div class="card">
            <a class="card-link" data-toggle="collapse" href="#parte_canal_relacionamento">
                <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">
                    6. {{ $abas[5] }}
                    @if(!empty(array_intersect_key($codigos[5], $resultado->getCamposEditados())))
                    <span class="badge badge-danger ml-2">Campos alterados</span>
                    @endif
                </div>
            </a>
            <div id="parte_canal_relacionamento" class="collapse" data-parent="#accordionPreRegistro">
                @include('admin.inc.pre-registro-canal-relacionamento', ['nome_campos' => $codigos[5]])
            </div>
        </div>

        <div class="card">
            <a class="card-link" data-toggle="collapse" href="#parte_anexos">
                <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">
                    7. {{ $abas[6] }}
                    @if(!empty(array_intersect_key($codigos[6], $resultado->getCamposEditados())))
                    <span class="badge badge-success ml-2">Novos anexos</span>
                    @endif
                </div>
            </a>
            <div id="parte_anexos" class="collapse" data-parent="#accordionPreRegistro">
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
            <button type="submit"
                class="btn btn-success {{ isset($resultado->justificativa) ? '' : 'disabled' }}"
                value="aprovado"
            >
                <i class="fas fa-check"></i> Aprovar
            </button>
        </form>

        <form method="POST" action="{{ route('preregistro.update.status', $resultado->id) }}" class="ml-3">
            @csrf
            @method('PUT')
            <input type="hidden" name="situacao" value="corrigir">
            <button type="submit"
                class="btn btn-warning"
                value="correcao"
            >
                <i class="fas fa-times"></i> Enviar para correção
            </button>
        </form>

        <form method="POST" action="{{ route('preregistro.update.status', $resultado->id) }}" class="ml-3" id="submitNegarPR">
            @csrf
            @method('PUT')
            <input type="hidden" name="situacao" value="negar">
            <button type="submit"
                class="btn btn-danger justificativaPreRegistro"
                value="negado"
            >
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

