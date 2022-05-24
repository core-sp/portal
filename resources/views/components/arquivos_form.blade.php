<!-- Input do arquivo -->
<div class="Arquivo_{{ $nome }}">
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576">
            <div class="custom-file">
                <input 
                    type="file" 
                    class="{{ $classes }} custom-file-input files {{ $errors->has('path') ? 'is-invalid' : '' }}" 
                    id="anexos"
                    name="path"
                    value=""
                />
                <label class="custom-file-label ml-0" for="customFile"><span class="text-secondary">Escolher arquivo</span></label>
            </div>
            <div class="input-group-append">
                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
            </div>
            @if($errors->has('path'))
            <div class="invalid-feedback" style="display:block">
                {{ $errors->first('path') }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- The Modal -->
<div class="modal fade" id="modalExcluirFile">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title"><i class="fas fa-trash-alt text-danger"></i> Excluir Arquivo</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            
            <!-- Modal body -->
            <div class="modal-body">
                Tem certeza que deseja excluir o anexo: <strong><span id="textoExcluir"></span></strong>?
            </div>
            
            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="Arquivo-Excluir btn btn-danger" value="">Sim</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>