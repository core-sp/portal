@php
    $correcoes = $resultado->getTextosJustificadosByAba($codigos[5]);
@endphp
@if($resultado->userPodeCorrigir() && !empty($correcoes))
    <div class="d-block w-100">
        <div class="alert alert-warning">
            <span class="bold">Justificativa(s):</span>
            <br>
        @foreach($correcoes as $key => $texto)
            <p>
                <span class="bold">{{ $key . ': ' }}</span>{{ $texto }}
            </p>
        @endforeach
        </div>
    </div>
@endif

<p class="text-dark mb-2"><i class="fas fa-info-circle text-primary"></i> <strong>Atenção!</strong>
    <br>
    <span class="ml-3"><i class="fas fa-minus"></i> Pode adicionar até {{ $totalFiles }} <span class="bold">anexos</span> com, no máximo, <mark>5MB</mark> de tamanho.</span>
    <br>
    <span class="ml-3"><i class="fas fa-minus"></i> Em cada anexo pode conter até 15 arquivos, basta manter a tecla <kbd>ctrl</kbd> pressionada ao selecionar.</span>
    <br>
    <span class="ml-3"><i class="fas fa-minus"></i> Caso selecione mais de um arquivo no anexo, ele será comprimido num arquivo com a extensão <mark>.zip</mark>.</span>
    <input id="totalFilesServer" type="hidden" value="{{ $totalFiles }}" />
    <br>
    <span class="ml-3"><i class="fas fa-minus"></i> Somente arquivos com extensão <mark>.pdf, .jpg, .jpeg, .png</mark> são aceitos.</span>
    <br>
    <span class="ml-3"><i class="fas fa-minus"></i> Todos os arquivos são renomeados.</span>
</p>

<div class="linha-lg-mini"></div>
<p class="bold mb-2 mt-1">Documentos a serem anexados: </p>
@if(!$resultado->userExterno->isPessoaFisica())
    <p class="ml-3"><i class="icon fa fa-check"></i> Comprovante de inscrição CNPJ</p>
    <p class="ml-3"><i class="icon fa fa-check"></i> Contrato Social</p>
    <p class="ml-3"><i class="icon fa fa-check"></i> Declaração Termo de indicação RT ou Procuração</p>
    <p class="bold mb-2 mt-3 ml-3">Documentos de todos os sócios: </p>
@endif

<p class="ml-3"><i class="icon fa fa-check"></i> Comprovante de identidade pode ser:</p>
<p class="ml-5"> RG; Passaporte, <span class="text-nowrap">CNH (data de expedição máxima: 10 anos);</span> <span class="text-nowrap">Carteira de Conselho Profissional;</span> <span class="text-nowrap">RNE (para estrangeiros)</span></p>
<p class="ml-3"><i class="icon fa fa-check"></i> CPF</p>
<p class="ml-3"><i class="icon fa fa-check"></i> Comprovante de Residência dos últimos 3 meses em nome do solicitante. 
    Em caso de comprovante em nome de terceiros, o solicitante deve anexar uma declaração de próprio punho, 
    informando que reside no endereço do comprovante apresentado, assinar e datar, além de enviar cópia do Comprovante em nome de Terceiros</p>
<p class="ml-3"><i class="icon fa fa-check"></i> Certidão de quitação eleitoral (exceto estrangeiros)</p>
<p class="ml-3"><i class="icon fa fa-check"></i> Cerificado de reservista ou dispensa para o sexo masculino que tenham até 45 anos (exceto estrangeiros)</p>

<br>

<!-- Carrega os arquivos do bd com seus botoes de controle -->	
@if(!\Route::is('externo.verifica.inserir.preregistro'))
    <label class="mt-3" for="anexos">{{ $codigos[5]['path'] }} - Anexo <span class="text-danger">*</span></label>
    @if($resultado->anexos->count() == 0)

        @component('components.arquivosBD', [
            'nome' => 'anexo', 
            'nome_file' => '', 
            'rota_download' => '',
            'id' => '',
            'display' => 'display:none',
            'podeExcluir' => true,
            ])
        @endcomponent

    @else

        @foreach($resultado->anexos as $anexo)
            @component('components.arquivosBD', [
                'nome' => 'anexo', 
                'nome_file' => $anexo->nome_original, 
                'rota_download' => route('externo.preregistro.anexo.download', $anexo->id),
                'id' => $anexo->id,
                'display' => '',
                'podeExcluir' => $resultado->userPodeEditar(),
                'extensao' => $anexo->extensao,
            ])
            @endcomponent
        @endforeach

    @endif

    <br>
    <input type="hidden" id="fileObrigatorio" class="obrigatorio" value="{{ $resultado->anexos->count() > 0 ? 'existeAnexo' : '' }}">

    {{-- Em 'accept' só aparece os tipos de files que são aceitos, string idêntica ao PreRegistroAjaxRequest --}}
    @if($resultado->userPodeEditar())
        @component('components.arquivos_form', [
            'nome' => 'anexo', 
            'classes' => $classes[0],
            'errors' => $errors,
            'accept' => 'application/pdf,image/jpeg,image/png',
            'multiple' => true,
        ])
        @endcomponent
    @endif
    <small class="text-muted text-left">
    <em>
        Para selecionar mais de um arquivo, mantenha a tecla <kbd>ctrl</kbd> pressionada ao selecionar
    </em>
</small>

@endif