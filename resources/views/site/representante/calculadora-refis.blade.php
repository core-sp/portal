@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
    <div class="nomargin conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Calculadora Refis</h4>
        <div class="linha-lg-mini mb-3"></div>
        @if (!empty($anuidades))
        <p>Selecione as anuidades em aberto para consultar valores com descontos.</p>
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

        <div class="contatos-table space-single">
            <h3 class="mt-0 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;Total s/ desconto: <div id="total" value="0">R$ 0,00</div></h3>
            <h3 class="mt-0 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;Total c/ 90% de desconto: <div id="total90" value="0">R$ 0,00</div></h3>
            <h3 class="mt-0 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;Total c/ 80% de desconto: <div id="total80" value="0">R$ 0,00</div></h3>
            <h3 class="mt-0 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;Total c/ 60% de desconto: <div id="total60" value="0">R$ 0,00</div></h3>
        </div>

        @else
        <p>Não foi encontrado nenhuma anuidade em aberto elegível para Refis. Não é possível consultar valores com descontos.</p>
        @endif


    </div>            
</div>

@endsection