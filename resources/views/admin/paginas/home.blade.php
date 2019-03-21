@extends('admin.layout.app')

@section('content')

@php
use \App\Http\Controllers\Helper;
@endphp

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1 class="d-inline mr-3 align-middle">Páginas</h1>
        <a href="/admin/paginas/criar" class="btn btn-primary mr-1">Nova Página</a>
        <a href="/admin/paginas/lixeira" class="btn btn-warning">Páginas Deletadas</a>
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
              Lista de páginas do CORE-SP
            </h3>
          </div>
          <div class="card-body">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Título</th>
                  <th>Categoria</th>
                  <th>Última alteração</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                @foreach($paginas as $pagina)
                <tr>
                  <td>{{ $pagina->idpagina }}</td>
                  <td>{{ $pagina->titulo }}</td>
                  <td>
                    @if(isset($pagina->paginacategoria->nome))
                      {{ $pagina->paginacategoria->nome }}
                    @else
                      Sem Categoria
                    @endif
                  </td>
                  <td>
                    {{ Helper::formataData($pagina->updated_at) }}
                    <br />
                    <small>Por: {{ $pagina->user->nome }}</small>
                  </td>
                  <td>
                    @if(isset($pagina->paginacategoria->nome))
                    <a href="/{{ Helper::toSlug($pagina->paginacategoria->nome) }}/{{ $pagina->slug }}" class="btn btn-sm btn-default" target="_blank">Ver</a>
                    @else
                    <a href="/{{ $pagina->slug }}" class="btn btn-sm btn-default" target="_blank">Ver</a>
                    @endif
                    <a href="/admin/paginas/editar/{{ $pagina->idpagina }}" class="btn btn-sm btn-primary">Editar</a>
                    <form method="POST" action="/admin/paginas/apagar/{{ $pagina->idpagina }}" class="d-inline">
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
            {{ $paginas->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection