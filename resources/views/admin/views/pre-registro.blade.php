<div class="card-body">
    <div id="accordionPreRegistro" class="mt-3">
        <input type="hidden" name="idPreRegistro" value="{{ $resultado->id }}" />

        <div class="card">
            <a class="card-link" data-toggle="collapse" href="#parte1_PF_PJ">
                <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">
                    {{ $abas[0] }} 
                </div>
            </a>
            <div id="parte1_PF_PJ" class="collapse show" data-parent="#accordionPreRegistro">
                @include('admin.inc.pre-registro-parte1_PF_PJ', [
                    'cod' => $codigos[$classes[1]]
                ])
            </div>
        </div>

        <div class="card">
            <a class="card-link" data-toggle="collapse" href="#parte2_PF_PJ">
                <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">
                    {{ $abas[1] }}
                </div>
            </a>
            <div id="parte2_PF_PJ" class="collapse" data-parent="#accordionPreRegistro">
                @include('admin.inc.pre-registro-parte2_PF_PJ', [
                    'codPre' => $codigos[$classes[4]],
                    'codCpf' => $codigos[$classes[2]],
                    'codCnpj' => $codigos[$classes[3]]
                ])
            </div>
        </div>

        <div class="card">
            <a class="card-link" data-toggle="collapse" href="#parte3_PF_PJ">
                <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">
                    {{ $abas[2] }}
                </div>
            </a>
            <div id="parte3_PF_PJ" class="collapse" data-parent="#accordionPreRegistro">
                @include('admin.inc.pre-registro-parte3_PF_PJ', [
                    'codPre' => $codigos[$classes[4]],
                    'codCnpj' => $codigos[$classes[3]]
                ])
            </div>
        </div>

        @if(!$resultado->userExterno->isPessoaFisica())
        <div class="card">
            <a class="card-link" data-toggle="collapse" href="#parte4_PJ">
                <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">
                    {{ $abas[3] }}
                </div>
            </a>
            <div id="parte4_PJ" class="collapse" data-parent="#accordionPreRegistro">
                @include('admin.inc.pre-registro-parte4_PJ', [
                    'codRT' => $codigos[$classes[5]]
                ])
            </div>
        </div>
        @endif

        <div class="card">
            <a class="card-link" data-toggle="collapse" href="#parte4_PF_parte5_PJ">
                <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">
                    {{ $abas[4] }}
                </div>
            </a>
            <div id="parte4_PF_parte5_PJ" class="collapse" data-parent="#accordionPreRegistro">
                @include('admin.inc.pre-registro-parte4_PF_parte5_PJ', [
                    'codPre' => $codigos[$classes[4]]
                ])
            </div>
        </div>

        <div class="card">
            <a class="card-link" data-toggle="collapse" href="#parte5_PF_parte6_PJ">
                <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">
                    {{ $abas[5] }}
                </div>
            </a>
            <div id="parte5_PF_parte6_PJ" class="collapse" data-parent="#accordionPreRegistro">
                @include('admin.inc.pre-registro-parte5_PF_parte6_PJ', [
                    'codAnexo' => $codigos[$classes[0]]
                ])
            </div>
        </div>

    </div>

    <div class="row ml-0">
        <form method="POST" action="{{ route('preregistro.update.aprovado', $resultado->id) }}" class="">
            @csrf
            @method('PUT')
            <button type="submit"
                class="btn btn-success {{ isset($resultado->justificativa) ? '' : 'disabled' }}"
                value="aprovado"
            >
                <i class="fas fa-check"></i> Aprovar
            </button>
        </form>

        <form method="POST" action="{{ route('preregistro.update.enviar.correcao', $resultado->id) }}" class="ml-3">
            @csrf
            @method('PUT')
            <button type="submit"
                class="btn btn-warning"
                value="correcao"
            >
                <i class="fas fa-times"></i> Enviar para correção
            </button>
        </form>

        <form method="POST" action="{{ route('preregistro.update.negado', $resultado->id) }}" class="ml-3" id="submitNegarPR">
            @csrf
            @method('PUT')
            <button type="submit"
                class="btn btn-danger justificativaPreRegistro"
                value="negado"
            >
                <i class="fas fa-ban"></i> Negar
            </button>
        </form>

    </div>

    <div class="col mt-4 pl-0">
        <span class="font-weight-bolder">Atualizado por: </span>
        <span id="userPreRegistro">{{ $resultado->user->nome }}</span>
        <span class="font-weight-bolder">, no dia </span>
        <span id="atualizacaoPreRegistro">{{ $resultado->updated_at->format('d\/m\/Y, \à\s H:i:s') }}</span>
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
                <button type="button" class="btn btn-success" id="submitJustificativaPreRegistro" value="">Salvar</button>
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

