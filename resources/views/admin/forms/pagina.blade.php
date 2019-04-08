<form role="form" method="POST">
    @csrf
    @if(isset($resultado))
        {{ method_field('PUT') }}
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
            @if(isset($resultado))
            value="{{ $resultado->titulo }}"
            @endif />
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
            @if(isset($resultado))
            value="{{ $resultado->subtitulo }}"
            @endif
            />
            @if($errors->has('subtitulo'))
            <div class="invalid-feedback">
            {{ $errors->first('subtitulo') }}
            </div>
            @endif
        </div>
        </div>
        <div class="form-row">
        <div class="col">
            <label for="lfm">Imagem principal</label>
            <div class="input-group">
            <span class="input-group-btn">
                <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-default">
                <i class="fas fa-picture-o"></i> Alterar/Inserir imagem
                </a>
            </span>
            <input id="thumbnail"
                class="form-control"
                type="text"
                name="img"
                @if(isset($resultado))
                value="{{ $resultado->img }}"
                @endif
                />
            </div>
        </div>
        <div class="col">
            <label for="categoria">Categoria</label>
            <select name="categoria" class="form-control">
            <option value="">Sem Categoria</option>
            @foreach($categorias as $categoria)
                @if(isset($resultado))
                    @if ($categoria->idpaginacategoria == $resultado->idcategoria)
                    <option value="{{ $categoria->idpaginacategoria }}" selected>{{ $categoria->nome }}</option>
                    @else
                    <option value="{{ $categoria->idpaginacategoria }}" >{{ $categoria->nome }}</option>
                    @endif
                @else
                <option value="{{ $categoria->idpaginacategoria }}" >{{ $categoria->nome }}</option>
                @endif
            @endforeach
            </select>
            <a href="/admin/paginas/categorias/criar" class="float-right"><small>Criar nova categoria</small></a>
        </div>
        </div>
        <div class="form-group">
        <label for="conteudopage">Conteúdo da página</label>
        <textarea name="conteudo"
            class="form-control my-editor {{ $errors->has('conteudo') ? 'is-invalid' : '' }}"
            id="conteudopage"
            rows="10">
        @if(isset($resultado))
            {!! $resultado->conteudo !!}
        @endif
        </textarea>
        @if($errors->has('conteudo'))
        <div class="invalid-feedback">
            {{ $errors->first('conteudo') }}
        </div>
        @endif
        </div>
    </div>
    <div class="card-footer float-right">
        <a href="/admin/paginas" class="btn btn-default">Cancelar</a>
        <button type="submit" class="btn btn-primary ml-1">
        @if(isset($resultado))
            Editar
        @else
            Publicar
        @endif
        </button>
    </div>
    </form>