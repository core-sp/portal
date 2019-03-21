@extends('admin.layout.app')

@section('content')

@php
use \App\Http\Controllers\Helper;
@endphp

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1 class="d-inline mr-3 align-middle">Licitações Deletadas</h1>
        <a href="/admin/licitacoes" class="btn btn-primary">Lista de Licitações</a>
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
              Lista de licitações deletadas do CORE-SP
            </h3>
          </div>
          <div class="card-body">
            <table class="table">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Modalidade</th>
                  <th>Nº da Licitação</th>
                  <th>Deletada em:</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                @foreach($licitacoes as $licitacao)
                <tr>
                  <td>{{ $licitacao->idlicitacao }}</td>
                  <td>{{ $licitacao->modalidade }}</td>
                  <td>{{ $licitacao->nrlicitacao }}</td>
                  <td>{{ Helper::formataData($licitacao->deleted_at) }}</td>
                  <td>
                    <a href="/admin/licitacoes/restore/{{ $licitacao->idlicitacao }}" class="btn btn-sm btn-primary">Restaurar</a>
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