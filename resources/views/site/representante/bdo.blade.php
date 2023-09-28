@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Oportunidades</h4>
        <div class="linha-lg-mini mb-1"></div>
        <h5 class="mb-1">
            <span class="badge badge-success p-2">Outras oportunidades 
                <a href="{{ route('bdosite.index') }}" target="_blank" class="text-white">
                    <u>aqui</u>
                </a>
            </span>
        </h5>
    @if($segmento)
        <div class="contatos-table space-single bg-info mb-2">
            <p class="light pb-0 text-white" data-clarity-mask="True">
                @if($bdo->count() == 0)
                Não foi encontrada nenhuma oportunidade <strong>em andamento</strong> para o seu segmento - <strong>{{ $segmento }}</strong> e sua seccional - <strong>{{ $seccional }}</strong>
                @else
                {!! $bdo->count() == 1 ? 'Foi encontrada 1 oportunidade' : 'Foram encontradas <strong>'.$bdo->count().'</strong> oportunidades' !!} <strong>em andamento</strong> para o seu segmento - <strong>{{ $segmento }}</strong> e sua seccional - <strong>{{ $seccional }}</strong>
                @endif
            </p>
        </div>
        <div class="contatos-table">
            @foreach($bdo as $b)
                <div class="contato-single {{ $loop->last ? '' : 'b-dashed' }}">
                    <p class="pb-0">
                    <strong>{{ $b->titulo }} -</strong> Essa empresa possui {{ $b->vagasdisponiveis }} {{ $b->vagasdisponiveis > 1 ? 'vagas disponíveis' : 'vaga disponível' }}! 
                    <a href="{{ $b->observacao }}" target="_blank" class="alert-link"><u>Confira aqui</u></a>.
                    </p>
                </div>
            @endforeach
        </div>
    @else
        <div class="contatos-table space-single">
            <p class="light pb-0">Você não cadastrou o segmento. Por favor, atualize seus dados para receber as oportunidades.</p>
        </div>
    @endif
    </div>
</div>

@endsection