@extends('site.userExterno.app')

@section('content-user-externo')

@if(Session::has('message'))
    <div class="d-block w-100">
        <p class="alert {{ Session::get('class') }}">{{ Session::get('message') }}</p>
    </div>
@endif

<div class="representante-content w-100">

    <!-- Nav tabs -->
    <ul class="menu-registro nav nav-pills flex-column">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#parte1_PF_PJ">
                Contabilidade - <i class="icon fa fa-check text-success"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte2_PF_PJ">
                Dados Gerais - <i class="icon fa fa-times text-danger"></i> <span class="text-danger">R27, R28</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte3_PF_PJ">
                Endereço
            </a>
        </li>
        @if(strlen($resultado->userExterno->cpf_cnpj) == 14)
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte4_PJ">
                Contato / RT
            </a>
        </li>
        @endif
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte4_PF_parte5_PJ">
                Canal de Relacionamento
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte5_PF_parte6_PJ">
                Anexos
            </a>
        </li>
    </ul>

    <hr class="mb-0">

    <form method="POST" enctype="multipart/form-data" id="inserirRegistro" class="cadastroRepresentante">
        @csrf
        @if(isset($resultado->id))
            @method('PUT')
        @endif

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
                    'codUser' => $codigos[$classes[6]],
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
                    'codUser' => $codigos[$classes[6]],
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

        <div class="form-row">
            <div class="col-sm mb-2-576">
                <button class="btn btn-success float-left" type="submit">Enviar para análise</button>
            </div>
        </div>
    </form>

    <!-- The Modal -->
    <div class="modal hide" id="modalLoadingPreRegistro">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!-- Modal body -->
                <div id="modalLoadingBody" class="modal-body text-center"></div>
            </div>
        </div>
    </div>
</div>

@endsection