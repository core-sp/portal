<form role="form" method="POST">
    @csrf
    @if(isset($permissoesArray))
        @method('PUT')
    @endif
    <div class="card-body">
        @if(isset($permissoesArray))
        <table class="table table-bordered perfilEdit table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Visualizar</th>
                    <th>Criar</th>
                    <th>Editar</th>
                    <th>Apagar</th>
                    <th>Show</th>
                </tr>
            </thead>
            <tbody>
                @foreach($permissoesArray as $controller)
                    <tr>
                        <td>{{ $controller['display'] }}</td>
                            @foreach($controller['permissoes'] as $permissao)

                                @if(!$permissao['editavel'])
                                    <td><input type="checkbox" class="form-check-input" disabled /></td>
                                @elseif($permissao['autorizado'])
                                    <td><input type="checkbox" class="form-check-input" name="{{ $controller['controller'].'_'.$permissao['metodo'] }}" checked /></td>
                                @else
                                    <td><input type="checkbox" class="form-check-input" name="{{ $controller['controller'].'_'.$permissao['metodo'] }}" /></td>
                                @endif

                            @endforeach
                    </tr>    
                @endforeach
            </tbody>
        </table>
        @else
        <div class="form-group">
            <label for="nome">Nome</label>
            <input type="text"
                class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
                placeholder="Nome"
                name="nome"
                />
            @if($errors->has('nome'))
                <div class="invalid-feedback">
                    {{ $errors->first('nome') }}
                </div>
            @endif
        </div>
        @endif
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="/admin/usuarios/perfis" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">Salvar</button>
        </div>
    </div>
</form>