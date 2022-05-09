@if(strlen($user->cpf_cnpj) == 11)
<label for="copia_identidade">{{ array_search('path', $codAnexo) }} - Cópia RG *</label>
<!-- Carrega os arquivos do bd com seus botoes de controle -->	
@if(isset($resultado->copia_identidade))
<div class="ArquivoBD_cp_identidade">
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
<div class="Arquivo_cp_identidade">
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576">
            <div class="custom-file">
                <input 
                    type="file" 
                    class="custom-file-input files {{ $errors->has('copia_identidade') ? 'is-invalid' : '' }}" 
                    id="copia_identidade"
                    name="copia_identidade"
                    value="{{ isset($resultado->copia_identidade) ? $resultado->copia_identidade : old('copia_identidade') }}"
                />
                <label class="custom-file-label ml-0" for="customFile"><span class="text-secondary">Escolher arquivo</span></label>
            </div>
            <div class="input-group-append">
                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
            </div>
        </div>
        @if($errors->has('copia_identidade'))
        <div class="invalid-feedback">
            {{ $errors->first('copia_identidade') }}
        </div>
        @endif
    </div>
</div>

<label for="copia_cpf">{{ array_search('path', $codAnexo) }} - Cópia do CPF / CNH *</label>
<!-- Carrega os arquivos do bd com seus botoes de controle -->	
@if(isset($resultado->copia_cpf))
    <div class="ArquivoBD_cp_cpf">
        <div class="form-row mb-2">
            <div class="input-group col-sm mb-2-576">
                <input 
                    type="text" 
                    class="form-control" 
                    value="{{-- $resultado->copia_cpf --}}"
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
<div class="Arquivo_cp_cpf">
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576">
            <div class="custom-file">
                <input 
                    type="file" 
                    class="custom-file-input files {{ $errors->has('copia_cpf') ? 'is-invalid' : '' }}" 
                    id="copia_cpf"
                    name="copia_cpf"
                    value="{{ isset($resultado->copia_cpf) ? $resultado->copia_cpf : old('copia_cpf') }}"
                />
                <label class="custom-file-label ml-0" for="customFile"><span class="text-secondary">Escolher arquivo</span></label>
            </div>
            <div class="input-group-append">
                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
            </div>
        </div>
        @if($errors->has('copia_cpf'))
        <div class="invalid-feedback">
            {{ $errors->first('copia_cpf') }}
        </div>
        @endif
    </div>
</div>

<label for="compr_residencia">{{ array_search('path', $codAnexo) }} - Comprovante de residência (dos últimos 3 meses) *</label>
<!-- Carrega os arquivos do bd com seus botoes de controle -->	
@if(isset($resultado->compr_residencia))
<div class="ArquivoBD_resid">
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576">
            <input
                type="text" 
                class="form-control" 
                value="{{-- $resultado->compr_residencia --}}"
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
<div class="Arquivo_resid">
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576">
            <div class="custom-file">
                <input
                    type="file" 
                    class="custom-file-input files {{ $errors->has('compr_residencia') ? 'is-invalid' : '' }}" 
                    id="compr_residencia"
                    name="compr_residencia"
                    value="{{ isset($resultado->compr_residencia) ? $resultado->compr_residencia : old('compr_residencia') }}"
                />
                <label class="custom-file-label ml-0" for="customFile"><span class="text-secondary">Escolher arquivo</span></label>
            </div>
            <div class="input-group-append">
                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
            </div>
        </div>
        @if($errors->has('compr_residencia'))
        <div class="invalid-feedback">
            {{ $errors->first('compr_residencia') }}
        </div>
        @endif
    </div>
</div>

<label for="certidao_tse">{{ array_search('path', $codAnexo) }} - Certidão de quitação eleitoral *</label>
<!-- Carrega os arquivos do bd com seus botoes de controle -->	
@if(isset($resultado->certidao_tse))
<div class="ArquivoBD_tse">
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576 mb-1 mt-2">
            <input 
                type="text" 
                class="form-control" 
                value="{{-- $resultado->certidao_tse --}}"
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
<div class="Arquivo_tse">
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576">
            <div class="custom-file">
                <input 
                    type="file" 
                    class="custom-file-input files {{ $errors->has('certidao_tse') ? 'is-invalid' : '' }}" 
                    id="certidao_tse"
                    name="certidao_tse"
                    value="{{ isset($resultado->certidao_tse) ? $resultado->certidao_tse : old('certidao_tse') }}"
                />
                <label class="custom-file-label ml-0" for="customFile"><span class="text-secondary">Escolher arquivo</span></label>
            </div>
            <div class="input-group-append">
                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
            </div>
        </div>
        @if($errors->has('certidao_tse'))
        <div class="invalid-feedback">
            {{ $errors->first('certidao_tse') }}
        </div>
        @endif
    </div>
</div>

    @if(isset($resultado->sexo) && ($resultado->sexo == 'M'))
    <div class="linha-lg-mini"></div>

    <label for="reservista">{{ array_search('path', $codAnexo) }} - Cerificado de reservista ou dispensa *</label>
    <!-- Carrega os arquivos do bd com seus botoes de controle -->	
    @if(isset($resultado->reservista))
    <div class="ArquivoBD_reservista">
        <div class="form-row mb-2">
            <div class="input-group col-sm mb-2-576">
                <input 
                    type="text" 
                    class="form-control" 
                    value="{{-- $resultado->reservista --}}"
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
    <div class="Arquivo_reservista">
        <div class="form-row mb-2">
            <div class="input-group col-sm mb-2-576">
                <div class="custom-file">
                    <input 
                        type="file" 
                        class="custom-file-input files {{ $errors->has('reservista') ? 'is-invalid' : '' }}" 
                        id="reservista"
                        name="reservista"
                        value="{{ isset($resultado->reservista) ? $resultado->reservista : old('reservista') }}"
                    />
                    <label class="custom-file-label ml-0" for="customFile"><span class="text-secondary">Escolher arquivo</span></label>
                </div>
                <div class="input-group-append">
                    <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
                </div>
            </div>
            @if($errors->has('reservista'))
            <div class="invalid-feedback">
                {{ $errors->first('reservista') }}
            </div>
            @endif
        </div>
    </div>
    @endif

@else
<label for="compr_inscr_cnpj">{{ array_search('path', $codAnexo) }} - Comprovante de inscrição CNPJ *</label>
<!-- Carrega os arquivos do bd com seus botoes de controle -->	
@if(isset($resultado->compr_inscr_cnpj))
<div class="ArquivoBD_inscr_cnpj">
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576">
            <input 
                type="text" 
                class="form-control" 
                value="{{-- $resultado->compr_inscr_cnpj --}}"
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
<div class="Arquivo_inscr_cnpj">
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576">
            <div class="custom-file">
                <input 
                    type="file" 
                    class="custom-file-input files {{ $errors->has('compr_inscr_cnpj') ? 'is-invalid' : '' }}" 
                    id="compr_indica_rt_pj"
                    name="compr_indica_rt_pj"
                    value="{{ isset($resultado->compr_inscr_cnpj) ? $resultado->compr_inscr_cnpj : old('compr_inscr_cnpj') }}"
                />
                <label class="custom-file-label ml-0" for="customFile"><span class="text-secondary">Escolher arquivo</span></label>
            </div>
            <div class="input-group-append">
                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
            </div>
        </div>
        @if($errors->has('compr_inscr_cnpj'))
        <div class="invalid-feedback">
            {{ $errors->first('compr_inscr_cnpj') }}
        </div>
        @endif
    </div>
</div>

<label for="contrato_social">{{ array_search('path', $codAnexo) }} - Contrato Social *</label>
<!-- Carrega os arquivos do bd com seus botoes de controle -->	
@if(isset($resultado->contrato_social))
<div class="ArquivoBD_contrato_social">
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576">
            <input 
                type="text" 
                class="form-control" 
                value="{{-- $resultado->contrato_social --}}"
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
<div class="Arquivo_contrato_social">
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576">
            <div class="custom-file">
                <input 
                    type="file" 
                    class="custom-file-input files {{ $errors->has('contrato_social') ? 'is-invalid' : '' }}" 
                    id="contrato_social"
                    name="contrato_social"
                    value="{{ isset($resultado->contrato_social) ? $resultado->contrato_social : old('contrato_social') }}"
                />
                <label class="custom-file-label ml-0" for="customFile"><span class="text-secondary">Escolher arquivo</span></label>
            </div>
            <div class="input-group-append">
                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
            </div>
        </div>
        @if($errors->has('contrato_social'))
        <div class="invalid-feedback">
            {{ $errors->first('contrato_social') }}
        </div>
        @endif
    </div>
</div>

<label for="compr_indica_rt">{{ array_search('path', $codAnexo) }} - Declaração Termo de indicação RT ou Procuração *</label>
<!-- Carrega os arquivos do bd com seus botoes de controle -->	
@if(isset($resultado->compr_indica_rt))
<div class="ArquivoBD_indica_rt">
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576">
            <input 
                type="text" 
                class="form-control" 
                value="{{-- $resultado->compr_indica_rt --}}"
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
<div class="Arquivo_indica_rt">
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576">
            <div class="custom-file">
                <input 
                    type="file" 
                    class="custom-file-input files {{ $errors->has('compr_indica_rt') ? 'is-invalid' : '' }}" 
                    id="compr_indica_rt"
                    name="compr_indica_rt"
                    value="{{ isset($resultado->compr_indica_rt) ? $resultado->compr_indica_rt : old('compr_indica_rt') }}"
                />
                <label class="custom-file-label ml-0" for="customFile"><span class="text-secondary">Escolher arquivo</span></label>
            </div>
            <div class="input-group-append">
                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
            </div>
        </div>
        @if($errors->has('compr_indica_rt'))
        <div class="invalid-feedback">
            {{ $errors->first('compr_indica_rt') }}
        </div>
        @endif
    </div>
</div>

<div class="linha-lg-mini"></div>

<h4 class="text-danger">Ver a melhor maneira de trabalhar tantos anexos nessa parte</h4>
<h5 class="bold mb-2">Documentos de todos os sócios</h5>
<p class="text-dark mb-3"><i class="fas fa-info-circle"></i> Limite de até {{ $totalFiles }} anexos para cada</p>

<!-- Carrega os arquivos do bd com seus botoes de controle -->	
@if(isset($resultado->anexos_socios))
<div class="ArquivoBD_rg">
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576">
            <input 
                type="text" 
                class="form-control" 
                value="{{-- $teste --}}"
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

<!-- Inputs para anexar o arquivo -->	
<div class="Arquivo_rg">
    <label for="anexos_socios">{{ array_search('path', $codAnexo) }} - Cópia RG *</label>
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576 pl-0">
            <div class="custom-file">
                <input 
                    type="file" 
                    class="custom-file-input files {{-- $errors->has('anexos_socios') ? 'is-invalid' : '' --}}" 
                    name="anexos_socios[]"
                    value="{{-- old('compr_residencia') --}}"
                />
                <label class="custom-file-label"><span class="text-secondary">Escolher arquivo</span></label>
            </div>
            <div class="input-group-append">
                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
            </div>
        </div>
        @if($errors->has('anexos_socios'))
        <div class="invalid-feedback">
            {{ $errors->first('anexos_socios') }}
        </div>
        @endif
    </div>
</div>

<div class="linha-lg-mini"></div>

<!-- Carrega os arquivos do bd com seus botoes de controle -->	
@if(isset($resultado->anexos_socios))
<div class="ArquivoBD_cpf">
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576">
            <input 
                type="text" 
                class="form-control" 
                value="{{-- $teste --}}"
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

<!-- Inputs para anexar o arquivo -->	
<div class="Arquivo_cpf">
    <label for="anexos_socios">{{ array_search('path', $codAnexo) }} - Cópia CPF / CNH *</label>
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576 pl-0">
            <div class="custom-file">
                <input 
                    type="file" 
                    class="custom-file-input files {{-- $errors->has('anexos_socios') ? 'is-invalid' : '' --}}" 
                    name="anexos_socios[]"
                    value="{{-- old('compr_cpf') --}}"
                />
                <label class="custom-file-label"><span class="text-secondary">Escolher arquivo</span></label>
            </div>
            <div class="input-group-append">
                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
            </div>
        </div>
        @if($errors->has('anexos_socios'))
        <div class="invalid-feedback">
            {{ $errors->first('anexos_socios') }}
        </div>
        @endif
    </div>
</div>

<div class="linha-lg-mini"></div>

<!-- Carrega os arquivos do bd com seus botoes de controle -->	
@if(isset($resultado->anexos_resid_socios))
<div class="ArquivoBD_resid_socios">
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576">
            <input 
                type="text" 
                class="form-control" 
                value="{{-- $teste --}}"
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

<!-- Inputs para anexar o arquivo -->	
<div class="Arquivo_resid_socios">
    <label for="anexos_socios">{{ array_search('path', $codAnexo) }} - Comprovante de Residência (dos últimos 3 meses) *</label>
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576 pl-0">
            <div class="custom-file">
                <input 
                    type="file" 
                    class="custom-file-input files {{-- $errors->has('anexos_resid_socios') ? 'is-invalid' : '' --}}" 
                    name="anexos_resid_socios[]"
                    value="{{-- old('compr_residencia') --}}"
                />
                <label class="custom-file-label"><span class="text-secondary">Escolher arquivo</span></label>
            </div>
            <div class="input-group-append">
                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
            </div>
        </div>
        @if($errors->has('anexos_resid_socios')) 
        <div class="invalid-feedback">
            {{ $errors->first('anexos_resid_socios') }}
        </div>
        @endif
    </div>
</div>

<div class="linha-lg-mini"></div>

<!-- Carrega os arquivos do bd com seus botoes de controle -->	
@if(isset($resultado->anexos_socios))
<div class="ArquivoBD_tse_socios">
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576">
            <input 
                type="text" 
                class="form-control" 
                value="{{-- $teste --}}"
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

<!-- Inputs para anexar o arquivo -->	
<div class="Arquivo_tse_socios">
    <label for="anexos_socios">{{ array_search('path', $codAnexo) }} - Certidão de quitação eleitoral *</label>
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576 pl-0">
            <div class="custom-file">
                <input 
                    type="file" 
                    class="custom-file-input files {{-- $errors->has('anexos_socios') ? 'is-invalid' : '' --}}" 
                    name="anexos_socios[]"
                    value="{{-- old('compr_cpf') --}}"
                />
                <label class="custom-file-label"><span class="text-secondary">Escolher arquivo</span></label>
            </div>
            <div class="input-group-append">
                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
            </div>
        </div>
        @if($errors->has('anexos_socios'))
        <div class="invalid-feedback">
            {{ $errors->first('anexos_socios') }}
        </div>
        @endif
    </div>
</div>

<div class="linha-lg-mini"></div>

<!-- Carrega os arquivos do bd com seus botoes de controle -->	
@if(isset($resultado->anexos_socios))
<div class="ArquivoBD_reserv">
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576">
            <input 
                type="text" 
                class="form-control" 
                value="{{-- $teste --}}"
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

<!-- Inputs para anexar o arquivo -->	
<div class="Arquivo_reserv">
    <label for="anexos_socios">{{ array_search('path', $codAnexo) }} - Cerificado de reservista ou dispensa *</label>
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576 pl-0">
            <div class="custom-file">
                <input 
                    type="file" 
                    class="custom-file-input files {{-- $errors->has('anexos_socios') ? 'is-invalid' : '' --}}" 
                    name="anexos_socios[]"
                    value="{{-- old('compr_cpf') --}}"
                />
                <label class="custom-file-label"><span class="text-secondary">Escolher arquivo</span></label>
            </div>
            <div class="input-group-append">
                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
            </div>
        </div>
        @if($errors->has('anexos_socios'))
        <div class="invalid-feedback">
            {{ $errors->first('anexos_socios') }}
        </div>
        @endif
    </div>
</div>
@endif