@php
  use App\Http\Controllers\Helper;
@endphp

<div class="card-body">
  <dl>
    <dt>Tipo:</dt>
    <dd>{{ $resultado->tipo }}</dd>
    <dt>Prioridade:</dt>
    <dd>{{ $resultado->prioridade }}</dd>
    <dt>Mensagem:</dt>
    <dd>{{ $resultado->mensagem }}</dd>
    <dt>Por:</dt>
    <dd>{{ $resultado->user->nome }}</dd>
    <dt>Data de emissão:</dt>
    <dd>{{ Helper::formataData($resultado->created_at) }}</dd>
    @if($resultado->deleted_at)
    <dt>Data de conclusão:</dt>
    <dd>{{ Helper::formataData($resultado->deleted_at) }}</dd>
    <dd><a href="/admin/chamados/restore/{{ $resultado->idchamado }}">Reabrir</a></dd>
    @endif
  </dl>
</div>