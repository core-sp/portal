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

        <p><strong>Orientação do sumário:</strong>
            <a type="button" class="btn btn-link pt-0 {{ $orientacao_sumario == 'horizontal' ? 'disabled' : '' }}" href="{{ route('textos.orientacao', ['tipo_doc' => $tipo_doc, 'orientacao' => 'horizontal']) }}">Horizontal</a>
            <strong>|</strong>
            <a type="button" class="btn btn-link pt-0 {{ $orientacao_sumario != 'horizontal' ? 'disabled' : '' }}" href="{{ route('textos.orientacao', ['tipo_doc' => $tipo_doc, 'orientacao' => 'vertical']) }}">Vertical</a>
        </p>

        <p class="text-muted"><em>* Sumário após última atualização da índice.</em></p>
        <h4><strong>Sumário:</strong></h4>

        <em>Opção de criar vários textos de uma vez <i>(até <span id="lim-max-criar">{{ $limite_criar_textos }}</span>)</i></em>
        <div class="input-group col-2 mb-4 pl-0">
            <div class="input-group-prepend">
                <span class="input-group-text">Criar</span>
            </div>
            <input type="text" name="n_vezes" class="form-control {{ $errors->has('n_vezes') ? 'is-invalid' : '' }}" placeholder="Ex: 2">
            <div class="input-group-append">
                <button class="btn btn-success criarTexto" type="button">textos</button>
            </div>
            @if($errors->has('n_vezes'))
            <div class="invalid-feedback">
                {{ $errors->first('n_vezes') }}
            </div>
            @endif
        </div>

        @if(isset($can_update) && $can_update)
        <div class="row mt-3">
            <div class="col">
                <button type="button" class="btn btn-danger excluirTextos"><i class="fas fa-check-square"></i>&nbsp;&nbsp;<i class="fas fa-trash"></i></button>
                <button type="button" class="btn btn-success ml-2 criarTexto"><i class="fas fa-plus"></i>&nbsp;&nbsp;Texto</button>
            </div>
        </div>
        @endif
        
        <p class="mt-3">
            <i>* Clique no botão 
            <button type="button" class="btn btn-success btn-sm m-0 pt-0 pb-0"><i class="{{ $orientacao_sumario == 'horizontal' ? 'fas fa-exchange-alt' : 'fas fa-exchange-alt fa-rotate-90' }}"></i></button> 
            para inciar a ação de mover o item, em seguida clique no botão 
            <button type="button" class="btn btn-secondary btn-sm m-0 pt-0 pb-0"><i class="{{ $orientacao_sumario == 'horizontal' ? 'fas fa-long-arrow-alt-right' : 'fas fa-long-arrow-alt-down' }}"></i></button>
            para inserir o item na posição seguinte e reordenar a índice</i> 
        </p>
        <p>
            <i>* A nova ordem da índice é salva somente após clicar no botão <button type="button" class="btn btn-sm btn-primary">Atualizar índice</button></i>
        </p>
        <p>
            <i>* Clique sobre o título com o cursor <i class="far fa-hand-pointer"></i> para editar o item</i>
        </p>
        <p class="mb-4">
            <i>* Clique na caixinha <i class="fas fa-check-square"></i> para selecionar o item a ser excluído</i>
        </p>

        <button type="button" class="btn btn-info btn-sm selecionarTextos"><i class="fas fa-check-square"></i>&nbsp;&nbsp;Selecionar Todos</button>
        <div class="sumario-{{ $orientacao_sumario }}" id="sumario">
        @php
        $col = 1;
        $row = 1;
        @endphp

        <!-- Sumário horizontal -->
        @if($orientacao_sumario == 'horizontal')

        @foreach($resultado as $texto)
            
            @if($texto->tipoTitulo())
            <hr style="border-top: 2px dotted gray;" />
            <div class="row ml-0">
                <div class="d-flex flex-wrap ">
            @endif 

            @component('components.item-gerar-texto', ['texto' => $texto, 'orientacao_sumario' => $orientacao_sumario])
            @endcomponent

            @if($loop->last || $resultado->get($loop->iteration)->tipoTitulo())
                </div>
            </div>
            @endif
            
        @endforeach

        @else

        <!-- Sumário vertical -->
        @foreach($resultado as $texto)
            @if($row == 1)
            <hr />
            <div class="row">
            @endif
                @if($col == 1)
                <div class="col-3">
                @endif            
                
                @component('components.item-gerar-texto', ['texto' => $texto, 'orientacao_sumario' => $orientacao_sumario])
                @endcomponent
                
                @if(($col == 25) || $loop->last)
                </div>
                @endif
            @if(($row == 100) || $loop->last)
            </div>
            @endif

            @php
            $col = ($col == 25) ? 1 : $col + 1;
            $row = ($row == 100) ? 1 : $row + 1;
            @endphp
        @endforeach

        @endif
        </div>

        <hr />
        <button type="button" class="btn btn-info btn-sm selecionarTextos mt-2 mb-2"><i class="fas fa-check-square"></i>&nbsp;&nbsp;Selecionar Todos</button>

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
                        <p class="text-danger"><b>Lembre de atualizar a índice após alterações na ordem, tipo do texto ou nível</b></p>
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

            @if(isset($resultado) && $resultado->isNotEmpty())
                @if(isset($can_update) && $can_update)
                <button type="submit" class="btn btn-primary ml-1" id="updateIndice">Atualizar índice</button>
                <button type="button" id="publicarTexto" value="{{ $resultado->get(0)->publicar ? '0' : '1' }}" class="btn btn-{{ $resultado->get(0)->publicar ? 'danger' : 'success' }} ml-1">{{ $resultado->get(0)->publicar ? 'Reverter publicação' : 'Publicar' }}</button>
                @endif
            @endif

        </div>
    </div>
</form>

<!-- The Modal -->
<div class="modal" id="avisoTextos">
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
            <div class="modal-body"></div>
        </div>
    </div>
</div>