@extends('admin.layout.app')

@section('content')

@php
use \App\Http\Controllers\Helper;
@endphp

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1 class="d-inline mr-3 align-middle">Notícias</h1>
        <a href="/admin/noticias/criar" class="btn btn-primary mr-1">Nova Notícia</a>
        <a href="/admin/noticias/lixeira" class="btn btn-warning">Notícias Deletadas</a>
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
              Lista de notícias do CORE-SP
            </h3>
          </div>
          <div class="card-body">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Título</th>
                  <th>Regional</th>
                  <th>Última alteração</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                @foreach($noticias as $noticia)
                <tr>
                  <td>{{ $noticia->idnoticia }}</td>
                  <td>{{ $noticia->titulo }}</td>
                  <td>
                  	@if(isset($noticia->idregional))
                  	  {{ $noticia->regional->regional }}
                  	@else
                  	  Todas
                  	@endif
                  </td>
                  <td>
                    {{ Helper::formataData($noticia->updated_at) }}
                    <br />
                    <small>Por: {{ $noticia->user->nome }}</small>
                  </td>
                  <td>
                    <a href="/noticia/{{ $noticia->slug }}" class="btn btn-sm btn-default" target="_blank">Ver</a>
                    <a href="/admin/noticias/editar/{{ $noticia->idnoticia }}" class="btn btn-sm btn-primary">Editar</a>
                    <form method="POST" action="/admin/noticias/apagar/{{ $noticia->idnoticia }}" class="d-inline">
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
            {{ $noticias->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection