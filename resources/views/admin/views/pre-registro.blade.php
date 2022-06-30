<div id="accordion" class="mt-3">
    <input type="hidden" name="idPreRegistro" value="{{ $resultado->id }}" />

    <div class="card">
        <a class="card-link" data-toggle="collapse" href="#parte1_PF_PJ">
            <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder">
                {{ $abas[0] }}
            </div>
        </a>
        <div id="parte1_PF_PJ" class="collapse show" data-parent="#accordion">
            @include('admin.inc.pre-registro-parte1_PF_PJ', [
                'cod' => $codigos[$classes[1]]
            ])
        </div>
    </div>

    <div class="card">
        <a class="card-link" data-toggle="collapse" href="#parte2_PF_PJ">
            <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder">
                {{ $abas[1] }}
            </div>
        </a>
        <div id="parte2_PF_PJ" class="collapse" data-parent="#accordion">
            <div class="card-body">
                @include('admin.inc.pre-registro-parte2_PF_PJ', [
                    'codPre' => $codigos[$classes[4]],
                    'codCpf' => $codigos[$classes[2]],
                    'codCnpj' => $codigos[$classes[3]]
                ])
            </div>
        </div>
    </div>

    <div class="card">
        <a class="card-link" data-toggle="collapse" href="#parte3_PF_PJ">
            <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder">
                {{ $abas[2] }}
            </div>
        </a>
        <div id="parte3_PF_PJ" class="collapse" data-parent="#accordion">
            <div class="card-body">
                @include('admin.inc.pre-registro-parte3_PF_PJ', [
                    'codPre' => $codigos[$classes[4]],
                    'codCnpj' => $codigos[$classes[3]]
                ])
            </div>
        </div>
    </div>

    @if(!$resultado->userExterno->isPessoaFisica())
    <div class="card">
        <a class="card-link" data-toggle="collapse" href="#parte4_PJ">
            <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder">
                {{ $abas[3] }}
            </div>
        </a>
        <div id="parte4_PJ" class="collapse" data-parent="#accordion">
            <div class="card-body">
                @include('admin.inc.pre-registro-parte4_PJ', [
                    'codRT' => $codigos[$classes[5]]
                ])
            </div>
        </div>
    </div>
    @endif

    <div class="card">
        <a class="card-link" data-toggle="collapse" href="#parte4_PF_parte5_PJ">
            <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder">
                {{ $abas[4] }}
            </div>
        </a>
        <div id="parte4_PF_parte5_PJ" class="collapse" data-parent="#accordion">
            <div class="card-body">
                @include('admin.inc.pre-registro-parte4_PF_parte5_PJ', [
                    'codPre' => $codigos[$classes[4]]
                ])
            </div>
        </div>
    </div>

</div>

<div class="modal" id="modalJustificativaPreRegistro">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title"><i class="fas fa-edit"></i> {{-- verifica se existe justificativa ? 'Adicionar' : 'Editar' --}} justificativa</h4>
            </div>
                
            <!-- Modal body -->
            <div class="modal-body">
                <div class="form-group">
                    <textarea class="form-control" rows="5" maxlength="500" value="">{{-- carrega se existe justificativa --}}</textarea>
                    <small>
                        <span class="text-muted" id="contChar"></span>
                    </small>
                </div>
            </div>
                
            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="submitJustificativaPreRegistro" value="">Salvar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
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

