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
            <h3 class="card-title d-inline">
              Lista de usuários do CORE-SP
            </h3>
            @if(isset($busca))
            <a href="/admin/usuarios" class="badge badge-primary d-inline ml-2">Mostrar todas</a>
            @endif
            <div class="card-tools">
              <form class="input-group input-group-sm"
                method="GET"
                role="form"
                action ="/admin/usuarios/busca">
                <input type="text"
                  name="q"
                  class="form-control float-right"
                  placeholder="Pesquisar" />
                <div class="input-group-append">
                  <button type="submit" class="btn btn-default">
                    <i class="fas fa-search"></i>
                  </button>
                </div>
              </form>
            </div>
          </div>
          <div class="card-body">
            @if(isset($usuarios))
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
            @else
            Nenhum usuário encontrado
            <a href="/admin/usuarios" class="badge badge-primary d-inline ml-2">Mostrar todas</a>
            @endif
          </div>
          <div class="card-footer">
            @if(isset($usuarios))
            <div class="row">
              <div class="col-sm-5 align-self-center">
                @if($usuarios->count() > 1)
                Exibindo {{ $usuarios->firstItem() }} a {{ $usuarios->lastItem() }} usuários de {{ $usuarios->total() }} resultados.
                @endif
              </div>
              <div class="col-sm-7">
                <div class="float-right">
                  {{ $usuarios->links() }}
                </div>
              </div>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection