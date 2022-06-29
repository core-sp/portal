<div id="accordion" class="mt-3">
    <input type="hidden" name="idPreRegistro" value="{{ $resultado->id }}" />
    <div class="card">
        <a class="card-link" data-toggle="collapse" href="#contabilidade">
            <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder">
                {{ $abas[0] }}
            </div>
        </a>
        <div id="contabilidade" class="collapse show" data-parent="#accordion">
            @include('admin.inc.pre-registro-contabil', [
                'cod' => $codigos[$classes[1]]
            ])
        </div>
    </div>
    <div class="card">
        <a class="card-link" data-toggle="collapse" href="#dados-gerais">
            <div class="card-header bg-secondary text-center text-uppercase font-weight-bolder">
                {{ $abas[1] }}
            </div>
        </a>
        <div id="dados-gerais" class="collapse" data-parent="#accordion">
            <div class="card-body">
                dados gerais
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
                    <textarea class="form-control" rows="5">{{-- carrega se existe justificativa --}}</textarea>
                </div>
            </div>
                
            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="submitJustificativaPreRegistro" value="">Salvar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</a>
            </div>
        </div>
    </div>
</div>

