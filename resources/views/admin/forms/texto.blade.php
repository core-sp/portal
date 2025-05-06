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
        
        <p class="mt-3">
            <i>* Arraste as caixas com o cursor <i class="fas fa-arrows-alt"></i> para definir a ordem da índice</i>
        </p>
        <p>
            <i>* Clique sobre o título com o cursor <i class="far fa-hand-pointer"></i> para editar o item</i>
        </p>
        <p class="mb-4">
            <i>* Clique na caixinha <i class="fas fa-check-square"></i> para selecionar o item a ser excluído</i>
        </p>

        <button type="button" class="btn btn-info btn-sm selecionarTextos"><i class="fas fa-check-square"></i>&nbsp;&nbsp;Selecionar Todos</button>
        <div class="textosSortable">
        @php
        $col = 1;
        $row = 1;
        @endphp

        @foreach($resultado as $texto)
            @if($row == 1)
            <hr />
            <div class="row">
            @endif
                @if($col == 1)
                <div class="col-3">
                @endif            
                    <div class="form-check border border-left-0 border-info rounded-right mb-2 pr-4">
                        <label class="form-check-label pl-4">
                            <input type="hidden" name="id-{{ $texto->id }}" value="{{ $texto->id }}" />
                            <input type="checkbox" class="form-check-input mt-2" name="excluir_ids" value="{{ $texto->id }}">
                            <button type="button" class="btn btn-link btn-sm pl-0 abrir" value="{{ $texto->id }}">
                        @if($texto->tipoTitulo())
                        {!! session()->exists('novo_texto') && ($texto->id == session()->get('novo_texto')) ? '<span class="badge badge-warning">Novo</span>&nbsp;&nbsp;' : '' !!}
                        <span 
                        class="indice-texto"
                        >{{ $texto->tituloFormatado() }}</span>
                        @else
                        <strong><span 
                        class="indice-texto"
                        >{{ $texto->subtituloFormatado() }}</span></strong>
                        @endif
                            </button>
                        </label>
                    </div>
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
        </div>

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

<script type="module" src="{{ asset('/js/interno/modulos/gerar-texto.js?'.hashScriptJs()) }}" data-modulo-id="gerar-texto" data-modulo-acao="editar"></script>
