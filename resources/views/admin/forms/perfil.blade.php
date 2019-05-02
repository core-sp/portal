<form role="form" method="POST">
    @csrf
    {{ method_field('PUT') }}
    <div class="card-body">
        <table class="table table-bordered perfilEdit">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Visualizar</th>
                    <th>Criar</th>
                    <th>Editar</th>
                    <th>Apagar</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Usuários</td>
                    @foreach($permissoesUser as $p)
                    @php $name = $p->controller.'_'.$p->metodo; @endphp
                        @if(strpos($p->perfis, session('idperfil').',') !== false)
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" checked /></td>
                        @else
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" /></td>
                        @endif
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer float-right">
        <a href="/admin/usuarios/perfis" class="btn btn-default">Cancelar</a>
        <button type="submit" class="btn btn-primary ml-1">Salvar</button>
    </div>
</form>