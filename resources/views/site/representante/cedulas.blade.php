@extends('site.representante.app')

@section('content-representante')

@if(Session::has('message'))
    <div class="d-block w-100">
        <p class="alert {{ Session::get('class') }}">{!! Session::get('message') !!}</p>
    </div>
@endif

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Solicitação de Cédula</h4>
        <div class="linha-lg-mini mb-2"></div>
        <h5 class="mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;SOLICITAÇÕES</h5>
        @if ($emdia)
            @if ($possuiSolicitacaoCedula)
                <div class="list-group w-100">
                    @foreach ($cedulas as $item)
                        <div class="list-group-item light d-block bg-info">
                            <p class="pb-0 branco">Código: <strong>{{ $item->id }}</strong></p>
                            <p class="pb-0 branco">Data de solicitação: <strong>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</strong></p>
                            <p class="pb-0 branco">CEP: <strong>{{ $item->cep }}</strong></p>
                            <p class="pb-0 branco">Estado: <strong>{{ $item->estado }}</strong></p>
                            <p class="pb-0 branco">Município: <strong>{{ $item->municipio }}</strong></p>
                            <p class="pb-0 branco">Status: <strong class="{{ $item->status === 'Recusado' ? 'text-dark' : 'text-warning' }} text-uppercase">{{ $item->status }}</strong></p>
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
            @if (!$possuiSolicitacaoCedulaEmAndamento)
                <div class="d-block mt-2 mb-3">
                    <a href="{{ route('representante.inserirSolicitarCedulaView') }}" class="btn btn-primary link-nostyle branco">Adicionar nova solicitação de cédula</a>
                </div>
            @else
                <div class="contatos-table space-single mt-3">
                    <p class="light pb-0">Você já possui uma solicitação de cédula em andamento. Por favor, aguarde a atualização do status para solicitar novamente, se necessário.</p>
                </div>
            @endif
        @else
            <div class="contatos-table space-single mt-3">
                <p class="light pb-0">Infelizmente não é possível solicitar cédula no momento. Por favor, entre em contato com o atendimento para mais informações.</p>
            </div>
        @endif
    </div>
</div>

@endsection