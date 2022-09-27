<form role="form" action="{{ !isset($resultado) ? route('noticias.store') : route('noticias.update', $resultado->idnoticia) }}" method="POST">
    @csrf
    @if(isset($resultado))
        @method('PATCH')
    @endif
    <div class="card-body">
        <div class="form-row">
            <div class="col">
                <label for="titulo">Título da notícia</label>
                <input type="text"
                    class="form-control {{ $errors->has('titulo') ? 'is-invalid' : '' }}"
                    placeholder="Título"
                    name="titulo"
                    value="{{ empty(old('titulo')) && isset($resultado->titulo) ? $resultado->titulo : old('titulo') }}"
                />
                @if($errors->has('titulo'))
                <div class="invalid-feedback">
                    {{ $errors->first('titulo') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="lfm">Imagem principal <small>(opcional)</small></label>
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
                    value="{{ empty(old('img')) && isset($resultado->img) ? $resultado->img : old('img') }}"
                />
                @if($errors->has('img'))
                <div class="invalid-feedback">
                    {{ $errors->first('img') }}
                </div>
                @endif
                </div>
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col">
                <label for="idregional">Regional</label>
                <select name="idregional" class="form-control {{ $errors->has('idregional') ? 'is-invalid' : '' }}">
                    <option value="">Todas</option>
                    @foreach($regionais as $regional)
                        @if(old('idregional'))
                            <option value="{{ $regional->idregional }}" {{ old('idregional') == $regional->idregional ? 'selected' : '' }}>{{ $regional->regional }}</option>
                        @else
                            <option value="{{ $regional->idregional }}" {{ isset($resultado->idregional) && ($resultado->idregional == $regional->idregional) ? 'selected' : '' }}>{{ $regional->regional }}</option>
                        @endif
                    @endforeach
                </select>
                @if($errors->has('idregional'))
                <div class="invalid-feedback">
                    {{ $errors->first('idregional') }}
                </div>
                @endif
                <small class="form-text text-muted">
                    <em>* Associar notícia à alguma regional</em>
                </small>
            </div>
            <div class="col">
                <label for="idcurso">Curso</label>
                <input type="number"
                    name="idcurso"
                    class="form-control {{ $errors->has('idcurso') ? 'is-invalid' : '' }}"
                    placeholder="Código da Turma"
                    value="{{ empty(old('idcurso')) && isset($resultado->idcurso) ? $resultado->idcurso : old('idcurso') }}"
                />
                @if($errors->has('idcurso'))
                <div class="invalid-feedback">
                    {{ $errors->first('idcurso') }}
                </div>
                @endif
                <small class="form-text text-muted">
                    <em>* Associar notícia à algum curso</em>
                </small>
            </div>
            <div class="col">
                <label for="categoria">Categoria</label>
                <select name="categoria" class="form-control {{ $errors->has('categoria') ? 'is-invalid' : '' }}">
                    <option value="">Nenhuma</option>
                    @foreach($categorias as $categoria)
                        @if(old('categoria'))
                            <option value="{{ $categoria }}" {{ old('categoria') == $categoria ? 'selected' : '' }}>{{ $categoria }}</option>
                        @else
                            <option value="{{ $categoria }}" {{ isset($resultado->categoria) && ($resultado->categoria == $categoria) ? 'selected' : '' }}>{{ $categoria }}</option>
                        @endif
                    @endforeach
                </select>
                @if($errors->has('categoria'))
                <div class="invalid-feedback">
                    {{ $errors->first('categoria') }}
                </div>
                @endif
                <small class="form-text text-muted">
                    <em>* Associar notícia à alguma categoria específica</em>
                </small>
            </div>
        </div>
        <div class="form-group mt-2">
            <label for="conteudo">Conteúdo da página</label>
            <textarea name="conteudo"
                class="form-control {{ $errors->has('conteudo') ? 'is-invalid' : '' }} my-editor"
                id="conteudo"
                rows="25"
            >
                {!! empty(old('conteudo')) && isset($resultado->conteudo) ? $resultado->conteudo : old('conteudo') !!}
            </textarea>
            @if($errors->has('conteudo'))
            <div class="invalid-feedback">
                {{ $errors->first('conteudo') }}
            </div>
            @endif
        </div>
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="{{ route('noticias.index') }}" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">
                {{ isset($resultado) ? 'Salvar' : 'Publicar' }}
            </button>
        </div>
    </div>
</form>