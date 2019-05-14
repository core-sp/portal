<form role="form" method="POST">
    @csrf
    @if(isset($resultado))
        {{ method_field('PUT') }}
    @endif
    <input type="hidden" name="idusuario" value="{{ Auth::id() }}">
    <div class="card-body">
        <div class="form-group">
        <label for="nome">Nome da Categoria</label>
        <input type="text"
            class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
            placeholder="Título"
            name="nome"
            @if(isset($resultado))
            value="{{ $resultado->nome }}"
            @endif
            />
        @if($errors->has('nome'))
        <div class="invalid-feedback">
            {{ $errors->first('nome') }}
        </div>
        @endif
        </div>
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="/admin/paginas/categorias" class="btn btn-default">Cancelar</a>
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