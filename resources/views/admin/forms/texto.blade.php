<form role="form" method="POST" autocomplete="false">
    @csrf
    @method('PUT')
    <div class="card-body">
        <h4><strong>Sumário:</strong></h4>
        @foreach($resultado as $texto)
            @if($texto->tipo == 'Título')
            <h5>{{ isset($texto->indice) ? $texto->indice . '. ' : '' }}{{ $texto->texto_tipo }}</h5>
            @else
            <p>
                &nbsp;&nbsp;&nbsp;&nbsp;<strong>{{ $texto->indice }} - {{ $texto->texto_tipo }}</strong>
            </p>
            @endif
        @endforeach
        <hr />
        <p class="mb-4">
            <i>* Arraste as caixas para definir a ordem da índice<br></i>
        </p>
        <input type="hidden" id="tipo_doc" value="{{ $texto->tipo_doc }}" />
        <div id="accordion" class="mb-0 pl-0">
            <button type="button" id="criarTexto" class="btn btn-success mb-3"><i class="fas fa-plus"></i> Texto</button>
            @foreach($resultado as $texto)
            <div class="row homeimagens" id="lista-{{ $texto->id }}">
                <div class="col">
                    <h5 class="border rounded border-info bg-info p-2 {{ $loop->last ? '' : 'mb-3' }}">&nbsp;&nbsp;<strong>{{ $texto->tipo }} <small>(nível {{ $texto->nivel }})</small>:</strong> <em>{{ $texto->texto_tipo }}</em></h5>
                    <div class="card card-default bg-light">
                        <div class="card-body">
                            <div class="form-row mb-2">
                                <div class="col">
                                    <label for="tipo-{{ $texto->id }}">Tipo do texto</label>
                                    <select class="form-control form-control-sm" id="tipo-{{ $texto->id }}">
                                        <option value="Título" {{ $texto->tipo === 'Título' ? 'selected' : '' }}>Título</option>
                                        <option value="Subtítulo" {{ $texto->tipo === 'Subtítulo' ? 'selected' : '' }}>Subtítulo</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label for="com_numeracao-{{ $texto->id }}">Possui numeração na índice?</label>
                                    <select name="com_numeracao-{{ $texto->id }}" class="form-control form-control-sm" id="com_numeracao-{{ $texto->id }}">
                                        <option value="1" {{ $texto->com_numeracao == 1 ? 'selected' : '' }}>Sim</option>
                                        <option value="0" {{ $texto->com_numeracao == 0 ? 'selected' : '' }}>Não</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label for="nivel-{{ $texto->id }}">Nível <small>(0 para título)</small></label>
                                    <select name="nivel-{{ $texto->id }}" class="form-control form-control-sm" id="nivel-{{ $texto->id }}">
                                        <option value="0" {{ $texto->nivel == 0 ? 'selected' : '' }}>0</option>
                                        <option value="1" {{ $texto->nivel == 1 ? 'selected' : '' }}>1 (1.1)</option>
                                        <option value="2" {{ $texto->nivel == 2 ? 'selected' : '' }}>2 (1.1.1)</option>
                                        <option value="3" {{ $texto->nivel == 3 ? 'selected' : '' }}>3 (1.1.1.1)</option>
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
                        <div class="card-footer">
                            <div class="float-right">
                                <button type="button" value="{{ $texto->id }}" class="btn btn-primary updateCampos">Atualizar campos</button>
                                <button type="button" value="{{ $texto->id }}" class="btn btn-danger deleteTexto"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="/admin" class="btn btn-default">Cancelar</a>
            @if($resultado->get(0)->publicar)
            <a href="{{ route($resultado->get(0)->tipo_doc) }}" target="_blank" class="btn btn-secondary ml-1">Ver</a>
            @endif
            <button type="submit" class="btn btn-primary ml-1">Atualizar índice</button>
            <button type="button" id="publicarTexto" value="{{ $resultado->get(0)->publicar ? '0' : '1' }}" class="btn btn-{{ $resultado->get(0)->publicar ? 'danger' : 'success' }} ml-1">{{ $texto->publicar ? 'Reverter publicação' : 'Publicar' }}</button>
        </div>
    </div>
</form>