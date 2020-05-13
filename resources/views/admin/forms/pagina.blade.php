<form role="form" action="{{ !isset($resultado) ? route('paginas.store') : route('paginas.update', Request::route('id')) }}" method="POST">
    @csrf
    @if(isset($resultado))
        @method('PUT')
    @endif
    <input type="hidden" name="idusuario" value="{{ Auth::id() }}" />
    <div class="card-body">
        <div class="form-row">
            <div class="col">
                <label for="titulo">Título da página</label>
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
                <label for="subtitulo">Subtítulo</label>
                <input type="text"
                    class="form-control {{ $errors->has('subtitulo') ? 'is-invalid' : '' }}"
                    name="subtitulo"
                    placeholder="Subtítulo"
                    @if(!empty(old('subtitulo')))
                        value="{{ old('subtitulo') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $resultado->subtitulo }}"
                        @endif
                    @endif
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
                        @if(@!empty(old('img')))
                            value="{{ old('img') }}"
                        @else
                            @if(isset($resultado))
                                value="{{ $resultado->img }}"
                            @endif
                        @endif
                        />
                </div>
            </div>
        </div>
        <div class="form-group mt-2">
            <label for="conteudopage">Conteúdo da página</label>
            <textarea name="conteudo"
                class="form-control my-editor {{ $errors->has('conteudo') ? 'is-invalid' : '' }}"
                id="conteudopage"
                rows="10">@if(!empty(old('conteudo'))){{ old('conteudo') }}@else @if(isset($resultado)){!! $resultado->conteudo !!}@endif @endif</textarea>
            @if($errors->has('conteudo'))
            <div class="invalid-feedback">
                {{ $errors->first('conteudo') }}
            </div>
            @endif
        </div>
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="/admin/paginas" class="btn btn-default">Cancelar</a>
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