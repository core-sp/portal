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
                    class="btn btn-primary Arquivo-Download" 
                    value="" 
                    target="_blank" 
                >
                    Abrir
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