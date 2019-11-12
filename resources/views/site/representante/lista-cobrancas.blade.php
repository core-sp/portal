@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
    <div class="nomargin conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Situação Financeira</h4>
        <div class="linha-lg-mini mb-3"></div>
        <p>Confira abaixo sua lista de cobranças vigentes, as quais ainda estão <strong>dentro do prazo de vencimento.</strong></p>
        <h5 class="mt-0 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;ANUIDADE</h5>
        @if (!empty(Auth::guard('representante')->user()->cobrancas()['extrato']))
            <div class="list-group">
                @foreach (Auth::guard('representante')->user()->cobrancas()['extrato'] as $cobranca)
                    @include('site.representante.inc.cobranca')                        
                @endforeach
            </div>
        @else
            <p class="light">Você não possui nenhuma cobrança disponível para pagamento.</p>
        @endif
        <h5 class="mt-3 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;PARCELAMENTO</h5>
        @if (!empty(Auth::guard('representante')->user()->cobrancas()['parcelamento']))
            <div class="list-group">
                @foreach (Auth::guard('representante')->user()->cobrancas()['parcelamento'] as $cobranca)
                    @include('site.representante.inc.cobranca')                        
                @endforeach
            </div>
        @else
            <p class="light">Você não possui nenhum parcelamento disponível para pagamento.</p>
        @endif
    </div>            
</div>

@endsection