@php
  use App\Http\Controllers\Helper;
@endphp

<div class="card-body">
  @if(isset($resultado->resposta))
  <div class="row">
    <div class="col">
      <dl class="mb-0">
        <dt>Resposta do CTI:</dt>
        <dd class="mb-0">{{ $resultado->resposta }}</dd>
      </dl>
    </div>
  </div>
  <hr>
  @endif
  <div class="row">
    @if(isset($resultado->img))
    <div class="col-4">
    @else
    <div class="col">
    @endif
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
          @if(session('idperfil') === 1)
          <dd><a href="/admin/chamados/restore/{{ $resultado->idchamado }}">Reabrir</a></dd>
          @endif
        @else
        <hr>
        <form method="POST" action="/admin/chamados/apagar/{{ $resultado->idchamado }}" class="d-inline">
          @csrf
          <input type="hidden" name="_method" value="delete" />
          <input type="submit" class="btn btn-sm btn-success" value="Dar baixa" onclick="return confirm('Tem certeza que deseja dar baixa no chamado?')" />
        </form>
        @endif
      </dl>
    </div>
    @if(isset($resultado->img))
    <div class="col-8">
      <dl>
        <dt>Print:</dt>
        <img src="{{ asset($resultado->img) }}" class="w-100" />
      </dl>
    </div>
    @endif
  </div>
  @if(session('idperfil') === 1 && !isset($resultado->resposta) && !isset($resultado->deleted_at))
  <hr>
  <form role="form" method="POST" action="/admin/chamados/resposta/{{ $resultado->idchamado }}">
    @csrf
    @if(isset($resultado))
        {{ method_field('PUT') }}
    @endif
    <div class="form-row">
      <div class="col">
        <label for="resposta">Resposta</label>
        <textarea name="resposta"
          class="form-control {{ $errors->has('resposta') ? 'is-invalid' : '' }}"
          placeholder="Escreva uma resposta para o chamado"
          rows="3"></textarea>
      </div>
    </div>
    <div class="form-row mt-2">
      <div class="col">
        <button type="submit" class="btn btn-primary">Responder</button>
        <a href="/admin/chamados" class="btn btn-default ml-1">Cancelar</a>
      </div>
    </div>
  </form>
  @endif
</div>