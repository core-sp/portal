@extends('admin.layout.app')

@section('content')

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1 class="d-inline mr-3 align-middle">Usuários</h1>
        <a href="/admin/usuarios/criar" class="btn btn-primary mr-1">Novo Usuário</a>
        <a href="/admin/usuarios/lixeira" class="btn btn-warning">Usuários Deletados</a>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              Lista de usuários do CORE-SP
            </h3>
          </div>
          <div class="card-body">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Nome</th>
                  <th>Email</th>
                  <th>Perfil</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                @foreach($usuarios as $usuario)
                <tr>
                  <td>{{ $usuario->idusuario }}</td>
                  <td>{{ $usuario->nome }}</td>
                  <td>{{ $usuario->email }}</td>
                  <td>{{ $usuario->perfil[0]->nome }}</td>
                  <td>
                    <a href="/admin/usuarios/editar/{{ $usuario->idusuario }}" class="btn btn-sm btn-primary">Editar</a>
                    <form method="POST" action="/admin/usuarios/apagar/{{ $usuario->idusuario }}" class="d-inline">
                      @csrf
                      {{ method_field('DELETE') }}
                      <input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm('Tem certeza que deseja excluir o usuário?')" />
                    </form>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="card-footer">
            {{ $usuarios->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection