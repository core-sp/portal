<p class="text-dark mb-2"><i class="fas fa-info-circle text-primary"></i> <strong>Atenção!</strong>
    <br>
    <span class="ml-3"><strong>*</strong> Limite de até {{ $totalFiles }} anexos</span>
    <br>
    <span class="ml-3"><strong>*</strong> Somente arquivos com extensão: .pdf, .jpg, .jpeg, .png</span>
</p>

<div class="linha-lg-mini"></div>
<p class="bold mb-2 mt-1">Documentos a serem anexados: </p>
@if(!$resultado->userExterno->isPessoaFisica())
    <p class="ml-3"><i class="icon fa fa-check"></i> Comprovante de inscrição CNPJ</p>
    <p class="ml-3"><i class="icon fa fa-check"></i> Contrato Social</p>
    <p class="ml-3"><i class="icon fa fa-check"></i> Declaração Termo de indicação RT ou Procuração</p>
    <p class="bold mb-2 mt-3 ml-3">Documentos de todos os sócios: </p>
@endif

<p class="ml-3"><i class="icon fa fa-check"></i> RG / RNE (para estrangeiros)</p>
<p class="ml-3"><i class="icon fa fa-check"></i> CNH (data de expedição máxima: 10 anos)</p>
<p class="ml-3"><i class="icon fa fa-check"></i> CPF</p>
<p class="ml-3"><i class="icon fa fa-check"></i> Comprovante de Residência dos últimos 3 meses em nome do solicitante. 
    Em caso de comprovante em nome de terceiros, o solicitante deve anexar uma declaração de próprio punho, 
    informando que reside no endereço do comprovante apresentado, assinar e datar, além de enviar cópia do Comprovante em nome de Terceiros</p>
<p class="ml-3"><i class="icon fa fa-check"></i> Certidão de quitação eleitoral (exceto estrangeiros)</p>
<p class="ml-3"><i class="icon fa fa-check"></i> Cerificado de reservista ou dispensa para o sexo masculino que tenham até 45 anos (exceto estrangeiros)</p>

<br>

<!-- Carrega os arquivos do bd com seus botoes de controle -->	
<label class="mt-3" for="anexos">{{ array_search('path', $codAnexo) }} - Anexo <span class="text-danger">*</span></label>
@if($resultado->anexos->count() == 0)

    @component('components.arquivosBD', [
        'nome' => 'anexo', 
        'nome_file' => '', 
        'rota_download' => '',
        'id' => '',
        'display' => 'display:none'
        ])
    @endcomponent

@else

@foreach($resultado->anexos as $anexo)
    @component('components.arquivosBD', [
        'nome' => 'anexo', 
        'nome_file' => $anexo->nome_original, 
        'rota_download' => route('externo.preregistro.anexo.download', $anexo->id),
        'id' => $anexo->id,
        'display' => ''
    ])
    @endcomponent
@endforeach

@endif

@component('components.arquivos_form', [
    'nome' => 'anexo', 
    'classes' => $classes[0] . ' ' . array_search('path', $codAnexo),
    'errors' => $errors
])
@endcomponent