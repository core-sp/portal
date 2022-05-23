<p class="text-dark mb-2"><i class="fas fa-info-circle text-primary"></i> <strong>Atenção!</strong>
    <br>
    <span class="ml-3"><strong>*</strong> Limite de até {{ $totalFiles }} anexos</span>
    <br>
    <span class="ml-3"><strong>*</strong> Somente arquivos com extensão: .pdf, .jpg, .jpeg, .png</span>
</p>

<div class="linha-lg-mini"></div>

@if(strlen($resultado->userExterno->cpf_cnpj) == 11)
    <p>Cópia RG</p>
    <p>Cópia do CPF / CNH</p>
    <p>Comprovante de residência (dos últimos 3 meses)</p>
    <p>Certidão de quitação eleitoral</p>
    <p>Cerificado de reservista ou dispensa (somente para o sexo masculino)</p>

@else

    <p>Comprovante de inscrição CNPJ</p>
    <p>Contrato Social</p>
    <p>Declaração Termo de indicação RT ou Procuração</p>
    <p>Certidão de quitação eleitoral</p>
    <p class="bold mb-2 mt-1">Documentos de todos os sócios: </p>
    <p class="ml-3">Cópia RG</p>
    <p class="ml-3">Cópia CPF / CNH</p>
    <p class="ml-3">Comprovante de Residência (dos últimos 3 meses)</p>
    <p class="ml-3">Certidão de quitação eleitoral</p>
    <p class="ml-3">Cerificado de reservista ou dispensa (somente para o sexo masculino)</p>

@endif

<!-- Carrega os arquivos do bd com seus botoes de controle -->	
<label class="mt-3" for="anexos">{{ array_search('path', $codAnexo) }} - Anexo *</label>
@foreach($resultado->anexos as $anexo)
<div class="ArquivoBD_anexo">
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576">
            <input 
                type="text" 
                class="form-control" 
                value="{{ $anexo->nome_original }}"
                readonly
            />
            <div class="input-group-append">
                <a href="{{ route('externo.preregistro.anexo.download', $anexo->id) }}" 
                    class="btn btn-primary Arquivo-Download" 
                    value="" 
                    target="_blank" 
                >
                    <i class="fas fa-download"></i>
                </a>
                <button class="btn btn-danger modalExcluir"
                    value="{{ $anexo->id }}"
                    type="button" 
                    data-toggle="modal"
                    data-target="#modalExcluirFile"
                    data-backdrop="static"
                >
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- Input do arquivo -->
<div class="Arquivo_anexo">
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576">
            <div class="custom-file">
                <input 
                    type="file" 
                    class="{{ $classes[0] }} {{ array_search('path', $codAnexo) }} custom-file-input files {{ $errors->has('anexos') ? 'is-invalid' : '' }}" 
                    id="anexos"
                    name="path"
                    value=""
                />
                <label class="custom-file-label ml-0" for="customFile"><span class="text-secondary">Escolher arquivo</span></label>
            </div>
            <div class="input-group-append">
                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
            </div>
        </div>
        @if($errors->has('anexos'))
        <div class="invalid-feedback">
            {{ $errors->first('anexos') }}
        </div>
        @endif
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