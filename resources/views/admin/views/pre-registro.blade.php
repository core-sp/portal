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
        &nbsp;Após 1 mês com status <span class="badge badge-success">Aprovado</span>, os anexos serão <strong>excluídos</strong> automaticamente do sistema.
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
            $cont = -1;
        @endphp
        @foreach($resultado->getHistoricoStatus() as $status)
            @php
                $temp = explode(';', $status);
                $cont = $temp[0] == $resultado::STATUS_CORRECAO ? $cont + 1 : $cont;
            @endphp
            <li class="list-group-item pl-0">
                <span class="rounded p-1 bg{{ $resultado->getLabelStatus($temp[0]) }}">{{ $temp[0] }}</span> - {{ organizaData($temp[1]) }}
                @if(($temp[0] == $resultado::STATUS_CORRECAO) && (!empty($array_justificativas_hist)))
                <p class="mt-2 ml-0 mr-0 mb-0">&nbsp;&nbsp;&nbsp;&nbsp;<strong>
                    <i class="fas fa-user-edit text-success"></i> Justificativas:</strong></p>
                    @foreach($array_justificativas_hist[$cont] as $chave => $texto)
                    <span class="rounded p-1">
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <strong>{{ $chave }}:</strong> {{ $texto }}
                    </span>
                    <br>
                    @endforeach
                @endif
            </li>
        @endforeach
        </ul>
    </small>
    @endif
    
    <hr />

    <p class="font-weight-bolder">Documentos anexados pelo atendente após aprovação:</p>
    @if($resultado->isAprovado())
        @php
            $boleto = $resultado->temBoleto() ? $resultado->getBoleto() : null;
        @endphp
        <p>
            <i class="fas fa-exclamation-circle text-primary"></i>
            &nbsp;<i>Após anexar, o documento ficará disponível para o solicitante realizar download na área restrita.</i>
        </p>
        @if(isset($boleto))
            <i class="fas fa-paperclip"></i> <i>Boleto:</i> {{ $boleto->nome_original }}
            <a href="{{ route('preregistro.anexo.download', ['idPreRegistro' => $resultado->id, 'id' => $boleto->id]) }}" 
                class="btn btn-sm btn-primary ml-2" 
                target="_blank" 
            >
                Abrir
            </a>
            <a href="{{ route('preregistro.anexo.download', ['idPreRegistro' => $resultado->id, 'id' => $boleto->id]) }}" 
                class="btn btn-sm btn-primary ml-2" 
                download
            >
                <i class="fas fa-download"></i>
            </a>
            <br>
            <span class="mt-2"><small><i>Última atualização:</i> {{ formataData($boleto->updated_at) }}</small></span>
        @else
            <p>Sem boleto anexado.</p>
        @endif

    <form class="ml-1 mt-3" action="{{ route('preregistro.upload.doc', $resultado->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-row">
            <label>Anexar novo boleto:</label>
            <div class="custom-file">
                <input
                    type="file"
                    name="file"
                    class="custom-file-input {{ $errors->has('file') ? 'is-invalid' : '' }}"
                    id="doc_pre_registro"
                    accept=".pdf"
                    role="button"
                >
                <label class="custom-file-label" for="doc_pre_registro">Selecionar arquivo...</label>
                @if($errors->has('file'))
                <div class="invalid-feedback">
                    {{ $errors->first('file') }}
                </div>
                @endif
            </div>
        </div>

        <div class="form-row mt-2">
            <button type="submit" class="btn btn-sm btn-primary">Enviar</button>
        </div>
    </form>

    @else
    <p><i>Pré-registro não está aprovado.</i></p>
    @endif

    <hr />

    <div id="accordionPreRegistro" class="mt-3">
        <input type="hidden" name="idPreRegistro" value="{{ $resultado->id }}" />

        <div class="card">
            <a class="card-link" data-toggle="collapse" href="#parte1_PF_PJ">
                <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">
                    1. {{ $abas[0] }}
                    @if(!empty(array_intersect_key($codigos[0], $resultado->getCamposEditados())))
                    <span class="badge badge-danger ml-2">Campos alterados</span>
                    @endif
                </div>
            </a>
            <div id="parte1_PF_PJ" class="collapse" data-parent="#accordionPreRegistro">
                @include('admin.inc.pre-registro-parte1_PF_PJ')
            </div>
        </div>

        <div class="card">
            <a class="card-link" data-toggle="collapse" href="#parte2_PF_PJ">
                <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">
                    2. {{ $abas[1] }}
                    @if(!empty(array_intersect_key($codigos[1], $resultado->getCamposEditados())))
                    <span class="badge badge-danger ml-2">Campos alterados</span>
                    @endif
                </div>
            </a>
            <div id="parte2_PF_PJ" class="collapse" data-parent="#accordionPreRegistro">
                @include('admin.inc.pre-registro-parte2_PF_PJ')
            </div>
        </div>
        
        <div class="card">
            <a class="card-link" data-toggle="collapse" href="#parte3_PF_PJ">
                <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">
                    3. {{ $abas[2] }}
                    @if(!empty(array_intersect_key($codigos[2], $resultado->getCamposEditados())))
                    <span class="badge badge-danger ml-2">Campos alterados</span>
                    @endif
                </div>
            </a>
            <div id="parte3_PF_PJ" class="collapse" data-parent="#accordionPreRegistro">
                @include('admin.inc.pre-registro-parte3_PF_PJ')
            </div>
        </div>
        
        @if(!$resultado->userExterno->isPessoaFisica())
        <div class="card">
            <a class="card-link" data-toggle="collapse" href="#parte4_PJ">
                <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">
                    4. {{ $abas[3] }}
                    @if(!empty(array_intersect_key($codigos[3], $resultado->getCamposEditados())))
                    <span class="badge badge-danger ml-2">Campos alterados</span>
                    @endif
                </div>
            </a>
            <div id="parte4_PJ" class="collapse" data-parent="#accordionPreRegistro">
                @include('admin.inc.pre-registro-parte4_PJ')
            </div>
        </div>
        @endif
        
        <div class="card">
            <a class="card-link" data-toggle="collapse" href="#parte4_PF_parte5_PJ">
                <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">
                    5. {{ $abas[4] }}
                    @if(!empty(array_intersect_key($codigos[4], $resultado->getCamposEditados())))
                    <span class="badge badge-danger ml-2">Campos alterados</span>
                    @endif
                </div>
            </a>
            <div id="parte4_PF_parte5_PJ" class="collapse" data-parent="#accordionPreRegistro">
                @include('admin.inc.pre-registro-parte4_PF_parte5_PJ')
            </div>
        </div>

        <div class="card">
            <a class="card-link" data-toggle="collapse" href="#parte5_PF_parte6_PJ">
                <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">
                    6. {{ $abas[5] }}
                    @if(!empty(array_intersect_key($codigos[5], $resultado->getCamposEditados())))
                    <span class="badge badge-success ml-2">Novos anexos</span>
                    @endif
                </div>
            </a>
            <div id="parte5_PF_parte6_PJ" class="collapse" data-parent="#accordionPreRegistro">
                @include('admin.inc.pre-registro-parte5_PF_parte6_PJ')
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

