<form role="form" method="POST">
    @csrf
    @if(isset($resultado))
        {{ method_field('PUT') }}
    @endif
    <input type="hidden" name="idusuario" value="{{ Auth::id() }}" />
    <div class="card-body">
        <div class="form-group">
            <label for="nome">Nome do Perfil</label>
            <input type="text"
                class="form-control {{ $errors->has('titulo') ? 'is-invalid' : '' }}"
                placeholder="Nome"
                name="nome"
                @if(isset($resultado))
                value="{{ $resultado->nome }}"
                @endif />
            @if($errors->has('nome'))
                <div class="invalid-feedback">
                    {{ $errors->first('nome') }}
                </div>
            @endif
        </div>
        <div class="form-group">
            <label for="descricao">Descrição</label>
            <textarea class="form-control"
                name="descricao"
                id="descricao"
                rows="5"
                placeholder="Descrição do perfil">@if(isset($resultado)) {{ $resultado->descricao }} @endif</textarea>
        </div>    
    </div>
    <div class="card-footer float-right">
        <a href="/admin/usuarios/perfis" class="btn btn-default">Cancelar</a>
        <button type="submit" class="btn btn-primary ml-1">
        @if(isset($resultado))
            Salvar
        @else
            Publicar
        @endif
        </button>
    </div>
</form>