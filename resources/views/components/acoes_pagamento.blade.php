@if(isset($pagamento) && !$pagamento->cancelado())
    <a href="{{ route('pagamento.visualizar', ['boleto' => $pagamento->boleto_id, 'pagamento' => $pagamento->getIdPagamento()]) }}" class="btn btn-info btn-sm text-white text-decoration-none">Detalhes</a>
    @if($pagamento->canCancel())
    &nbsp;&nbsp;<a href="{{ route('pagamento.cancelar.view', ['boleto' => $pagamento->boleto_id, 'pagamento' => $pagamento->getIdPagamento()]) }}" class="btn btn-danger btn-sm text-white text-decoration-none">Cancelar</a>
    @endif
@elseif($podePagar)
    <a href="{{ route('pagamento.view', $boleto_id) }}" class="btn btn-success btn-sm text-white text-decoration-none">Realizar pagamento</a>
@endif