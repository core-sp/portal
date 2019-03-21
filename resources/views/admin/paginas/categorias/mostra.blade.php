@extends('admin.layout.app')

@section('content')

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1>Páginas na categoria: {{ $cat->nome }}</h1>
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
              Lista de páginas desta categoria
            </h3>
          </div>
          <div class="card-body">
            <table class="table">
              <tbody>
                <tr>
                  <th>Código</th>
                  <th>Nome</th>
                  <th>Última Atualização</th>
                  <th>Ações</th>
                </tr>
                @foreach($paginas as $pagina)
                <tr>
                  <td>{{ $pagina->idpagina }}</td>
                  <td>{{ $pagina->titulo }}</td>
                  <td>{{ $pagina->updated_at }}</td>
                  <td>
                    <a href="/{{ $pagina->idpagina }}" class="btn btn-sm btn-default">Ver</a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-12">
        <a href="/admin/paginas/categorias" class="btn btn-primary">Categorias</a>
      </div>
    </div>
  </div>
</section>

@endsection