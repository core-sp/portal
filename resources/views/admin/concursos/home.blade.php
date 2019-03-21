@extends('admin.layout.app')

@section('content')

@php
use \App\Http\Controllers\Helper;
@endphp

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1 class="d-inline mr-3 align-middle">Concursos</h1>
        <a href="/admin/concursos/criar" class="btn btn-primary mr-1">Novo Concurso</a>
        <a href="/admin/concursos/lixeira" class="btn btn-warning">Concursos Deletados</a>
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
              Lista de concursos do CORE-SP
            </h3>
          </div>
          <div class="card-body">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Modalidade</th>
                  <th>Nº do Processo</th>
                  <th>Situação</th>
                  <th>Data de Realização</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                @foreach($concursos as $concurso)
                <tr>
                  <td>{{ $concurso->idconcurso }}</td>
                  <td>{{ $concurso->modalidade }}</td>
                  <td>{{ $concurso->nrprocesso }}</td>
                  <td>{{ $concurso->situacao }}</td>
                  <td>{{ Helper::formataData($concurso->datarealizacao) }}</td>
                  <td>
                    <a href="/concurso/{{ $concurso->idconcurso }}" class="btn btn-sm btn-default" target="_blank">Ver</a>
                    <a href="/admin/concursos/editar/{{ $concurso->idconcurso }}" class="btn btn-sm btn-primary">Editar</a>
                    <form method="POST" action="/admin/concursos/apagar/{{ $concurso->idconcurso }}" class="d-inline">
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
            {{ $concursos->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection