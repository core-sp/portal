@if(\Session::get('message'))
<div class="row mt-3 ml-3 mr-3">
    <div class="col">
        <div class="alert alert-dismissible {{ \Session::get('class') }}">
        {!! \Session::get('message') !!}
        </div>
    </div>
</div>
@endif

<form role="form" method="POST" autocomplete="false" id="formGerarTexto">
    @csrf
    @method('PUT')
    <div class="card-body">

        <p class="text-muted"><em>* Sumário após última atualização da índice.</em></p>
        <h4><strong>Sumário:</strong></h4>

        @if(isset($can_update) && $can_update)
        <div class="row mt-3">
            <div class="col">
            <button type="button" class="btn btn-danger excluirTextos"><i class="fas fa-check-square"></i>&nbsp;&nbsp;<i class="fas fa-trash"></i></button>
            <button type="button" class="btn btn-success ml-2 criarTexto"><i class="fas fa-plus"></i>&nbsp;&nbsp;Texto</button>
            </div>
        </div>
        @endif
        
        <p class="mb-4 mt-3">
            <i>* Arraste as caixas com o cursor <i class="fas fa-arrows-alt"></i> para definir a ordem da índice</i>
        </p>

        @php
        $i = 1;
        @endphp
        <div class="row textosSortable">

        @foreach($resultado as $texto)
            @if($i == 1)
            <div class="col">
            @endif            
                <div class="form-check border border-left-0 border-info rounded-right mb-2">
                    <label class="form-check-label">
                        <input type="hidden" name="id-{{ $texto->id }}" value="{{ $texto->id }}" />
                        <input type="checkbox" class="form-check-input" name="excluir_ids" value="{{ $texto->id }}">
                        <button type="button" class="btn btn-link btn-sm abrir" value="{{ $texto->id }}">
                    @if($texto->tipoTitulo())
                    <span 
                    class="indice-texto {{ session()->exists('novo_texto') && ($texto->id == session()->get('novo_texto')) ? 'text-danger' : '' }}"
                    >{{ $texto->tituloFormatado() }}</span>
                    @else
                    <strong><span 
                    class="indice-texto {{ session()->exists('novo_texto') && ($texto->id == session()->get('novo_texto')) ? 'text-danger' : '' }}"
                    >{{ $texto->subtituloFormatado() }}</span></strong>
                    @endif
                        </button>
                    </label>
                </div>
            @if(($i == 50) || $loop->last)
            </div>
            @endif
            @php
            $i = ($i == 50) ? 1 : $i + 1;
            @endphp
        @endforeach
        </div>

        @if(isset($can_update) && $can_update)
        <div class="row mt-3">
            <div class="col">
            <button type="button" class="btn btn-danger excluirTextos"><i class="fas fa-check-square"></i>&nbsp;&nbsp;<i class="fas fa-trash"></i></button>
            <button type="button" class="btn btn-success ml-2 criarTexto"><i class="fas fa-plus"></i>&nbsp;&nbsp;Texto</button>
            </div>
        </div>
        @endif

        <hr />

        <input type="hidden" id="tipo_doc" value="{{ $tipo_doc }}" />

        <div class="row" id="lista" style="display: none;">
            <div class="col">
                <h5 class="border rounded bg-info p-2">
                    <strong>&nbsp;&nbsp;
                        <em><span id="span-texto_tipo"></span></em>:&nbsp;
                        <span id="span-tipo" class=""></span>&nbsp;
                        <small>(nível <span id="span-nivel"></span>)</small>
                    </strong>
                </h5>
                <div class="card card-default bg-light">
                    <div class="card-body">

                        <div class="form-row mb-2">
                            <div class="col">
                                <label for="tipo">Tipo do texto</label>
                                <select class="form-control form-control-sm textoTipo" id="tipo">
                                    <option value="Título">Título</option>
                                    <option value="Subtítulo">Subtítulo</option>
                                </select>
                            </div>

                            <div class="col">
                                <label for="com_numeracao-">Possui numeração na índice?</label>
                                <select class="form-control form-control-sm comNumero" id="com_numeracao">
                                    <option value="1">Sim</option>
                                    <option value="0" style="">Não</option>
                                </select>
                            </div>

                            <div class="col">
                                <label for="nivel-">Nível <small>(0 para título)</small></label>
                                <select class="form-control form-control-sm comNivel" id="nivel">
                                    <option value="0" style="">0</option>
                                    <option value="1" style="">1 (1.1)</option>
                                    <option value="2" style="">2 (1.1.1)</option>
                                    <option value="3" style="">3 (1.1.1.1)</option>
                                </select>
                            </div>

                        </div>

                        <div class="form-row mb-2">
                            <div class="col">
                                <label for="texto_tipo-">Título do tipo do texto</label>
                                <input id="texto_tipo"
                                    class="form-control text-uppercase"
                                    type="text"
                                    value=""
                                />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col">
                                <label for="conteudo-">Conteúdo do texto</label>
                                <textarea 
                                    class="form-control my-editor"
                                    id="conteudo"
                                    rows="15"
                                ></textarea>
                            </div>
                        </div>

                    </div>

                    @if(isset($can_update) && $can_update)
                    <div class="card-footer">
                        <div class="float-right">
                            <button type="button" value="" class="btn btn-primary updateCampos mr-2"><i class="fas fa-save"></i></button>
                            <button type="button" value="" class="btn btn-danger deleteTexto"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
    
    <div class="card-footer">
        <div class="float-right">
            <a href="/admin" class="btn btn-default">Cancelar</a>
            <a href="{{ route($tipo_doc) }}" target="_blank" class="btn btn-secondary ml-1">Ver</a>

            @if(isset($resultado))
                @if(isset($can_update) && $can_update)
                <button type="submit" class="btn btn-primary ml-1" id="updateIndice">Atualizar índice</button>
                <button type="button" id="publicarTexto" value="{{ $resultado->get(0)->publicar ? '0' : '1' }}" class="btn btn-{{ $resultado->get(0)->publicar ? 'danger' : 'success' }} ml-1">{{ $resultado->get(0)->publicar ? 'Reverter publicação' : 'Publicar' }}</button>
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

  <!-- The Modal -->
<div class="modal" id="loadingIndice">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
            <!-- Modal body -->
            <div class="modal-body"><div class="spinner-border text-primary"></div>&nbsp;&nbsp;Atualizando a índice...</div>
        </div>
    </div>
</div>