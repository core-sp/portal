@extends('admin.layout.app')

@section('content')

@php
use \App\Http\Controllers\Helper;
@endphp

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1 class="d-inline mr-3 align-middle">Usuários deletados</h1>
        <a href="/admin/usuarios" class="btn btn-primary">Lista de Usuários</a>
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
              Lista de usuários deletados do CORE-SP
            </h3>
          </div>
          <div class="card-body">
            <table class="table">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Nome</th>
                  <th>Email</th>
                  <th>Deletado em:</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                @foreach($usuarios as $usuario)
                <tr>
                  <td>{{ $usuario->idusuario }}</td>
                  <td>{{ $usuario->nome }}</td>
                  <td>{{ $usuario->email }}</td>
                  <td>{{ Helper::formataData($usuario->deleted_at) }}</td>
                  <td>
                    <a href="/admin/usuarios/restore/{{ $usuario->idusuario }}" class="btn btn-sm btn-primary">Restaurar</a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection