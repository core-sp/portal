@extends('site.representante.app')

@section('content-representante')

@if(Session::has('message'))
    <div class="d-block w-100">
        <p class="alert {{ Session::get('class') }}">{{ Session::get('message') }}</p>
    </div>
@endif

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Dados gerais</h4>
        <div class="linha-lg-mini mb-3"></div>
        <div class="contatos-table">
            <div class="contato-single b-dashed">
                <p class="pb-0">Nome: <strong>{{ Auth::guard('representante')->user()->nome }}</strong></p>
            </div>
            <div class="contato-single b-dashed">
                <p class="pb-0">Registro Core: <strong>{{ Auth::guard('representante')->user()->registro_core }}</strong></p>
            </div>
            <div class="contato-single b-dashed">
                <p class="pb-0">CPF/CNPJ: <strong>{{ Auth::guard('representante')->user()->cpf_cnpj }}</strong></p>
            </div>
            @foreach (Auth::guard('representante')->user()->dadosGerais() as $key => $dado)
                @if (Auth::guard('representante')->user()->tipoPessoa() === 'PF')
                    <div class="contato-single {{ $loop->last ? '' : 'b-dashed' }}">
                        <p class="pb-0">{{ $key }}: <strong>{!! empty($dado) ? '----------' : $dado !!}</strong></p>
                    </div>
                @else
                    <div class="contato-single {{ $loop->last ? '' : 'b-dashed' }}">
                        <p class="pb-0">{{ $key }}: <strong>{!! empty($dado) ? '----------' : $dado !!}</strong></p>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>

@endsection