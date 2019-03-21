@extends('admin.layout.app')

@section('content')

@php
use \App\Http\Controllers\Helper;
@endphp

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1 class="d-inline mr-3 align-middle">
          Inscritos em {{ $curso->tipo }}: {{ $curso->tema }}
        </h1>
        <a href="/admin/cursos/adicionar-inscrito/{{ $curso->idcurso }}" class="btn btn-primary mr-1">Adicionar inscrito</a>
        <a href="/admin/cursos" class="btn btn-default">Lista de Cursos</a>
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
              Lista de inscritos
            </h3>
          </div>
          <div class="card-body">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>CPF</th>
                  <th>Nome</th>
                  <th>Telefone</th>
                  <th>Email</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                @foreach($inscritos as $inscrito)
                <tr>
                  <td>{{ $inscrito->cpf }}</td>
                  <td>{{ $inscrito->nome }}</td>
                  <td>{{ $inscrito->telefone }}</td>
                  <td>{{ $inscrito->email }}</td>
                  <td>
                    <form method="POST" action="/admin/cursos/cancelar-inscricao/{{ $inscrito->idcursoinscrito }}" class="d-inline">
                      @csrf
                      {{ method_field('PUT') }}
                      <input type="submit"
                        class="btn btn-sm btn-danger"
                        value="Cancelar inscrição"
                        onclick="return confirm('Tem certeza que deseja cancelar a inscrição do usuário {{ $inscrito->nome }}?')" />
                    </form>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="card-footer">
            {{ $inscritos->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection