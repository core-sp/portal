@extends('admin.layout.app')

@section('content')

@php
use \App\Http\Controllers\Helper;
@endphp

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1 class="d-inline mr-3 align-middle">Cursos Deletados</h1>
        <a href="/admin/cursos" class="btn btn-primary">Lista de Cursos</a>
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
              Lista de cursos deletados do CORE-SP
            </h3>
          </div>
          <div class="card-body">
            <table class="table">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Tipo / Tema</th>
                  <th>Onde / Quando</th>
                  <th>Regional</th>
                  <th>Deletado em:</th>
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
                  <td>{{ $curso->regional->regional }}</td>
                  <td>{{ Helper::formataData($curso->deleted_at) }}</td>
                  <td>
                    <a href="/admin/cursos/restore/{{ $curso->idcurso }}" class="btn btn-sm btn-primary">Restaurar</a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="card-footer">
            {{ $cursos->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection