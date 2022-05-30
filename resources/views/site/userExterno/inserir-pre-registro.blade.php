@extends('site.userExterno.app')

@section('content-user-externo')

@php
    $abas = ['Contabilidade', 'Dados Gerais', 'Endereço', 'Contato / RT', 'Canal de Relacionamento', 'Anexos'];
@endphp

@if($errors->count() > 0)
    @php
        $temp = $abas;
        $arrayAbas = [
                'cnpj_contabil' => $abas[0],
                'nome_contabil' => $abas[0],
                'email_contabil' => $abas[0],
                'nome_contato_contabil' => $abas[0],
                'telefone_contabil' => $abas[0],
                'registro_secundario' => $abas[1],
                'ramo_atividade' => $abas[1],
                'segmento' => $abas[1],
                'idregional' => $abas[1],
                'nome_social' => $abas[1],
                'sexo' => $abas[1],
                'dt_nascimento' => $abas[1],
                'estado_civil' => $abas[1],
                'nacionalidade' => $abas[1],
                'naturalidade' => $abas[1],
                'nome_mae' => $abas[1],
                'nome_pai' => $abas[1],
                'identidade' => $abas[1],
                'orgao_emissor' => $abas[1],
                'dt_expedicao' => $abas[1],    
                'razao_social' => $abas[1],
                'capital_social' => $abas[1],
                'nire' => $abas[1],
                'tipo_empresa' => $abas[1],
                'dt_inicio_atividade' => $abas[1],
                'inscricao_municipal' => $abas[1],
                'inscricao_estadual' => $abas[1],
                'cep' => $abas[2],
                'bairro' => $abas[2],
                'logradouro' => $abas[2],
                'numero' => $abas[2],
                'complemento' => $abas[2],
                'cidade' => $abas[2],
                'uf' => $abas[2],
                'checkEndEmpresa' => $abas[2],
                'cep_empresa' => $abas[2],
                'bairro_empresa' => $abas[2],
                'logradouro_empresa' => $abas[2],
                'numero_empresa' => $abas[2],
                'complemento_empresa' => $abas[2],
                'cidade_empresa' => $abas[2],
                'uf_empresa' => $abas[2],
                'nome_rt' => $abas[3],
                'nome_social_rt' => $abas[3],
                'registro' => $abas[3],
                'sexo_rt' => $abas[3],
                'dt_nascimento_rt' => $abas[3],
                'cpf_rt' => $abas[3],
                'identidade_rt' => $abas[3],
                'orgao_emissor_rt' => $abas[3],
                'dt_expedicao_rt' => $abas[3],
                'cep_rt' => $abas[3],
                'bairro_rt' => $abas[3],
                'logradouro_rt' => $abas[3],
                'numero_rt' => $abas[3],
                'complemento_rt' => $abas[3],
                'cidade_rt' => $abas[3],
                'uf_rt' => $abas[3],
                'nome_mae_rt' => $abas[3],
                'nome_pai_rt' => $abas[3],
                'tipo_telefone' => $abas[4],
                'telefone' => $abas[4],
                'tipo_telefone_1' => $abas[4],
                'telefone_1' => $abas[4],
                'path' => $abas[5],
            ];
    @endphp

    <div class="d-block w-100">
        <p class="alert alert-danger">Foi encontrado erro em: 
        @foreach($arrayAbas as $nome => $aba)
            @if($errors->has($nome) && in_array($aba, $temp))
                <strong>{{ $aba }}</strong> <span class="text-dark">*</span>
                @php
                    unset($temp[array_search($aba, $temp)]);
                @endphp
            @endif
        @endforeach
        </p>
    </div>
@endif

<div class="representante-content w-100">

    <!-- Nav tabs -->
    <ul class="menu-registro nav nav-pills flex-column">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#parte1_PF_PJ">
                {{ $abas[0] }} - <i class="icon fa fa-check text-success"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#parte2_PF_PJ">
                {{ $abas[1] }} - <i class="icon fa fa-times text-danger"></i> <span class="text-danger">R27, R28</span>
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

    <form method="POST" enctype="multipart/form-data" id="inserirRegistro" class="cadastroRepresentante" autocomplete="off">
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