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
            <a class="nav-link active" data-toggle="tab" href="#parte_contabilidade">
                {{ $abas[0] }}&nbsp;
                @component('components.justificativa_pre_registro', [
                    'resultado' => $resultado,
                    'correcoes' => $resultado->getCodigosJustificadosByAba($codigos[0]),
                    'menu' => true,
                ])
                @endcomponent
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte_dados_gerais">
                {{ $abas[1] }}&nbsp;
                @component('components.justificativa_pre_registro', [
                    'resultado' => $resultado,
                    'correcoes' => $resultado->getCodigosJustificadosByAba($codigos[1]),
                    'menu' => true,
                ])
                @endcomponent
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte_endereco">
                {{ $abas[2] }}&nbsp;
                @component('components.justificativa_pre_registro', [
                    'resultado' => $resultado,
                    'correcoes' => $resultado->getCodigosJustificadosByAba($codigos[2]),
                    'menu' => true,
                ])
                @endcomponent
            </a>
        </li>
        @if(!$resultado->userExterno->isPessoaFisica())
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte_contato_rt">
                {{ $abas[3] }}&nbsp;
                @component('components.justificativa_pre_registro', [
                    'resultado' => $resultado,
                    'correcoes' => $resultado->getCodigosJustificadosByAba($codigos[3]),
                    'menu' => true,
                ])
                @endcomponent
            </a>
        </li>
        <!-- socios -->
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte_socios">
                {{ $abas[4] }}&nbsp;
                @component('components.justificativa_pre_registro', [
                    'resultado' => $resultado,
                    'correcoes' => $resultado->getCodigosJustificadosByAba($codigos[4]),
                    'menu' => true,
                ])
                @endcomponent
            </a>
        </li>
        @endif
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte_canal_relacionamento">
                {{ $abas[5] }}&nbsp;
                @component('components.justificativa_pre_registro', [
                    'resultado' => $resultado,
                    'correcoes' => $resultado->getCodigosJustificadosByAba($codigos[5]),
                    'menu' => true,
                ])
                @endcomponent
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte_anexos">
                {{ $abas[6] }}&nbsp;
                @component('components.justificativa_pre_registro', [
                    'resultado' => $resultado,
                    'correcoes' => $resultado->getCodigosJustificadosByAba($codigos[6]),
                    'menu' => true,
                ])
                @endcomponent
            </a>
        </li>
    </ul>

    <hr class="mb-0">

    @php
        $rotaInserir = auth()->guard('contabil')->check() ? route('externo.inserir.preregistro', $resultado->id) : route('externo.inserir.preregistro');
        $rotaVerifica = auth()->guard('contabil')->check() ? route('externo.verifica.inserir.preregistro', $resultado->id) : route('externo.verifica.inserir.preregistro');
        $rotaCancelar = auth()->guard('contabil')->check() ? route('externo.preregistro.view', $resultado->id) : route('externo.preregistro.view');
    @endphp
    <form method="POST" 
        action="{{ isset($semPendencia) && $semPendencia ? $rotaInserir : $rotaVerifica }}" 
        enctype="multipart/form-data" 
        id="inserirRegistro" 
        class="cadastroRepresentante" 
        autocomplete="off"
    >
        @csrf
        @method('PUT')
        
        @if(auth()->guard('contabil')->check())
        <input type="hidden" value="{{ $resultado->id }}" id="contabil_editar_pr">
        @endif

        <!-- Tab panes -->
        <div class="tab-content">
        
            <!-- Tab 1 -->
            <div id="parte_contabilidade" class="tab-pane container active"><br>
                @if(!$resultado->userPodeEditar())
                <fieldset disabled>
                    @include('site.userExterno.inc.pre-registro-contabilidade', ['nome_campos' => $codigos[0], 'classe' => $classes[1]])
                </fieldset>
                @else
                    @include('site.userExterno.inc.pre-registro-contabilidade', ['nome_campos' => $codigos[0], 'classe' => $classes[1]])
                @endif
            </div>
    
            <!-- Tab 2 -->
            <div id="parte_dados_gerais" class="tab-pane container fade"><br>
                @if(!$resultado->userPodeEditar())
                <fieldset disabled>
                    @include('site.userExterno.inc.pre-registro-dados-gerais', ['nome_campos' => $codigos[1], 'classe' => $classes[4], 'classe_pf' => $classes[2], 'classe_pj' => $classes[3]])
                </fieldset>
                @else
                    @include('site.userExterno.inc.pre-registro-dados-gerais', ['nome_campos' => $codigos[1], 'classe' => $classes[4], 'classe_pf' => $classes[2], 'classe_pj' => $classes[3]])
                @endif
            </div>

            <!-- Tab 3 -->
            <div id="parte_endereco" class="tab-pane container fade"><br>
                @if(!$resultado->userPodeEditar())
                <fieldset disabled>
                    @include('site.userExterno.inc.pre-registro-endereco', ['nome_campos' => $codigos[2], 'classe' => $classes[4], 'classe_pj' => $classes[3]])
                </fieldset>
                @else
                    @include('site.userExterno.inc.pre-registro-endereco', ['nome_campos' => $codigos[2], 'classe' => $classes[4], 'classe_pj' => $classes[3]])
                @endif
            </div>

            <!-- Tab 4 PJ -->
            @if(!$resultado->userExterno->isPessoaFisica())
            <div id="parte_contato_rt" class="tab-pane container fade"><br>
                @if(!$resultado->userPodeEditar())
                <fieldset disabled>
                    @include('site.userExterno.inc.pre-registro-contato-rt', ['nome_campos' => $codigos[3], 'classe' => $classes[5]])
                </fieldset>
                @else
                    @include('site.userExterno.inc.pre-registro-contato-rt', ['nome_campos' => $codigos[3], 'classe' => $classes[5]])
                @endif
            </div>
            <!-- socios -->
            <div id="parte_socios" class="tab-pane container fade"><br>
                @include('site.userExterno.inc.pre-registro-socios', ['nome_campos' => $codigos[4], 'classe' => $classes[6]])
            </div>
            @endif

            <!-- Tab 4 PF e Tab 5 PJ -->
            <div id="parte_canal_relacionamento" class="tab-pane container fade"><br>
                @if(!$resultado->userPodeEditar())
                <fieldset disabled>
                    @include('site.userExterno.inc.pre-registro-canal-relacionamento', ['nome_campos' => $codigos[5], 'classe' => $classes[4]])
                </fieldset>
                @else
                    @include('site.userExterno.inc.pre-registro-canal-relacionamento', ['nome_campos' => $codigos[5], 'classe' => $classes[4]])
                @endif
            </div>

            <!-- Tab 5 PF e Tab 6 PJ -->
            <div id="parte_anexos" class="tab-pane container fade"><br>
                @include('site.userExterno.inc.pre-registro-anexos', ['nome_campos' => $codigos[6], 'classe' => $classes[0]])
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
                    {{ $resultado->status != $resultado::STATUS_CORRECAO ? 'Verificar Pendências' : 'Enviar' }}
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
                    @if($resultado->status != $resultado::STATUS_CORRECAO)
                    Sua solicitação não possui pendências. <br>Você pode prosseguir com o pedido de registro no CORE-SP para ser
                    <strong>REPRESENTANTE COMERCIAL</strong>
                    @else
                    Você pode prosseguir com o pedido de registro no CORE-SP para ser <strong>REPRESENTANTE COMERCIAL</strong>
                    @endif
                </div>
                
                <!-- Modal footer -->
                <div class="modal-footer">
                    <a href="{{ $rotaCancelar }}" class="btn btn-secondary">Cancelar</a>
                    <button type="button" class="btn btn-success" id="submitPreRegistro" value="">Enviar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal hide" id="modalJustificativaPreRegistro">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title"></h4>
            </div>
                
            <!-- Modal body -->
            <div class="modal-body">
                <div class="form-group">
                    <textarea class="form-control" rows="5" maxlength="500" value="" data-clarity-mask="True"></textarea>
                </div>
            </div>
                
            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- The Modal -->
<div class="modal fade" id="modalExcluir">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title"><i class="fas fa-trash-alt text-danger"></i> Excluir <span id="completa-titulo-excluir"></span></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            
            <!-- Modal body -->
            <div class="modal-body">
                Tem certeza que deseja excluir <span id="completa-texto-excluir"></span>: <strong><span id="textoExcluir" data-clarity-mask="True"></span></strong>?
            </div>
            
            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" id="excluir-geral" class="Arquivo-Excluir btn btn-danger" value="">Sim</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
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