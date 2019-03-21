@extends('admin.layout.app')

@section('content')

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1 class="d-inline align-middle mr-3">Categorias de Páginas</h1>
        <a href="/admin/paginas/categorias/criar" class="btn btn-primary">Nova Categoria</a>
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
              Lista de categorias de páginas do CORE-SP
            </h3>
            
          </div>
          <div class="card-body">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Nome</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                @foreach($categorias as $categoria)
                <tr>
                  <td>{{ $categoria->idpaginacategoria }}</td>
                  <td>{{ $categoria->nome }}</td>
                  <td>
                    <a href="/admin/paginas/categorias/mostra/{{ $categoria->idpaginacategoria }}" class="btn btn-sm btn-default">Ver</a>
                    <a href="/admin/paginas/categorias/editar/{{ $categoria->idpaginacategoria }}" class="btn btn-sm btn-primary">Editar</a>
                    <form method="POST" action="/admin/paginas/categorias/apagar/{{ $categoria->idpaginacategoria }}" class="d-inline">
                      @csrf
                      {{ method_field('DELETE') }}
                      <input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm('Tem certeza que deseja excluir a página?')" />
                    </form>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="card-footer">
            {{ $categorias->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection