@extends('admin.layout.app')

@section('content')

@php
use \App\Http\Controllers\Helper;
@endphp

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1 class="d-inline mr-3 align-middle">Páginas Deletadas</h1>
        <a href="/admin/paginas" class="btn btn-primary">Lista de Páginas</a>
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
              Lista de páginas deletadas do CORE-SP
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
                @foreach($paginas as $pagina)
                <tr>
                  <td>{{ $pagina->idpagina }}</td>
                  <td>{{ $pagina->titulo }}</td>
                  <td>{{ Helper::formataData($pagina->deleted_at) }}</td>
                  <td>
                    <a href="/admin/paginas/restore/{{ $pagina->idpagina }}" class="btn btn-sm btn-primary">Restaurar</a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="card-footer">
            {{ $paginas->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection