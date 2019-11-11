<div class="list-group-item light d-block">
    <p class="pb-2"><i>{{ $cobranca['ITENSBOL'] }}</i></p>
    <p class="pb-0">Valor: <strong>R$ {{ $cobranca['VALORBOLETO'] }}</strong></p>
    <p class="pb-0">Data de vencimento: <strong>{{ formataDataGerenti($cobranca['DATAVENCIMENTO']) }}</strong></p>
    <div class="mt-2 mb-1">
        <a 
            href="{{ 'https://boletoonline.caixa.gov.br/ecobranca/SIGCB/imprimir/0779951/' . $cobranca['NOSSONUMERO'] }}"
            class="btn btn-sm btn-info link-nostyle branco"
        >Imprimir Boleto</a>
    </div>
</div>