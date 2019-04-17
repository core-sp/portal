<form role="form" method="POST">
    @csrf
    @if(isset($resultado))
        {{ method_field('PUT') }}
    @endif
    <input type="hidden" name="idusuario" value="{{ Auth::id() }}" />
    <div class="card-body">
        <div class="form-row mb-3">
        <div class="col">
            <label for="titulo">Título da notícia</label>
            <input type="text"
                class="form-control {{ $errors->has('titulo') ? 'is-invalid' : '' }}"
                placeholder="Título"
                name="titulo"
                @if(isset($resultado))
                value="{{ $resultado->titulo }}"
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
            <span class="input-group-btn">
                <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-default">
                <i class="fas fa-picture-o"></i> Inserir/Alterar imagem
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
        </div>
        <div class="form-group mt-2">
        <label for="conteudo">Conteúdo da página</label>
        <textarea name="conteudo"
            class="form-control {{ $errors->has('conteudo') ? 'is-invalid' : '' }} my-editor"
            id="conteudo"
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
        <div class="form-row">
        <div class="col">
            <label for="regionais">Regional</label>
            <select name="regionais" class="form-control">
            <option value="">Todas</option>
            @foreach($regionais as $regional)
                @if(isset($resultado))
                    @if($resultado->idregional == $regional->idregional)
                    <option value="{{ $regional->idregional }}" selected>{{ $regional->regional }}</option>
                    @else
                    <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                    @endif
                @else
                <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                @endif
            @endforeach
            </select>
            <small class="form-text text-muted">
            <em>* Associar notícia à alguma regional</em>
            </small>
        </div>
        <div class="col">
            <label for="curso">Curso</label>
            <input type="number"
                name="curso"
                class="form-control"
                placeholder="Código da Turma"
                @if(isset($resultado))
                value="{{ $resultado->idcurso }}"
                @endif
                />
            <small class="form-text text-muted">
            <em>* Associar notícia à algum curso</em>
            </small>
        </div>
        </div>
    </div>
    <div class="card-footer float-right">
        <a href="/admin/noticias" class="btn btn-default">Cancelar</a>
        <button type="submit" class="btn btn-primary ml-1">
        @if(isset($resultado))
            Salvar
        @else
            Publicar
        @endif
        </button>
    </div>
</form>