@extends('site.representante.app')

@section('content-representante')

@if(Session::has('message'))
    <div class="d-block w-100">
        <p class="alert {{ Session::get('class') }}">{{ Session::get('message') }}</p>
    </div>
@endif

<div class="representante-content w-100">
    <div class="row nomargin conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Dados gerais</h4>
        <div class="linha-lg-mini mb-3"></div>
        <div class="contato-single b-dashed">
            <p class="pb-0">Nome: <strong>{{ Auth::guard('representante')->user()->nome }}</strong></p>
        </div>
        <div class="contato-single b-dashed">
            <p class="pb-0">CPF/CNPJ: <strong>{{ Auth::guard('representante')->user()->cpf_cnpj }}</strong></p>
        </div>
        <div class="contato-single b-dashed">
            <p class="pb-0">Registro Core: <strong>{{ Auth::guard('representante')->user()->registro_core }}</strong></p>
        </div>
        @foreach (Auth::guard('representante')->user()->dadosGerais() as $dado)
            @if (Auth::guard('representante')->user()->tipoPessoa() === 'PF')
                <div class="contato-single {{ $loop->last ? '' : 'b-dashed' }}">
                    <p class="pb-0">{{ $dado['TPD_DESCRICAO'] }}: <strong>{{ empty($dado['DAD_VALOR']) ? '----------' : utf8_encode($dado['DAD_VALOR']) }}</strong></p>
                </div>
            @else
                <div class="contato-single b-dashed">
                    <p class="pb-0">Data de Registro Social: <strong>{{ formataDataGerenti($dado['ASS_DT_REG_SOCIAL']) }}</strong></p>
                </div>
                <div class="contato-single b-dashed">
                    <p class="pb-0">Data de Admissão: <strong>{{ formataDataGerenti($dado['ASS_DT_ADMISSAO']) }}</strong></p>
                </div>
                <div class="contato-single b-dashed">
                    <p class="pb-0">Regional: <strong>{{ utf8_encode($dado['REGIONAL']) }}</strong></p>
                </div>
                <div class="contato-single">
                    <p class="pb-0">NIRE: <strong>{{ empty($dado['NIRE']) ? '-----' : $dado['NIRE'] }}</strong></p>
                </div>
            @endif
        @endforeach
    </div>
</div>

@endsection