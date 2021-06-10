@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
    <div class="nomargin conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Simulador Refis para anuidades em aberto</h4>
        <div class="linha-lg-mini mb-3"></div>
        @if ($valoresRefis['total'] !== 0)
        <p>Abaixo valores com descontos disponíveis com seus respectivos parcelamentos. Atenção, parcela deve ter o valor mínimo de R$ 100,00 (cem reais).</p>

        <h5 class="mt-3 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;ANUIDADES COBRADAS</h5>
        <table class="table table-bordered bg-white mb-0">
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th class="quinze">Valor Original</th>
                    <th class="quinze">Juros</th>
                    <th class="quinze">Multa</th>
                    <th class="quinze">IPCA</th>
                    <th class="quinze">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($valoresRefis['anuidadesRefis'] as $anuidade)
                    <tr>
                        <td class="ls-meio-neg">{{ $anuidade['descricao'] }}</td>
                        <td class="ls-meio-neg" value="{{ $anuidade['valor'] }}">R$ {{ toReais($anuidade['valor']) }}</td>
                        <td class="ls-meio-neg" value="{{ $anuidade['juros'] }}">R$ {{ toReais($anuidade['juros']) }}</td>
                        <td class="ls-meio-neg" value="{{ $anuidade['multa'] }}">R$ {{ toReais($anuidade['multa']) }}</td>
                        <td class="ls-meio-neg" value="{{ $anuidade['correcao'] }}">R$ {{ toReais($anuidade['correcao']) }}</td>
                        <td class="ls-meio-neg" value="{{ $anuidade['total'] }}">R$ {{ toReais($anuidade['total']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h5 class="mt-0 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;VALORES</h5>
        <table class="table table-bordered bg-white mb-0">
            <tbody>
                <tr>
                    <td class="ls-meio-neg">Total s/ desconto</td>
                    <td class="ls-meio-neg"><div id="total" value="{{ $valoresRefis['total'] }}">R$ {{ toReais($valoresRefis['total']) }}</div></td>
                </tr>
                <tr>
                    @if($valoresRefis['nParcelas90'][0] !== 0)
                        <td class="ls-meio-neg">Total c/ 90% de desconto sobre juros e multas<p class="text-left">
                        @if(count($valoresRefis['nParcelas90']) === 1)
                            <small>* pagamento à vista, no boleto ou no cartão de crédito</small>
                        @else
                            <small>* pagamento à vista, no boleto ou em até {{ end($valoresRefis['nParcelas90']) }} parcelas no cartão de crédito</small>
                        @endif
                        </p></td>
                        <td class="ls-meio-neg"><div id="total90" value="{{ $valoresRefis['total90'] }}">R$ {{ toReais($valoresRefis['total90']) }}<p class="text-left verde"><small><strong>Desconto: R$ {{ toReais($valoresRefis['total'] - $valoresRefis['total90']) }}</strong></small></p></div></td>
                        <td class="ls-meio-neg">
                            <select id="90" class="form-control nParcela">
                                @foreach($valoresRefis['nParcelas90'] as $n)
                                    <option value="{{ $n }}">{{ $n }}x</option>
                                @endforeach
                        </td>
                        <td id="parcelamento90" class="ls-meio-neg">R$ {{ toReais($valoresRefis['total90']/$valoresRefis['nParcelas90'][0]) }}</td>
                    @else
                        <td class="ls-meio-neg">Total c/ 90% de desconto sobre juros e multas<p class="text-left vermelho"><small>* não disponível devido ao valor mínimo de parcela</small></p></td>
                        <td class="ls-meio-neg"><div id="total90" value="0">-</div></td>
                        <td class="ls-meio-neg">-</td>
                        <td class="ls-meio-neg">-</td>
                    @endif
                </tr>
                <tr>
                    @if($valoresRefis['nParcelas80'][0] !== 0)
                        <td class="ls-meio-neg">Total c/ 80% de desconto sobre juros e multas<p class="text-left">
                        @if(count($valoresRefis['nParcelas80']) === 1)
                            <small>* pagamento em {{ $valoresRefis['nParcelas80'][0] }} parcelas no boleto</small>
                        @else
                            <small>* pagamento de {{ $valoresRefis['nParcelas80'][0] }} a {{ end($valoresRefis['nParcelas80']) }} parcelas no boleto</small>
                        @endif
                        </p></td>
                        <td class="ls-meio-neg"><div id="total80" value="{{ $valoresRefis['total80'] }}">R$ {{ toReais($valoresRefis['total80']) }}<p class="text-left verde"><small><strong>Desconto: R$ {{ toReais($valoresRefis['total'] - $valoresRefis['total80']) }}</strong></small></p></div></td>
                        <td class="ls-meio-neg">
                            <select id="80" class="form-control nParcela">
                                @foreach($valoresRefis['nParcelas80'] as $n)
                                    <option value="{{ $n }}">{{ $n }}x</option>
                                @endforeach
                        </td>
                        <td id="parcelamento80" class="ls-meio-neg">R$ {{ toReais($valoresRefis['total80']/$valoresRefis['nParcelas80'][0]) }}</td>
                    @else
                        <td class="ls-meio-neg">Total c/ 80% de desconto sobre juros e multas<p class="text-left vermelho"><small>* não disponível devido ao valor mínimo de parcela</small></p></td>
                        <td class="ls-meio-neg"><div id="total80" value="0">-</div></td>
                        <td class="ls-meio-neg">-</td>
                        <td class="ls-meio-neg">-</td>
                    @endif
                </tr>
                <tr>
                    @if($valoresRefis['nParcelas60'][0] !== 0)
                        <td class="ls-meio-neg">Total c/ 60% de desconto sobre juros e multas<p class="text-left">
                        @if(count($valoresRefis['nParcelas60']) === 1)
                            <small>* pagamento em {{ $valoresRefis['nParcelas60'][0] }} parcelas no boleto</small>
                        @else
                            <small>* pagamento de {{ $valoresRefis['nParcelas60'][0] }} a {{ end($valoresRefis['nParcelas60']) }} parcelas no boleto</small>
                        @endif
                        </p></td>
                        <td class="ls-meio-neg"><div id="total60" value="{{ $valoresRefis['total60'] }}">R$ {{ toReais($valoresRefis['total60']) }}<p class="text-left verde"><small><strong>Desconto: R$ {{ toReais($valoresRefis['total'] - $valoresRefis['total60']) }}</strong></small></p></div></td>
                        <td class="ls-meio-neg">
                            <select id="60" class="form-control nParcela">
                                @foreach($valoresRefis['nParcelas60'] as $n)
                                    <option value="{{ $n }}">{{ $n }}x</option>
                                @endforeach
                        </td>
                        <td id="parcelamento60" class="ls-meio-neg">R$ {{ toReais($valoresRefis['total60']/$valoresRefis['nParcelas60'][0]) }}</td>
                    @else
                    <td class="ls-meio-neg">Total c/ 60% de desconto sobre juros e multas<p class="text-left vermelho"><small>* não disponível devido ao valor mínimo de parcela</small></p></td>
                        <td class="ls-meio-neg"><div id="total60" value="0">-</div></td>
                        <td class="ls-meio-neg">-</td>
                        <td class="ls-meio-neg">-</td>
                    @endif
                </tr>
            </tbody>
        </table>

        @else
        <p>Não é possível simular valores Refis para quitar anuidades em aberto devido a situação do representante comercial. Em caso de dúvidas, por favor contactar o CORE-SP.</p>
        @endif

    </div>            
</div>

@endsection