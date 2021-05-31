@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
    <div class="nomargin conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Simulador Refis para anuidades em aberto</h4>
        <div class="linha-lg-mini mb-3"></div>
        @if ($total !== 0)
        <p>Abaixo valores com descontos disponíveis com seus respectivos parcelamentos. Atenção, parcela deve ter o valor mínimo de R$ 100,00 (cem reais).</p>

        <h5 class="mt-0 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;VALORES</h5>
        <table class="table table-bordered bg-white mb-0">
            <tbody>
                <tr>
                    <td class="ls-meio-neg">Total s/ desconto</td>
                    <td class="ls-meio-neg"><div id="total" value="{{ $total }}">R$ {{ toReais($total) }}</div></td>
                </tr>
                <tr>
                    @if($nParcelas90[0] !== 0)
                        <td class="ls-meio-neg">Total c/ 90% de desconto<p class="text-left"><small>* pagamento à vista, no boleto ou em até {{ end($nParcelas90) }} parcelas no cartão de crédito</small></p></td>
                        <td class="ls-meio-neg"><div id="total90" value="{{ $total90 }}">R$ {{ toReais($total90) }}</div></td>
                        <td class="ls-meio-neg">
                            <select id="90" class="form-control nParcela">
                                @foreach($nParcelas90 as $n)
                                    <option value="{{ $n }}">{{ $n }}x</option>
                                @endforeach
                        </td>
                        <td id="parcelamento90" class="ls-meio-neg">R$ {{ toReais($total90/$nParcelas90[0]) }}</td>
                    @else
                        <td class="ls-meio-neg">Total c/ 90% de desconto<p class="text-left vermelho"><small>* não disponível devido ao valor mínimo de parcela</small></p></td>
                        <td class="ls-meio-neg"><div id="total90" value="0">-</div></td>
                        <td class="ls-meio-neg">-</td>
                        <td class="ls-meio-neg">-</td>
                    @endif
                </tr>
                <tr>
                    @if($nParcelas80[0] !== 0)
                        <td class="ls-meio-neg">Total c/ 80% de desconto<p class="text-left"><small>* pagamento de {{ $nParcelas80[0] }} a {{ end($nParcelas80) }} parcelas no boleto</small></p></td>
                        <td class="ls-meio-neg"><div id="total80" value="{{ $total80 }}">R$ {{ toReais($total80) }}</div></td>
                        <td class="ls-meio-neg">
                            <select id="80" class="form-control nParcela">
                                @foreach($nParcelas80 as $n)
                                    <option value="{{ $n }}">{{ $n }}x</option>
                                @endforeach
                        </td>
                        <td id="parcelamento80" class="ls-meio-neg">R$ {{ toReais($total80/$nParcelas80[0]) }}</td>
                    @else
                        <td class="ls-meio-neg">Total c/ 80% de desconto<p class="text-left vermelho"><small>* não disponível devido ao valor mínimo de parcela</small></p></td>
                        <td class="ls-meio-neg"><div id="total80" value="0">-</div></td>
                        <td class="ls-meio-neg">-</td>
                        <td class="ls-meio-neg">-</td>
                    @endif
                </tr>
                <tr>
                    @if($nParcelas60[0] !== 0)
                        <td class="ls-meio-neg">Total c/ 60% de desconto<p class="text-left"><small>* pagamento de {{ $nParcelas60[0] }} a {{ end($nParcelas60) }} parcelas no boleto</small></p></td>
                        <td class="ls-meio-neg"><div id="total60" value="{{ $total60 }}">R$ {{ toReais($total60) }}</div></td>
                        <td class="ls-meio-neg">
                            <select id="60" class="form-control nParcela">
                                @foreach($nParcelas60 as $n)
                                    <option value="{{ $n }}">{{ $n }}x</option>
                                @endforeach
                        </td>
                        <td id="parcelamento60" class="ls-meio-neg">R$ {{ toReais($total60/$nParcelas60[0]) }}</td>
                    @else
                    <td class="ls-meio-neg">Total c/ 60% de desconto<p class="text-left vermelho"><small>* não disponível devido ao valor mínimo de parcela</small></p></td>
                        <td class="ls-meio-neg"><div id="total60" value="0">-</div></td>
                        <td class="ls-meio-neg">-</td>
                        <td class="ls-meio-neg">-</td>
                    @endif
                </tr>
            </tbody>
        </table>

        <h5 class="mt-3 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;ANUIDADES COBRADAS</h5>
        <table class="table table-bordered bg-white mb-0">
            <tbody>
                @foreach ($anuidadesRefis as $anuidade)
                    <tr>
                        <td class="ls-meio-neg">
                            {{ $anuidade }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @else
        <p>Não é possível simular valores Refis para quitar anuidades em aberto devido a situação do representante comercial. Em caso de dúvidas, por favor contactar o CORE-SP.</p>
        @endif

    </div>            
</div>

@endsection