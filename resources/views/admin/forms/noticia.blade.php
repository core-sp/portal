<form role="form" action="{{ !isset($resultado) ? route('noticias.store') : route('noticias.update', Request::route('id')) }}" method="POST">
    @csrf
    @if(isset($resultado))
        @method('PATCH')
    @endif
    <input type="hidden" name="idusuario" value="{{ Auth::id() }}" />
    <div class="card-body">
        <div class="form-row">
        <div class="col">
            <label for="titulo">Título da notícia</label>
            <input type="text"
                class="form-control {{ $errors->has('titulo') ? 'is-invalid' : '' }}"
                placeholder="Título"
                name="titulo"
                @if(!empty(old('titulo')))
                    value="{{ old('titulo') }}"
                @else
                    @if(isset($resultado))
                        value="{{ $resultado->titulo }}"
                    @endif
                @endif
                />
            @if($errors->has('titulo'))
            <div class="invalid-feedback">
            {{ $errors->first('titulo') }}
            </div>
            @endif
        </div>
        <div class="col">
            <label for="lfm">Imagem principal</label>
            <div class="input-group">
            <span class="input-group-prepend">
                <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-default">
                    <i class="fas fa-picture-o"></i> Inserir/Alterar imagem
                </a>
            </span>
            <input id="thumbnail"
                class="form-control {{ $errors->has('img') ? 'is-invalid' : '' }}"
                type="text"
                name="img"
                @if(!empty(old('img')))
                    value="{{ old('img') }}"
                @else
                    @if(isset($resultado))
                        value="{{ $resultado->img }}"
                    @endif
                @endif
                />
            @if($errors->has('img'))
                <div class="invalid-feedback">
                    {{ $errors->first('img') }}
                </div>
            @endif
            </div>
        </div>
        </div>
        <div class="form-group mt-2">
            <label for="conteudo">Conteúdo da página</label>
            <textarea name="conteudo"
                class="form-control {{ $errors->has('conteudo') ? 'is-invalid' : '' }} my-editor"
                id="conteudo"
                rows="10">@if(!empty(old('conteudo'))){{ old('conteudo') }}@else @if(isset($resultado)){!! $resultado->conteudo !!}@endif @endif</textarea>
            @if($errors->has('conteudo'))
            <div class="invalid-feedback">
                {{ $errors->first('conteudo') }}
            </div>
            @endif
        </div>
        <div class="form-row">
        <div class="col">
            <label for="idregional">Regional</label>
            <select name="idregional" class="form-control">
            <option value="">Todas</option>
            @foreach($regionais as $regional)
                @if(!empty(old('idregional')))
                    @if(old('idregional') == $regional->idregional)
                        <option value="{{ $regional->idregional }}" selected>{{ $regional->regional }}</option>
                    @else
                        <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                    @endif
                @else
                    @if(isset($resultado))
                        @if($resultado->idregional == $regional->idregional)
                            <option value="{{ $regional->idregional }}" selected>{{ $regional->regional }}</option>
                        @else
                            <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                        @endif
                    @else
                        <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                    @endif
                @endif
            @endforeach
            </select>
            <small class="form-text text-muted">
            <em>* Associar notícia à alguma regional</em>
            </small>
        </div>
        <div class="col">
            <label for="idcurso">Curso</label>
            <input type="number"
                name="idcurso"
                class="form-control"
                placeholder="Código da Turma"
                @if(!empty(old('idcurso')))
                    value="{{ old('idcurso') }}"
                @else
                    @if(isset($resultado))
                        value="{{ $resultado->idcurso }}"
                    @endif
                @endif
                />
            <small class="form-text text-muted">
            <em>* Associar notícia à algum curso</em>
            </small>
        </div>
        <div class="col">
            <label for="categoria">Categoria</label>
            <select name="categoria" class="form-control">
            <option value="">Nenhuma</option>
            @foreach(noticiaCategorias() as $cat)
                @if(!empty(old('categoria')))
                    @if(old('categoria') === $cat)
                        <option value="{{ $cat }}" selected>{{ $cat }}</option>
                    @else
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endif
                @else
                    @if(isset($resultado))
                        @if($resultado->categoria == $cat)
                            <option value="{{ $cat }}" selected>{{ $cat }}</option>
                        @else
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endif
                    @else
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endif
                @endif
            @endforeach
            </select>
            <small class="form-text text-muted">
            <em>* Associar notícia à algum categoria específica</em>
            </small>
        </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="/admin/noticias" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">
            @if(isset($resultado))
                Salvar
            @else
                Publicar
            @endif
            </button>
        </div>
    </div>
</form>