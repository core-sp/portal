@extends('admin.layout.app')

@section('content')

@php
use \App\Http\Controllers\Helper;
@endphp

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1 class="d-inline mr-3 align-middle">Licitações</h1>
        <a href="/admin/licitacoes/criar" class="btn btn-primary mr-1">Nova Licitação</a>
        <a href="/admin/licitacoes/lixeira" class="btn btn-warning">Licitações Deletadas</a>
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
              Lista de licitações do CORE-SP
            </h3>
          </div>
          <div class="card-body">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Modalidade</th>
                  <th>Nº da Licitação</th>
                  <th>Nº do Processo</th>
                  <th>Situação</th>
                  <th>Data de Realização</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                @foreach($licitacoes as $licitacao)
                <tr>
                  <td>{{ $licitacao->idlicitacao }}</td>
                  <td>{{ $licitacao->modalidade }}</td>
                  <td>{{ $licitacao->nrlicitacao }}</td>
                  <td>{{ $licitacao->nrprocesso }}</td>
                  <td>{{ $licitacao->situacao }}</td>
                  <td>{{ Helper::formataData($licitacao->datarealizacao) }}</td>
                  <td>
                    <a href="/licitacao/{{ $licitacao->idlicitacao }}" class="btn btn-sm btn-default" target="_blank">Ver</a>
                    <a href="/admin/licitacoes/editar/{{ $licitacao->idlicitacao }}" class="btn btn-sm btn-primary">Editar</a>
                    <form method="POST" action="/admin/licitacoes/apagar/{{ $licitacao->idlicitacao }}" class="d-inline">
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
            {{ $licitacoes->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection