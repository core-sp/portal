<p class="text-dark mb-3"><i class="fas fa-info-circle"></i> Limite de até {{ $totalFiles }} anexos</p>

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

<label class="mt-3" for="anexos">{{ array_search('path', $codAnexo) }} - Anexo *</label>
<!-- Carrega os arquivos do bd com seus botoes de controle -->	
@if(isset($resultado->anexos))
<div class="ArquivoBD_anexo">
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576">
            <input 
                type="text" 
                class="form-control" 
                value="{{-- $resultado->copia_identidade --}}"
                readonly
            />
            <div class="input-group-append">
                <a href="/downloadFile/" class="btn btn-primary Arquivo-Download" value="" ><i class="fas fa-download"></i></a>
                <a href="/excluirFile/" class="btn btn-danger Arquivo-Excluir" type="button" data-target=".myModal"><i class="fas fa-trash-alt"></i></a>
            </div>
        </div>
    </div>
</div>
@endif

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
                    value="{{-- isset($resultado->copia_identidade) ? $resultado->copia_identidade : old('copia_identidade') --}}"
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