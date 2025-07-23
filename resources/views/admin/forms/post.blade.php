<form role="form" method="POST" action="{{ isset($post->id) ? route('posts.update', $post->id) : route('posts.store') }}">
    @csrf
    @if(isset($post->id))
        @method('PATCH')
    @endif
    <div class="card-body">
        <div class="form-row">
            <div class="col">
                <label for="titulo">Título</label>
                <input type="text"
                    class="form-control {{ $errors->has('titulo') ? 'is-invalid' : '' }}"
                    placeholder="Título"
                    name="titulo"
                    value="{{ empty(old('titulo')) && isset($post->titulo) ? $post->titulo : old('titulo') }}"
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
                    placeholder="Subtítulo"
                    name="subtitulo"
                    value="{{ empty(old('subtitulo')) && isset($post->subtitulo) ? $post->subtitulo : old('subtitulo') }}"
                />
                @if($errors->has('subtitulo'))
                <div class="invalid-feedback">
                    {{ $errors->first('subtitulo') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-group mt-2">
            <label for="lfm">Imagem principal</label>
            <div class="input-group">
                <span class="input-group-prepend">
                    <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-default">
                        <i class="fas fa-picture-o"></i> Alterar/Inserir imagem
                    </a>
                </span>
                <input id="thumbnail"
                    class="form-control {{ $errors->has('img') ? 'is-invalid' : '' }}"
                    type="text"
                    name="img"
                    value="{{ empty(old('img')) && isset($post->img) ? $post->img : old('img') }}"
                />
                <div class="input-group-append">
                    <span class="input-group-text" id="preview-lfm" data-toggle="popover">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                @if($errors->has('img'))
                <div class="invalid-feedback">
                    {{ $errors->first('img') }}
                </div>
                @endif
            </div>
            <div id="holder" src="{{ isset($post->img) ? asset($post->img) : '' }}"></div>
        </div>
        <div class="form-group mt-2">
            <label for="conteudopost">Conteúdo da página</label>
            <textarea name="conteudo"
                class="form-control my-editor {{ $errors->has('conteudo') ? 'is-invalid' : '' }}"
                id="conteudopost"
                rows="25"
            >
                {!! empty(old('conteudo')) && isset($post->conteudo) ? $post->conteudo : old('conteudo') !!}
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
            <a href="{{ route('posts.index') }}" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">
                {{ isset($post->id) ? 'Salvar' : 'Publicar' }}
            </button>
        </div>
    </div>
</form>