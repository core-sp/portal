@extends('site.representante.app')

@section('content-representante')

<div class="representante-content">
    
    @if(Session::has('message-cartao'))
    <p class="alert {{ Session::get('class') }}">{!! Session::get('message-cartao') !!}</p>
    <div class="linha-lg-mini mb-3"></div>
    @endif

    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Home</h4>
        <div class="linha-lg-mini mb-3"></div>
        <p class="pt-2">Seja bem-vindo à nova <strong>Área do Representante Comercial</strong> do <strong>Core-SP.</strong> Este é o seu espaço para conferir, atualizar e regularizar todas as suas pendências junto ao Conselho.</p>
        <p class="pb-0">Estamos trabalhando para tornar este espaço cada vez mais completo e com mais funcionalidades. Fique por dentro das <a href="/noticias">notícias</a> do Core-SP para saber todas as novidades.</p>
        <h5 class="mt-3 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;SITUAÇÃO</h5>
        <p class="pb-0">{!! $status !!}</p>
        <h5 class="mt-3 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;ANUIDADE {{ $ano }}</h5>
        @if(isset($nrBoleto))
            <p class="pb-0">
                <a href="https://boletoonline.caixa.gov.br/ecobranca/SIGCB/imprimir/0779951/{{ $nrBoleto }}">
                    <button class="btn btn-success btn-lg" onclick="clickBoleto({{ 'Anuidade ' . $ano . ' (Parcela Única)' }})">
                        <i class="fas fa-download"></i>&nbsp;&nbsp;BAIXAR BOLETO
                    </button>
                </a>
            </p>
        @else
            <p class="pb-0">Já pago ou indisponível. Confira mais detalhes na guia de <a href="/representante/situacao-financeira">Situação Financeira</a>.</p>
        @endif

        <br>
        <!-- Temporário -->
        <a href="{{ route('representante.pagamento.view', 1) }}" class="btn btn-primary text-white text-decoration-none">Teste Realizar Pagamento On-line</a>
        <a href="{{ route('representante.cancelar.pagamento.cartao.view', ['boleto' => '1', 'pagamento' => '1']) }}" class="btn btn-danger text-white text-decoration-none">Teste Cancelar Pagamento On-line</a>
        
    </div>
</div>

@endsection