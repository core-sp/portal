<div class="card-body">
  @if(isset($resultado->resposta))
  <div class="row">
    <div class="col">
      <div class="direct-chat-msg">
        <img class="direct-chat-img border border-dark" src="{{ asset('img/ti.png') }}" alt="CTI">
        <div class="direct-chat-text">
          <h5 class="mb-1">Resposta do CTI</h5>
          <p class="mb-0">{!! $resultado->resposta !!}</p>
        </div>
      </div>
    </div>
  </div>
  <hr>
  @endif
  <div class="row">
    <div class="col">
      @if($resultado->tipo === 'Reportar Bug')
      <div class="direct-chat-danger">
      @elseif($resultado->tipo === 'Dúvida')
      <div class="direct-chat-warning">
      @elseif($resultado->tipo === 'Solicitar Funcionalidade')
      <div class="direct-chat-primary">
      @else
      <div class="direct-chat-msg">
      @endif
      <div class="direct-chat-msg right">
        <img class="direct-chat-img border border-dark" src="{{ asset('img/user.png') }}" alt="USER">
        <div class="direct-chat-text">
          <h5 class="mb-0">{{ $resultado->tipo }}</h5>
          <small>(Criado por: {{ $resultado->user->nome }} - {{ formataData($resultado->created_at) }})</small>
          <p class="mt-2 mb-2"><i>Mensagem:</i> {{ $resultado->mensagem }}</p>
          @if(isset($resultado->img))
          <hr>
          <p><i>(Anexo)</i></p>
          <img src="{{ asset($resultado->img) }}" class="mb-2" />
          @endif
        </div>
      </div>
      </div>
      @if(!isset($resultado->deleted_at))
      <hr>
      <form method="POST" action="/admin/chamados/apagar/{{ $resultado->idchamado }}" class="d-inline">
        @csrf
        <input type="hidden" name="_method" value="delete" />
        <input type="submit" class="btn btn-sm btn-success" value="Dar baixa" onclick="return confirm('Tem certeza que deseja dar baixa no chamado?')" />
      </form>
      @else
      <hr>
      <p class="mb-0"><i>* Chamado concluído em {{ formataData($resultado->deleted_at) }}</i></p>
      @endif
    </div>
  </div>
  @if(auth()->user()->idperfil === 1 && !isset($resultado->resposta) && !isset($resultado->deleted_at))
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