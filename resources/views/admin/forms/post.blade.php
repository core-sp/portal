<form role="form" method="POST" action="{{ isset($post) ? '/admin/posts/' . $post->id : '/admin/posts' }}">
    @csrf
    @if(isset($post))
        @method('PATCH')
    @endif
    <input type="hidden" name="idusuario" value="{{ auth()->id() }}" />
    <div class="card-body">
        <div class="form-row">
            <div class="col">
                <label for="titulo">Título</label>
                <input type="text"
                    class="form-control {{ $errors->has('titulo') ? 'is-invalid' : '' }}"
                    placeholder="Título"
                    name="titulo"
                    value="{{ isset($post) ? $post->titulo : old('titulo') }}"
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
                    value="{{ isset($post) ? $post->subtitulo : old('subtitulo') }}"
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
                    value="{{ isset($post) ? $post->img : old('img') }}"
                />
                @if($errors->has('img'))
                    <div class="invalid-feedback">
                        {{ $errors->first('img') }}
                    </div>
                @endif
            </div>
        </div>
        <div class="form-group mt-2">
            <label for="conteudopost">Conteúdo da página</label>
            <textarea name="conteudo"
                class="form-control my-editor {{ $errors->has('conteudo') ? 'is-invalid' : '' }}"
                id="conteudopost"
                rows="10"
            >{!! isset($post) ? $post->conteudo : old('conteudo') !!}</textarea>
            @if($errors->has('conteudo'))
                <div class="invalid-feedback">
                    {{ $errors->first('conteudo') }}
                </div>
            @endif
        </div>
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="/admin/posts" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">
            @if(isset($post))
                Salvar
            @else
                Publicar
            @endif
            </button>
        </div>
    </div>
</form>