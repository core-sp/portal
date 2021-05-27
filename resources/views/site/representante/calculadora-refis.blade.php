@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
    <div class="nomargin conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Calculadora Refis para anuidades em aberto</h4>
        <div class="linha-lg-mini mb-3"></div>
        @if (!empty($anuidades))
        <p>Selecione as anuidades em aberto para consultar valores com descontos. Atenção, parcela deve ter o valor mínimo de R$ 100,00 (cem reais).</p>

        <h5 class="mt-0 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;Valores</h5>
        <table class="table table-bordered bg-white mb-0">
            <tbody>
                <tr>
                    <td class="ls-meio-neg">Total s/ desconto</td>
                    <td class="ls-meio-neg"><div id="total" value="0">R$ 0,00</div></td>
                </tr>
                <tr>
                    <td class="ls-meio-neg">Total c/ 90% de desconto<p class="text-left"><small>* pagamento à vista, no boleto ou em até 12 (doze) parcelas no cartão de crédito</small></p></td>
                    <td class="ls-meio-neg"><div id="total90" value="0">R$ 0,00</div></td>
                </tr>
                <tr>
                    <td class="ls-meio-neg">Total c/ 80% de desconto<p class="text-left"><small>* pagamento de 2 a 6 parcelas no boleto</small></p></td>
                    <td class="ls-meio-neg"><div id="total80" value="0">R$ 0,00</div></td>
                </tr>
                <tr>
                    <td class="ls-meio-neg">Total c/ 60% de desconto<p class="text-left"><small>* para pagamento de 7 a 12 parcelas no boleto</small></p></td>
                    <td class="ls-meio-neg"><div id="total60" value="0">R$ 0,00</div></td>
                </tr>
            </tbody>
        </table>

        <h5 class="mt-0 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;Anuidades em aberto</h5>
        <table class="table table-bordered bg-white mb-0">
            <thead>
                <tr>
                    <th></th>
                    <th>Descrição</th>
                    <th class="quinze">Valor Original</th>
                    <th class="quinze">Juros</th>
                    <th class="quinze">Multa</th>
                    <th class="quinze">Correção</th>
                    <th class="quinze">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($anuidades as $i => $anuidade)
                    <tr>
                        <td class="ls-meio-neg"><input type="checkbox" id="{{ $i }}" class="refis-checkbox" /></td>
                        <td id="{{ 'descricao_'.$i }}" class="ls-meio-neg">{{ $anuidade['DESCRICAO'] }}</td>
                        <td id="{{ 'valor_'.$i }}" class="ls-meio-neg" value="{{ $anuidade['VALOR'] }}">R$ {{ toReais($anuidade['VALOR']) }}</td>
                        <td id="{{ 'juros_'.$i }}" class="ls-meio-neg" value="{{ $anuidade['JUROS'] }}">R$ {{ toReais($anuidade['JUROS']) }}</td>
                        <td id="{{ 'multa_'.$i }}" class="ls-meio-neg" value="{{ $anuidade['MULTA'] }}">R$ {{ toReais($anuidade['MULTA']) }}</td>
                        <td id="{{ 'correcao_'.$i }}" class="ls-meio-neg" value="{{ $anuidade['CORRECAO'] }}">R$ {{ toReais($anuidade['CORRECAO']) }}</td>
                        <td id="{{ 'total_'.$i }}" class="ls-meio-neg" value="{{ $anuidade['TOTAL'] }}">R$ {{ toReais($anuidade['TOTAL']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @else
        <p>Não foi encontrada nenhuma anuidade em aberto elegível para Refis. Não é possível consultar valores com descontos.</p>
        @endif

    </div>            
</div>

@endsection