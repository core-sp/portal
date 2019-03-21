@extends('admin.layout.app')

@section('content')

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1>Notícias deletadas</h1>
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
              Lista de notícias deletadas do CORE-SP
            </h3>
          </div>
          <div class="card-body">
            <table class="table">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Título</th>
                  <th>Deletada em:</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                @foreach($noticias as $noticia)
                <tr>
                  <td>{{ $noticia->idnoticia }}</td>
                  <td>{{ $noticia->titulo }}</td>
                  <td>{{ $noticia->deleted_at }}</td>
                  <td>
                    <a href="/admin/noticias/restore/{{ $noticia->idnoticia }}" class="btn btn-sm btn-primary">Restaurar</a>
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
        <a href="/admin/noticias" class="btn btn-primary">Lista de Notícias</a>
      </div>
    </div>
  </div>
</section>

@endsection