@php
  use App\Http\Controllers\Helper;
@endphp

<div class="card-body">
  @if(isset($resultado->resposta))
  <div class="row">
    <div class="col">
      <img class="direct-chat-img" src="{{ asset('img/ti.png') }}" alt="CTI">
      <div class="direct-chat-text">
        <h5 class="mb-1">Resposta do CTI</h5>
        <p class="mb-0">{!! $resultado->resposta !!}</p>
      </div>
    </div>
  </div>
  <hr>
  @endif
  <div class="row">
    <div class="col">
      <img class="direct-chat-img" src="{{ asset('img/user.png') }}" alt="USER">
      <div class="direct-chat-text">
        <h5 class="mb-1">{{ $resultado->tipo }}</h5>
        <p class="mb-0"><i>({{ Helper::formataData($resultado->created_at) }}):</i> {{ $resultado->mensagem }}</p>
      </div>
      @if(isset($resultado->img))
        <p><strong>Print:</strong></p>
        <img src="{{ asset($resultado->img) }}" class="w-100" />
      @endif
      <hr>
      <form method="POST" action="/admin/chamados/apagar/{{ $resultado->idchamado }}" class="d-inline">
        @csrf
        <input type="hidden" name="_method" value="delete" />
        <input type="submit" class="btn btn-sm btn-success" value="Dar baixa" onclick="return confirm('Tem certeza que deseja dar baixa no chamado?')" />
      </form>
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