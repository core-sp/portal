@extends('site.userExterno.app')

@section('content-user-externo')

@php
    $abas = ['Contabilidade', 'Dados Gerais', 'Endereço', 'Contato / RT', 'Canal de Relacionamento', 'Anexos'];
@endphp

@if($errors->count() > 0)
<div class="d-block w-100 border border-warning mb-2" id="erroPreRegistro">
    <p class="bg-warning font-weight-bolder pl-1">
        {{ $errors->count() > 1 ? 'Foram encontrados ' . $errors->count() . ' erros:' : 'Foi encontrado 1 erro:' }}
    <p>
    <div class="alert alert-light pl-0 pb-0">
    @foreach($errors->messages() as $key => $message)
        @php
            if(in_array($key, ['opcional_celular', 'opcional_celular_1']))
                $key .= '[]';
        @endphp
        <span>
            <button class="btn btn-sm btn-link erroPreRegistro" value="{{ $key }}">
                <i class="fas fa-exclamation-triangle text-danger"></i>  {{ $message[0] }}
            </button>
            <br>
        </span>
    @endforeach
    </div>
</div>
@endif

<div class="representante-content w-100">

    <!-- Nav tabs -->
    <ul class="menu-registro nav nav-pills flex-column">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#parte1_PF_PJ">
                {{ $abas[0] }} {{-- - <i class="icon fa fa-check text-success"></i> --}}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte2_PF_PJ">
                {{ $abas[1] }} {{-- - <i class="icon fa fa-times text-danger"></i> <span class="text-danger">R27, R28</span> --}}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte3_PF_PJ">
                {{ $abas[2] }}
            </a>
        </li>
        @if(strlen($resultado->userExterno->cpf_cnpj) == 14)
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte4_PJ">
                {{ $abas[3] }}
            </a>
        </li>
        @endif
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte4_PF_parte5_PJ">
                {{ $abas[4] }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte5_PF_parte6_PJ">
                {{ $abas[5] }}
            </a>
        </li>
    </ul>

    <hr class="mb-0">

    <form method="POST" 
        action="{{ isset($semPendencia) && $semPendencia ? route('externo.inserir.preregistro') : route('externo.verifica.inserir.preregistro') }}" 
        enctype="multipart/form-data" 
        id="inserirRegistro" 
        class="cadastroRepresentante" 
        autocomplete="off"
    >
        @csrf
        @method('PUT')

        <!-- Tab panes -->
        <div class="tab-content">

            <!-- Tab 1 -->
            <div id="parte1_PF_PJ" class="tab-pane container active"><br>
                @include('site.userExterno.inc.pre-registro-etapa1', [
                    'cod' => $codigos[$classes[1]]
                ])
            </div>
     
            <!-- Tab 2 -->
            <div id="parte2_PF_PJ" class="tab-pane container fade"><br>
                @include('site.userExterno.inc.pre-registro-etapa2', [
                    'codPre' => $codigos[$classes[4]],
                    'codCpf' => $codigos[$classes[2]],
                    'codCnpj' => $codigos[$classes[3]]
                ])
            </div>

            <!-- Tab 3 -->
            <div id="parte3_PF_PJ" class="tab-pane container fade"><br>
                @include('site.userExterno.inc.pre-registro-etapa3', [
                    'codPre' => $codigos[$classes[4]],
                    'codCnpj' => $codigos[$classes[3]]
                ])
            </div>

            <!-- Tab 4 PJ -->
            @if(strlen($resultado->userExterno->cpf_cnpj) == 14)
            <div id="parte4_PJ" class="tab-pane container fade"><br>
                @include('site.userExterno.inc.pre-registro-etapa4-PJ', [
                    'codRT' => $codigos[$classes[5]]
                ])
            </div>
            @endif

            <!-- Tab 4 PF e Tab 5 PJ -->
            <div id="parte4_PF_parte5_PJ" class="tab-pane container fade"><br>
                @include('site.userExterno.inc.pre-registro-etapa4-PF-etapa5-PJ', [
                    'codPre' => $codigos[$classes[4]]
                ])
            </div>

            <!-- Tab 5 PF e Tab 6 PJ -->
            <div id="parte5_PF_parte6_PJ" class="tab-pane container fade"><br>
                @include('site.userExterno.inc.pre-registro-etapa5-PF-etapa6-PJ', [
                    'codAnexo' => $codigos[$classes[0]],
                ])
            </div>

        </div>

        <br>
        <div class="linha-lg-mini"></div>

        <!-- Menu voltar e avançar no formulário -->
        <div class="form-row">
            <div class="col-6">
                <button class="btn btn-link p-0 mr-3" id="voltarPreRegistro" type="button" disabled>
                    <i class="fas fa-angle-double-left"></i> <strong>Voltar</strong>
                </button>

                <button class="btn btn-link p-0 ml-3" id="avancarPreRegistro" type="button">
                    <i class="fas fa-angle-double-right"></i> <strong>Avançar</strong>
                </button>
            </div>

            <div class="col-6">
                <button 
                    class="btn btn-primary btn-sm float-right" 
                    type="submit" 
                    id="btnVerificaPend"
                    disabled
                >
                    Verificar Pendências
                </button>
            </div>
        </div>
    </form>

    <!-- The Modal Loading acionado via ajax -->
    <div class="modal hide" id="modalLoadingPreRegistro">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!-- Modal body -->
                <div id="modalLoadingBody" class="modal-body text-center"></div>
            </div>
        </div>
    </div>

    <!-- The Modal que aparece quando não há pendências -->
    <div class="modal {{ isset($semPendencia) && $semPendencia ? 'show' : 'hide' }}" id="modalSubmitPreRegistro">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-info-circle text-primary"></i> Atenção!</h4>
                </div>
                
                <!-- Modal body -->
                <div class="modal-body">
                    Texto....
                    Sua solicitação de registro para ser <strong>REPRESENTANTE COMERCIAL</strong>
                </div>
                
                <!-- Modal footer -->
                <div class="modal-footer">
                    <a href="{{ route('externo.inserir.preregistro.view') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="button" class="btn btn-success" id="submitPreRegistro" value="">Enviar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col d-flex justify-content-between mt-2 pl-0 pr-0">
    <small class="text-muted text-left">
        <em><span class="text-danger font-weight-bolder">*</span> Preencha todos os campos obrigatórios</em>
    </small>
    <small class="text-right">Atualizado em: 
        <span id="atualizacaoPreRegistro">{{ $resultado->updated_at->format('d\/m\/Y, \à\s H:i:s') }}</span>
    </small>
</div>

@endsection