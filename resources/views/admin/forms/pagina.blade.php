<form role="form" action="{{ !isset($resultado) ? route('paginas.store') : route('paginas.update', $resultado->idpagina) }}" method="POST">
    @csrf
    @if(isset($resultado))
        @method('PATCH')
    @endif
    <div class="card-body">
        <div class="form-row">
            <div class="col">
                <label for="titulo">Título da página</label>
                <input type="text"
                    class="form-control {{ $errors->has('titulo') ? 'is-invalid' : '' }}"
                    placeholder="Título"
                    name="titulo"
                    value="{{ empty(old('titulo')) && isset($resultado->titulo) ? $resultado->titulo : old('titulo') }}"
                    required
                />
                @if($errors->has('titulo'))
                <div class="invalid-feedback">
                    {{ $errors->first('titulo') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="subtitulo">Subtítulo</label>
                <input type="text"
                    class="form-control {{ $errors->has('subtitulo') ? 'is-invalid' : '' }}"
                    name="subtitulo"
                    placeholder="Subtítulo"
                    value="{{ empty(old('subtitulo')) && isset($resultado->subtitulo) ? $resultado->subtitulo : old('subtitulo') }}"
                />
                @if($errors->has('subtitulo'))
                <div class="invalid-feedback">
                    {{ $errors->first('subtitulo') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col">
                <label for="lfm">Imagem principal</label>
                <div class="input-group">
                    <span class="input-group-prepend">
                        <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-default">
                            <i class="fas fa-picture-o"></i> Alterar/Inserir imagem
                        </a>
                    </span>
                    <input id="thumbnail"
                        class="form-control"
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
        <div class="form-group mt-2">
            <label for="conteudopage">Conteúdo da página</label>
            <textarea name="conteudo"
                class="form-control my-editor {{ $errors->has('conteudo') ? 'is-invalid' : '' }}"
                id="conteudopage"
                rows="25"
                required
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
            <a href="{{ route('paginas.index') }}" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">
                {{ isset($resultado) ? 'Salvar' : 'Publicar' }}
            </button>
        </div>
    </div>
</form>