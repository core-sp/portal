<!-- Carrega os arquivos do bd com seus botoes de controle -->	
<div class="ArquivoBD_{{ $nome }}" style="{{ $display }}">
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576">
            <input 
                type="text" 
                class="form-control NomeFile" 
                value="{{ $nome_file }}"
                readonly
            />
            <div class="input-group-append">
                
                <a href="{{ $rota_download }}" 
                    class="btn btn-info Arquivo-Abrir" 
                    value="" 
                    target="_blank" 
                    @if(strpos(request()->header('user-agent'), 'mobile') || (isset($extensao) && !in_array($extensao, ['jpg', 'jpeg', 'png', 'pdf'])))
                    style="display:none;"
                    @endif
                >
                    Abrir
                </a>
                
                <a href="{{ $rota_download }}" 
                    class="btn btn-primary Arquivo-Download" 
                    download
                >
                    <i class="fas fa-download"></i>
                </a>
                @if($podeExcluir)
                <button class="btn btn-danger modalExcluir"
                    value="{{ $id }}"
                    type="button" 
                    data-toggle="modal"
                    data-target="#modalExcluirFile"
                    data-backdrop="static"
                >
                    <i class="fas fa-trash-alt"></i>
                </button>
                @endif
            </div>
        </div>
    </div>
</div>