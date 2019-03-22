@extends('admin.layout.app')

@section('content')

@php
use \App\Http\Controllers\Helper;
@endphp

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1 class="d-inline mr-3 align-middle">Concursos Deletados</h1>
        <a href="/admin/concursos" class="btn btn-primary">Lista de Concursos</a>
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
              Lista de concursos deletados do CORE-SP
            </h3>
          </div>
          <div class="card-body">
            <table class="table">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Modalidade</th>
                  <th>Nº do Processo</th>
                  <th>Deletada em:</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                @foreach($concursos as $concurso)
                <tr>
                  <td>{{ $concurso->idconcurso }}</td>
                  <td>{{ $concurso->modalidade }}</td>
                  <td>{{ $concurso->processo }}</td>
                  <td>{{ Helper::formataData($concurso->deleted_at) }}</td>
                  <td>
                    <a href="/admin/concursos/restore/{{ $concurso->idconcurso }}" class="btn btn-sm btn-primary">Restaurar</a>
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