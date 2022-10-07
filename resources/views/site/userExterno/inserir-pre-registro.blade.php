@extends('site.userExterno.app')

@section('content-user-externo')

@if($errors->count() > 0)
<div class="d-block w-100 border border-warning mb-2" id="erroPreRegistro">
    <p class="bg-warning font-weight-bolder pl-1">
        {{ $errors->count() > 1 ? 'Foram encontrados ' . count($errors->messages()) . ' erros:' : 'Foi encontrado 1 erro:' }}
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
                {{ $abas[0] }}&nbsp;
                @php
                    $correcoes = $resultado->getCodigosJustificadosByAba($codigos[0]);
                @endphp
                @if($resultado->userPodeCorrigir() && empty($correcoes))
                <span class="badge badge-success"><i class="icon fa fa-check"></i></span>
                @elseif(isset($correcoes) && !empty($correcoes))
                    @foreach($correcoes as $correcao)
                    <span class="badge badge-danger"> {{ $correcao }}</span>
                    @endforeach
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte2_PF_PJ">
                {{ $abas[1] }}&nbsp;
                @php
                    $correcoes = $resultado->getCodigosJustificadosByAba($codigos[1]);
                @endphp
                @if($resultado->userPodeCorrigir() && empty($correcoes))
                <span class="badge badge-success"><i class="icon fa fa-check"></i></span>
                @elseif(isset($correcoes) && !empty($correcoes))
                    @foreach($correcoes as $correcao)
                    <span class="badge badge-danger"> {{ $correcao }}</span>
                    @endforeach
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte3_PF_PJ">
                {{ $abas[2] }}&nbsp;
                @php
                    $correcoes = $resultado->getCodigosJustificadosByAba($codigos[2]);
                @endphp
                @if($resultado->userPodeCorrigir() && empty($correcoes))
                <span class="badge badge-success"><i class="icon fa fa-check"></i></span>
                @elseif(isset($correcoes) && !empty($correcoes))
                    @foreach($correcoes as $correcao)
                    <span class="badge badge-danger"> {{ $correcao }}</span>
                    @endforeach
                @endif
            </a>
        </li>
        @if(!$resultado->userExterno->isPessoaFisica())
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte4_PJ">
                {{ $abas[3] }}&nbsp;
                @php
                    $correcoes = $resultado->getCodigosJustificadosByAba($codigos[3]);
                @endphp
                @if($resultado->userPodeCorrigir() && empty($correcoes))
                <span class="badge badge-success"><i class="icon fa fa-check"></i></span>
                @elseif(isset($correcoes) && !empty($correcoes))
                    @foreach($correcoes as $correcao)
                    <span class="badge badge-danger"> {{ $correcao }}</span>
                    @endforeach
                @endif
            </a>
        </li>
        @endif
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte4_PF_parte5_PJ">
                {{ $abas[4] }}&nbsp;
                @php
                    $correcoes = $resultado->getCodigosJustificadosByAba($codigos[4]);
                @endphp
                @if($resultado->userPodeCorrigir() && empty($correcoes))
                <span class="badge badge-success"><i class="icon fa fa-check"></i></span>
                @elseif(isset($correcoes) && !empty($correcoes))
                    @foreach($correcoes as $correcao)
                    <span class="badge badge-danger"> {{ $correcao }}</span>
                    @endforeach
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte5_PF_parte6_PJ">
                {{ $abas[5] }}&nbsp;
                @php
                    $correcoes = $resultado->getCodigosJustificadosByAba($codigos[5]);
                @endphp
                @if($resultado->userPodeCorrigir() && empty($correcoes))
                <span class="badge badge-success"><i class="icon fa fa-check"></i></span>
                @elseif(isset($correcoes) && !empty($correcoes))
                    @foreach($correcoes as $correcao)
                    <span class="badge badge-danger"> {{ $correcao }}</span>
                    @endforeach
                @endif
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
                @if(!$resultado->userPodeEditar())
                <fieldset disabled>
                    @include('site.userExterno.inc.pre-registro-etapa1')
                </fieldset>
                @else
                    @include('site.userExterno.inc.pre-registro-etapa1')
                @endif
            </div>
    
            <!-- Tab 2 -->
            <div id="parte2_PF_PJ" class="tab-pane container fade"><br>
                @if(!$resultado->userPodeEditar())
                <fieldset disabled>
                    @include('site.userExterno.inc.pre-registro-etapa2')
                </fieldset>
                @else
                    @include('site.userExterno.inc.pre-registro-etapa2')
                @endif
            </div>

            <!-- Tab 3 -->
            <div id="parte3_PF_PJ" class="tab-pane container fade"><br>
                @if(!$resultado->userPodeEditar())
                <fieldset disabled>
                    @include('site.userExterno.inc.pre-registro-etapa3')
                </fieldset>
                @else
                    @include('site.userExterno.inc.pre-registro-etapa3')
                @endif
            </div>

            <!-- Tab 4 PJ -->
            @if(!$resultado->userExterno->isPessoaFisica())
            <div id="parte4_PJ" class="tab-pane container fade"><br>
                @if(!$resultado->userPodeEditar())
                <fieldset disabled>
                    @include('site.userExterno.inc.pre-registro-etapa4-PJ')
                </fieldset>
                @else
                    @include('site.userExterno.inc.pre-registro-etapa4-PJ')
                @endif
            </div>
            @endif

            <!-- Tab 4 PF e Tab 5 PJ -->
            <div id="parte4_PF_parte5_PJ" class="tab-pane container fade"><br>
                @if(!$resultado->userPodeEditar())
                <fieldset disabled>
                    @include('site.userExterno.inc.pre-registro-etapa4-PF-etapa5-PJ')
                </fieldset>
                @else
                    @include('site.userExterno.inc.pre-registro-etapa4-PF-etapa5-PJ')
                @endif
            </div>

            <!-- Tab 5 PF e Tab 6 PJ -->
            <div id="parte5_PF_parte6_PJ" class="tab-pane container fade"><br>
                @include('site.userExterno.inc.pre-registro-etapa5-PF-etapa6-PJ')
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

            @if($resultado->userPodeEditar())
            <div class="col-6">
                <button 
                    class="btn btn-primary btn-sm float-right" 
                    type="submit" 
                    id="btnVerificaPend"
                    data-toggle="modal" 
                    data-target="#modalLoadingPreRegistro" 
                    data-backdrop="static"
                >
                    Verificar Pendências
                </button>
            </div>
            @endif
        </div>
    </form>

    <!-- The Modal Loading acionado via ajax -->
    <div class="modal hide" id="modalLoadingPreRegistro">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!-- Modal body -->
                <div id="modalLoadingBody" class="modal-body text-center">
                    <i class="spinner-border text-info"></i> Enviando...
                </div>
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
                    Sua solicitação não possui pendências. <br>Você pode prosseguir com o pedido de registro no CORE-SP para ser
                    <strong>REPRESENTANTE COMERCIAL</strong>
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