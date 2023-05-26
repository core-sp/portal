@extends('site.userExterno.app')

@section('content-user-externo')

@if(Session::has('message'))
<div class="d-block w-100 alert alert-dismissible {{ Session::get('class') }}">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {!! Session::get('message') !!}
</div>
@endif

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Solicitações de registro gerenciados pela Contabilidade</h4>
        <div class="linha-lg-mini mb-2"></div>
        <a class="btn btn-success text-white" href="{{ route('externo.preregistro.view') }}">
            Criar solicitação
        </a>
        <div class="d-block mt-2 mb-3">
            <p>
                Listagem das solicitações de registro que os <strong>Representantes Comerciais</strong> relacionaram a sua contabilidade e as solicitações criadas pela própria contabilidade.
            </p>
        </div>
        @if(isset($resultados) && $resultados->total() > 0)
        <div class="list-group w-100">
            @foreach($resultados as $solicitacao)
            <div class="list-group-item light d-block">
                <p class="pb-0">ID: <strong>{{ $solicitacao->id }}</strong></p>
                <p class="pb-0">CPF / CNPJ: <strong>{{ formataCpfCnpj($solicitacao->userExterno->cpf_cnpj) }}</strong></p>
                <p class="pb-0">Nome: <strong>{{ $solicitacao->userExterno->nome }}</strong></p>
                <p class="pb-0">Status: {!! $solicitacao->getLabelStatusUser() !!}</p>
                <p class="pb-0 pt-2">
                    <a class="btn btn-primary btn-sm text-white" href="{{ route('externo.preregistro.view', $solicitacao->id) }}">
                         Visualizar solicitação
                    </a>
                </p>
            </div>
            @endforeach
        </div>
        @else
        <div class="contatos-table space-single">
            <p class="light pb-0">Você não possui nenhuma solicitação de registro relacionada.</p>
        </div>
        @endif
        <div class="float-left mt-3">
        @if($resultados instanceof \Illuminate\Pagination\LengthAwarePaginator)
            {{ $resultados->appends(request()->input())->links() }}
        @endif
        </div>
    </div>
</div>

@endsection