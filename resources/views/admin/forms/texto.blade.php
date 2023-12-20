@if(\Session::get('message'))
<div class="row mt-3 ml-3 mr-3">
    <div class="col">
        <div class="alert alert-dismissible {{ \Session::get('class') }}">
        {!! \Session::get('message') !!}
        </div>
    </div>
</div>
@endif

<form role="form" method="POST" autocomplete="false">
    @csrf
    @method('PUT')
    <div class="card-body">

        <p class="text-muted"><em>* Sumário após última atualização da índice.</em></p>
        <h4><strong>Sumário:</strong></h4>
        @foreach($resultado as $texto)
            @switch($texto->nivel)
                @case(1)
                  <p>&nbsp;&nbsp;&nbsp;<strong>{{ $texto->subtituloFormatado() }}</strong></p>
                  @break
                @case(2)
                  <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>{{ $texto->subtituloFormatado() }}</strong></p>
                  @break
                @case(3)
                  <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>{{ $texto->subtituloFormatado() }}</strong></p>
                  @break
                @default
                  <p>{{ $texto->tituloFormatado() }}</p>
              @endswitch
        @endforeach

        <hr />
        <p class="mb-4">
            <i>* Arraste as caixas para definir a ordem da índice</i>
        </p>

        <input type="hidden" id="tipo_doc" value="{{ $tipo_doc }}" />
        <div id="accordion" class="mb-0 pl-0">
            @if(isset($can_update) && $can_update)
            <button type="button" class="btn btn-danger mb-3 excluirTextos"><i class="fas fa-check-square"></i>&nbsp;&nbsp;<i class="fas fa-trash"></i></button>
            <button type="button" class="btn btn-success mb-3 ml-2 criarTexto"><i class="fas fa-plus"></i>&nbsp;&nbsp;Texto</button>
            @endif

            @foreach($resultado as $texto)
            <div class="row homeimagens" id="lista-{{ $texto->id }}">
                <input type="hidden" name="id-{{ $texto->id }}" value="{{ $texto->id }}" />

                <div class="form-check">
                    <label class="form-check-label">
                        <input type="checkbox" class="form-check-input" name="excluir_ids" value="{{ $texto->id }}">
                    </label>
                </div>

                <div class="col">
                    <h5 class="border rounded bg-info p-2 {{ $loop->last ? '' : 'mb-3' }}">
                        <strong>&nbsp;&nbsp;
                            <em><span id="span-texto_tipo-{{ $texto->id }}">{{ $texto->texto_tipo }}</span></em>:&nbsp;
                            <span id="span-tipo-{{ $texto->id }}" class="text-{{ $texto->tipoTitulo() ? 'warning' : 'dark' }}">{{ $texto->tipo }}</span>&nbsp;
                            <small>(nível <span id="span-nivel-{{ $texto->id }}">{{ $texto->nivel }}</span>)</small>
                        </strong>
                    </h5>
                    <div class="card card-default bg-light">
                        <div class="card-body">

                            <div class="form-row mb-2">
                                <div class="col">
                                    <label for="tipo-{{ $texto->id }}">Tipo do texto</label>
                                    <select class="form-control form-control-sm textoTipo" id="tipo-{{ $texto->id }}">
                                        <option value="Título" {{ $texto->tipo === 'Título' ? 'selected' : '' }}>Título</option>
                                        <option value="Subtítulo" {{ $texto->tipo === 'Subtítulo' ? 'selected' : '' }}>Subtítulo</option>
                                    </select>
                                </div>

                                <div class="col">
                                    <label for="com_numeracao-{{ $texto->id }}">Possui numeração na índice?</label>
                                    <select class="form-control form-control-sm comNumero" id="com_numeracao-{{ $texto->id }}">
                                        <option value="1" {{ $texto->com_numeracao == 1 ? 'selected' : '' }}>Sim</option>
                                        <option value="0" {{ $texto->com_numeracao == 0 ? 'selected' : '' }} style="{{ $texto->tipo == 'Subtítulo' ? 'display: none;' : '' }}">Não</option>
                                    </select>
                                </div>

                                <div class="col">
                                    <label for="nivel-{{ $texto->id }}">Nível <small>(0 para título)</small></label>
                                    <select class="form-control form-control-sm comNivel" id="nivel-{{ $texto->id }}">
                                        <option value="0" {{ $texto->nivel == 0 ? 'selected' : '' }} style="{{ $texto->tipo == 'Subtítulo' ? 'display: none;' : '' }}">0</option>
                                        <option value="1" {{ $texto->nivel == 1 ? 'selected' : '' }} style="{{ $texto->tipo == 'Título' ? 'display: none;' : '' }}">1 (1.1)</option>
                                        <option value="2" {{ $texto->nivel == 2 ? 'selected' : '' }} style="{{ $texto->tipo == 'Título' ? 'display: none;' : '' }}">2 (1.1.1)</option>
                                        <option value="3" {{ $texto->nivel == 3 ? 'selected' : '' }} style="{{ $texto->tipo == 'Título' ? 'display: none;' : '' }}">3 (1.1.1.1)</option>
                                    </select>
                                </div>

                            </div>

                            <div class="form-row mb-2">
                                <div class="col">
                                    <label for="texto_tipo-{{ $texto->id }}">Título do tipo do texto</label>
                                    <input id="texto_tipo-{{ $texto->id }}"
                                        class="form-control text-uppercase"
                                        type="text"
                                        value="{{ $texto->texto_tipo }}"
                                    />
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col">
                                    <label for="conteudo-{{ $texto->id }}">Conteúdo do texto</label>
                                    <textarea 
                                        class="form-control my-editor"
                                        id="conteudo-{{ $texto->id }}"
                                        rows="15"
                                    >
                                        {!! empty(old('conteudo')) && isset($texto->conteudo) ? $texto->conteudo : old('conteudo') !!}
                                    </textarea>
                                </div>
                            </div>

                        </div>

                        @if(isset($can_update) && $can_update)
                        <div class="card-footer">
                            <div class="float-right">
                                <button type="button" value="{{ $texto->id }}" class="btn btn-primary updateCampos mr-2"><i class="fas fa-save"></i></button>
                                <button type="button" value="{{ $texto->id }}" class="btn btn-danger deleteTexto"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>

            </div>
            @endforeach

            @if(isset($can_update) && $can_update)
            <button type="button" class="btn btn-danger mt-3 excluirTextos"><i class="fas fa-check-square"></i>&nbsp;&nbsp;<i class="fas fa-trash"></i></button>
            <button type="button" class="btn btn-success mt-3 ml-2 criarTexto"><i class="fas fa-plus"></i>&nbsp;&nbsp;Texto</button>
            @endif
        </div>
    </div>
    
    <div class="card-footer">
        <div class="float-right">
            <a href="/admin" class="btn btn-default">Cancelar</a>
            <a href="{{ route($tipo_doc) }}" target="_blank" class="btn btn-secondary ml-1">Ver</a>

            @if(isset($resultado) && isset($texto))
                @if(isset($can_update) && $can_update)
                <button type="submit" class="btn btn-primary ml-1">Atualizar índice</button>
                <button type="button" id="publicarTexto" value="{{ $resultado->get(0)->publicar ? '0' : '1' }}" class="btn btn-{{ $resultado->get(0)->publicar ? 'danger' : 'success' }} ml-1">{{ $texto->publicar ? 'Reverter publicação' : 'Publicar' }}</button>
                @endif
            @endif

        </div>
    </div>
</form>

<!-- The Modal -->
<div class="modal fade" id="avisoTextos">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title"></h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <!-- Modal body -->
        <div class="modal-body"></div>
         <!-- Modal footer -->
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-danger" id="excluirTexto" value="">Sim</button>
        </div>
      </div>
    </div>
  </div>