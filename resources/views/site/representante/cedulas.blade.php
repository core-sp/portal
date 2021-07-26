@extends('site.representante.app')

@section('content-representante')

@if(Session::has('message'))
    <div class="d-block w-100">
        <p class="alert {{ Session::get('class') }}">{!! Session::get('message') !!}</p>
    </div>
@endif

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Solicitações de Cédula</h4>
        <div class="linha-lg-mini mb-2"></div>
        <h5 class="mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;SOLICITAÇÕES</h5>
        @if ($possuiSolicitacaoCedulas)
            <div class="list-group w-100">
                @foreach ($cedulas as $item)
                    <div class="list-group-item light d-block bg-info">
                        <p class="pb-0 branco">Endereço: <strong>{{ $item->logradouro }}, {{ $item->numero }} {{ isset($item->complemento) ? ' - ' . $item->complemento : '' }}</strong></p>
                        <p class="pb-0 branco">Bairro: <strong>{{ $item->bairro }}</strong></p>
                        <p class="pb-0 branco">Município: <strong>{{ $item->municipio }}</strong></p>
                        <p class="pb-0 branco">Estado: <strong>{{ $item->estado }}</strong></p>
                        <p class="pb-0 branco">CEP: <strong>{{ $item->cep }}</strong></p>
                        <p class="pb-2 branco"><small><i>(Novo endereço para correspondência)</i></small></p>
                        <p class="pb-0 branco">Status: <strong class="{{ $item->status === 'Reprovado' ? 'text-danger' : 'text-warning' }} text-uppercase">{{ $item->status }}</strong></p>
                        @isset($item->justificativa)
                            <p class="pb-0 lh-1 cinza-claro"><small class="light">{!! '—————<br>' . $item->justificativa !!}</small></p>
                        @endisset
                    </div>
                @endforeach
            </div>
        @else
            <div class="contatos-table space-single">
                <p class="light pb-0">Você não possui nenhuma solicitação de cédula.</p>
            </div>
        @endif
        <div class="d-block mt-2 mb-3">
            <a href="{{ route('representante.inserirSolicitarCedulaView') }}" class="btn btn-primary link-nostyle branco">Adicionar nova solicitação de cédula</a>
        </div>
    </div>
</div>

@endsection