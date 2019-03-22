@extends('admin.layout.app')

@section('content')

@php
use \App\Http\Controllers\Helper;
use \App\Http\Controllers\Helpers\CursoHelper;
@endphp

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1 class="d-inline mr-3 align-middle">Cursos</h1>
        <a href="/admin/cursos/criar" class="btn btn-primary mr-1">Novo Curso</a>
        <a href="/admin/cursos/lixeira" class="btn btn-warning">Cursos Deletados</a>
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
              Lista de cursos do CORE-SP
            </h3>
            @if(isset($busca))
            <a href="/admin/cursos" class="badge badge-primary d-inline ml-2">Mostrar todos</a>
            @endif
            <div class="card-tools">
              <form class="input-group input-group-sm"
                method="GET"
                role="form"
                action ="/admin/cursos/busca">
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
            @if(isset($cursos))
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Tipo / Tema</th>
                  <th>Onde / Quando</th>
                  <th>Vagas</th>
                  <th>Regional</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                @foreach($cursos as $curso)
                <tr>
                  <td>{{ $curso->idcurso }}</td>
                  <td>{{ $curso->tipo }}<br />{{ $curso->tema }}</td>
                  <td>
                    {{ $curso->endereco }}<br />
                    {{ Helper::formataData($curso->datarealizacao) }}
                  </td>
                  <td>{{ CursoHelper::contagem($curso->idcurso) }} / {{ $curso->nrvagas }}</td>
                  <td>{{ $curso->regional->regional }}</td>
                  <td>
                    <a href="/curso/{{ $curso->idcurso }}" class="btn btn-sm btn-default" target="_blank">Ver</a>
                    <a href="/admin/cursos/inscritos/{{ $curso->idcurso }}" class="btn btn-sm btn-secondary">Inscritos</a>
                    <a href="/admin/cursos/editar/{{ $curso->idcurso }}" class="btn btn-sm btn-primary">Editar</a>
                    <form method="POST" action="/admin/cursos/apagar/{{ $curso->idcurso }}" class="d-inline">
                      @csrf
                      {{ method_field('DELETE') }}
                      <input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm('Tem certeza que deseja excluir o curso?')" />
                    </form>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
            @else
            Nenhum curso encontrado
            <a href="/admin/cursos" class="badge badge-primary d-inline ml-2">Mostrar todos</a>
            @endif
          </div>
          <div class="card-footer">
            @if(isset($cursos))
            <div class="row">
              <div class="col-sm-5 align-self-center">
                @if($cursos->count() > 1)
                Exibindo {{ $cursos->firstItem() }} a {{ $cursos->lastItem() }} cursos de {{ $cursos->total() }} resultados.
                @endif
              </div>
              <div class="col-sm-7">
                <div class="float-right">
                  {{ $cursos->links() }}
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