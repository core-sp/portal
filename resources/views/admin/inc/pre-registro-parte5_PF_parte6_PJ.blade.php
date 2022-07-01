<div class="card-body">

    <p id="path" class="mb-4">
        <span class="font-weight-bolder">{{ array_search('path', $codAnexo) }} - Anexos: </span>
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'path',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    @foreach($resultado->anexos as $anexo)
    <p>{{ $anexo->nome_original }} 
        <a href="{{ route('preregistro.anexo.download', ['idPreRegistro' => $resultado->id, 'id' => $anexo->id]) }}" 
            class="btn btn-sm btn-primary ml-2" 
            target="_blank" 
        >
            Abrir
        </a>
        <a href="{{ route('preregistro.anexo.download', ['idPreRegistro' => $resultado->id, 'id' => $anexo->id]) }}" 
            class="btn btn-sm btn-primary ml-2" 
            download
        >
            <i class="fas fa-download"></i>
        </a>
    </p>
    @endforeach

    <hr>

    <label for="confere_anexos[]"><i class="fas fa-check"></i> Anexos entregues: </label>
    <br>

    @if(!$resultado->userExterno->isPessoaFisica())

    <div class="form-check">
        <label class="form-check-label">
            <input 
                type="checkbox" 
                name="confere_anexos[]"
                value="Comprovante de inscrição CNPJ" 
                class="confirmaAnexoPreRegistro"
                {{ isset($resultado->getConfereAnexosArray()['Comprovante de inscrição CNPJ']) ? 'checked' : '' }}
            /> Comprovante de inscrição CNPJ
        </label>
    </div>

    <div class="form-check">
        <label class="form-check-label">
            <input 
                type="checkbox" 
                name="confere_anexos[]"
                value="Contrato Social" 
                class="confirmaAnexoPreRegistro"
                {{ isset($resultado->getConfereAnexosArray()['Contrato Social']) ? 'checked' : '' }}
            /> Contrato Social
        </label>
    </div>

    <div class="form-check">
        <label class="form-check-label">
            <input 
                type="checkbox" 
                name="confere_anexos[]"
                value="Declaração Termo de indicação RT ou Procuração" 
                class="confirmaAnexoPreRegistro"
                {{ isset($resultado->getConfereAnexosArray()['Declaração Termo de indicação RT ou Procuração']) ? 'checked' : '' }}
            /> Declaração Termo de indicação RT ou Procuração
        </label>
    </div>

    <p class="font-weight-bolder pl-3 mt-2 mb-1">Documentos de todos os sócios:</p>

    @endif

    <div class="form-check">
        <label class="form-check-label">
            <input 
                type="checkbox" 
                name="confere_anexos[]"
                value="Comprovante de identidade" 
                class="confirmaAnexoPreRegistro"
                {{ isset($resultado->getConfereAnexosArray()['Comprovante de identidade']) ? 'checked' : '' }}
            /> Comprovante de identidade
        </label>
    </div>

    <div class="form-check">
        <label class="form-check-label">
            <input 
                type="checkbox" 
                name="confere_anexos[]"
                value="CPF" 
                class="confirmaAnexoPreRegistro"
                {{ isset($resultado->getConfereAnexosArray()['CPF']) ? 'checked' : '' }}
            /> CPF
        </label>
    </div>

    <div class="form-check">
        <label class="form-check-label">
            <input 
                type="checkbox" 
                name="confere_anexos[]"
                value="Comprovante de Residência" 
                class="confirmaAnexoPreRegistro"
                {{ isset($resultado->getConfereAnexosArray()['Comprovante de Residência']) ? 'checked' : '' }}
            /> Comprovante de Residência
        </label>
    </div>

    @if((isset($resultado->pessoaFisica->nacionalidade) && ($resultado->pessoaFisica->nacionalidade == 'BRASILEIRO')) ||
    !$resultado->userExterno->isPessoaFisica())
    <div class="form-check">
        <label class="form-check-label">
            <input 
                type="checkbox" 
                name="confere_anexos[]"
                value="Certidão de quitação eleitoral" 
                class="confirmaAnexoPreRegistro"
                {{ isset($resultado->getConfereAnexosArray()['Certidão de quitação eleitoral']) ? 'checked' : '' }}
            /> Certidão de quitação eleitoral
        </label>
    </div>
    @endif

    @if((isset($resultado->pessoaFisica->sexo) && ($resultado->pessoaFisica->sexo == 'M')) ||
    !$resultado->userExterno->isPessoaFisica())
    <div class="form-check">
        <label class="form-check-label">
            <input 
                type="checkbox" 
                name="confere_anexos[]"
                value="Cerificado de reservista ou dispensa" 
                class="confirmaAnexoPreRegistro"
                {{ isset($resultado->getConfereAnexosArray()['Cerificado de reservista ou dispensa']) ? 'checked' : '' }}
            /> Cerificado de reservista ou dispensa
        </label>
    </div>
    @endif

</div>