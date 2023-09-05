@extends('site.representante.app')

@section('content-representante')

@if(Session::has('message'))
    <div class="d-block w-100">
        <p class="alert {{ Session::get('class') }}">{!! Session::get('message') !!}</p>
    </div>
@endif

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Endereço de correspondência</h4>
        <div class="linha-lg-mini mb-2"></div>
        <h5 class="mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;SOLICITAÇÕES</h5>
        @if ($possuiSolicitacaoEnderecos)
            <div class="list-group w-100">
                @foreach ($solicitacoesEnderecos as $item)
                    <div class="list-group-item light d-block bg-info" data-clarity-mask="True">
                        <p class="pb-0 branco">Endereço: <strong>{{ $item->logradouro }}, {{ $item->numero }} {{ isset($item->complemento) ? ' - ' . $item->complemento : '' }}</strong></p>
                        <p class="pb-0 branco">Bairro: <strong>{{ $item->bairro }}</strong></p>
                        <p class="pb-0 branco">Município: <strong>{{ $item->municipio }}</strong></p>
                        <p class="pb-0 branco">Estado: <strong>{{ $item->estado }}</strong></p>
                        <p class="pb-0 branco">CEP: <strong>{{ $item->cep }}</strong></p>
                        <p class="pb-2 branco"><small><i>(Novo endereço para correspondência)</i></small></p>
                        <p class="pb-0 branco">Status: <strong class="{{ $item->status === 'Recusado' ? 'text-dark' : 'text-warning' }} text-uppercase">{{ $item->status }}</strong></p>
                        @isset($item->observacao)
                            <p class="pb-0 lh-1 cinza-claro"><small class="light">{!! '—————<br>' . $item->observacao !!}</small></p>
                        @endisset
                    </div>
                @endforeach
            </div>
        @else
            <div class="contatos-table space-single">
                <p class="light pb-0">Você não possui nenhuma solicitação de inclusão de endereço.</p>
            </div>
        @endif
        <div class="d-block mt-2 mb-3">
            <a href="{{ route('representante.inserir-endereco.view') }}" class="btn btn-primary link-nostyle branco">Adicionar novo endereço de correspondência</a>
        </div>
        <h5 class="mt-3 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;END. DE CORRESPONDÊNCIA CADASTRADO</h5>
        <div class="contatos-table space-single">
            @if($endereco['CEP'] !== null)
                @foreach ($endereco as $key => $item)
                    <p class="pb-0" data-clarity-mask="True">{{ $key }}: <strong class="text-uppercase">{{ !empty($item) ? $item : '-----' }}</strong></p>
                @endforeach
            @else
                <p class="pb-0">Nenhum endereço cadastrado.</p>
            @endif
        </div>
    </div>
</div>

@endsection