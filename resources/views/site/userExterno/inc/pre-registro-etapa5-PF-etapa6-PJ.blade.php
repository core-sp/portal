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