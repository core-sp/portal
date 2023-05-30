@php
    $arrayJustificativas = $resultado->getJustificativaArray();
    $campoAnexo = isset($resultado->getCamposEditados()['path']) ? explode(',', $resultado->getCamposEditados()['path']) : array();
@endphp

<div class="card-body bg-light">

    <p class="font-weight-bolder mb-2 mt-1"><i class="fas fa-info-circle text-primary"></i> Exigências sobre os documentos obrigatórios: </p>
    @if(!$resultado->userExterno->isPessoaFisica())
        <p class="ml-3 mb-0 text-secondary"><i class="icon fa fa-check"></i> Comprovante de inscrição CNPJ</p>
        <p class="ml-3 mb-0 text-secondary"><i class="icon fa fa-check"></i> Contrato Social</p>
        <p class="ml-3 mb-0 text-secondary"><i class="icon fa fa-check"></i> Declaração Termo de indicação RT ou Procuração</p>
        <p class="font-weight-bolder mb-2 mt-3 ml-3 text-secondary">Documentos de todos os sócios: </p>
    @endif

    <p class="ml-3 mb-0 text-secondary"><i class="icon fa fa-check"></i> Comprovante de identidade pode ser:</p>
    <p class="ml-5 mb-0 text-secondary"> RG; Passaporte, CNH (data de expedição máxima: 10 anos); Carteira de Conselho Profissional; RNE (para estrangeiros)</p>
    <p class="ml-3 mb-0 text-secondary"><i class="icon fa fa-check"></i> CPF</p>
    <p class="ml-3 mb-0 text-secondary"><i class="icon fa fa-check"></i> Comprovante de Residência dos últimos 3 meses em nome do solicitante. 
        Em caso de comprovante em nome de terceiros, o solicitante deve anexar uma declaração de próprio punho, 
        informando que reside no endereço do comprovante apresentado, assinar e datar, além de enviar cópia do Comprovante em nome de Terceiros</p>
    <p class="ml-3 mb-0 text-secondary"><i class="icon fa fa-check"></i> Certidão de quitação eleitoral (exceto estrangeiros)</p>
    <p class="ml-3 mb-2 text-secondary"><i class="icon fa fa-check"></i> Cerificado de reservista ou dispensa para o sexo masculino que tenham até 45 anos (exceto estrangeiros)</p>

    <hr>

    <p id="path" class="mb-4">
        <span class="font-weight-bolder">{{ $codigos[5]['path'] }} - Anexos: </span>
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'path',
            'resultado' => $arrayJustificativas
        ])
        @endcomponent
    </p>

    @foreach($resultado->anexos as $anexo)
    <span class="font-weight-bolder">ID: {{ $anexo->id }} </span>
    <p class="mb-0">
        <i class="fas fa-paperclip"></i> {{ $anexo->nome_original }} 
        @if(in_array($anexo->extensao, ['jpg', 'jpeg', 'png', 'pdf']))
        <a href="{{ route('preregistro.anexo.download', ['idPreRegistro' => $resultado->id, 'id' => $anexo->id]) }}" 
            class="btn btn-sm btn-primary ml-2" 
            target="_blank" 
        >
            Abrir
        </a>
        @endif
        <a href="{{ route('preregistro.anexo.download', ['idPreRegistro' => $resultado->id, 'id' => $anexo->id]) }}" 
            class="btn btn-sm btn-primary ml-2" 
            download
        >
            <i class="fas fa-download"></i>
        </a>
        @if(!empty($campoAnexo) && (array_search($anexo->id, $campoAnexo) !== false))
        <span class="badge badge-success ml-2">Novo anexo</span>
        @endif
    </p>
    @endforeach

    <hr>

    {!! !$resultado->atendentePodeEditar() ? '<fieldset disabled>' : '' !!}
    <label><i class="fas fa-check"></i> Anexos entregues: </label>
    <br>

    @if(!$resultado->userExterno->isPessoaFisica())

    <div class="form-check">
        <label for="inscricao_cnpj" class="form-check-label">
            <input 
                id="inscricao_cnpj"
                type="checkbox" 
                name="confere_anexos[]"
                value="Comprovante de inscrição CNPJ" 
                class="confirmaAnexoPreRegistro"
                {{ isset($resultado->getConfereAnexosArray()['Comprovante de inscrição CNPJ']) ? 'checked' : '' }}
            /> Comprovante de inscrição CNPJ
        </label>
    </div>

    <div class="form-check">
        <label for="contrato_social" class="form-check-label">
            <input 
                id="contrato_social"
                type="checkbox" 
                name="confere_anexos[]"
                value="Contrato Social" 
                class="confirmaAnexoPreRegistro"
                {{ isset($resultado->getConfereAnexosArray()['Contrato Social']) ? 'checked' : '' }}
            /> Contrato Social
        </label>
    </div>

    <div class="form-check">
        <label for="termo_rt_procuracao" class="form-check-label">
            <input 
                id="termo_rt_procuracao"
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
        <label for="comprovante_identidade" class="form-check-label">
            <input 
                id="comprovante_identidade"
                type="checkbox" 
                name="confere_anexos[]"
                value="Comprovante de identidade" 
                class="confirmaAnexoPreRegistro"
                {{ isset($resultado->getConfereAnexosArray()['Comprovante de identidade']) ? 'checked' : '' }}
            /> Comprovante de identidade
        </label>
    </div>

    <div class="form-check">
        <label for="comprovante_cpf" class="form-check-label">
            <input 
                id="comprovante_cpf"
                type="checkbox" 
                name="confere_anexos[]"
                value="CPF" 
                class="confirmaAnexoPreRegistro"
                {{ isset($resultado->getConfereAnexosArray()['CPF']) ? 'checked' : '' }}
            /> CPF
        </label>
    </div>

    <div class="form-check">
        <label for="comprovante_residencia" class="form-check-label">
            <input 
                id="comprovante_residencia"
                type="checkbox" 
                name="confere_anexos[]"
                value="Comprovante de Residência" 
                class="confirmaAnexoPreRegistro"
                {{ isset($resultado->getConfereAnexosArray()['Comprovante de Residência']) ? 'checked' : '' }}
            /> Comprovante de Residência
        </label>
    </div>

    @if((isset($resultado->pessoaFisica->nacionalidade) && ($resultado->pessoaFisica->nacionalidade == 'BRASILEIRA')) ||
    !$resultado->userExterno->isPessoaFisica())
    <div class="form-check">
        <label for="cert_quitacao_eleitoral" class="form-check-label">
            <input 
                id="cert_quitacao_eleitoral"
                type="checkbox" 
                name="confere_anexos[]"
                value="Certidão de quitação eleitoral" 
                class="confirmaAnexoPreRegistro"
                {{ isset($resultado->getConfereAnexosArray()['Certidão de quitação eleitoral']) ? 'checked' : '' }}
            /> Certidão de quitação eleitoral
        </label>
    </div>
    @endif

    @if((isset($resultado->pessoaFisica->sexo) && ($resultado->pessoaFisica->sexo == 'M') && !$resultado->pessoaFisica->maisDe45Anos()) ||
    !$resultado->userExterno->isPessoaFisica())
    <div class="form-check">
        <label for="cert_reservista_dispensa" class="form-check-label">
            <input 
                id="cert_reservista_dispensa"
                type="checkbox" 
                name="confere_anexos[]"
                value="Cerificado de reservista ou dispensa" 
                class="confirmaAnexoPreRegistro"
                {{ isset($resultado->getConfereAnexosArray()['Cerificado de reservista ou dispensa']) ? 'checked' : '' }}
            /> Cerificado de reservista ou dispensa
        </label>
    </div>
    @endif

    <p class="text-muted mt-3">
        <em>* Obs: somente para PJ não é obrigatório confirmar a "Certidão de quitação eleitoral" e "Cerificado de reservista ou dispensa" para aprovação</em>
    </p>
    {!! !$resultado->atendentePodeEditar() ? '</fieldset>' : '' !!}
    
</div>