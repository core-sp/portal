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
            <h3 class="card-title d-inline">
              Lista de licitações do CORE-SP
            </h3>
            @if(isset($busca))
            <a href="/admin/licitacoes" class="badge badge-primary d-inline ml-2">Mostrar todas</a>
            @endif
            <div class="card-tools">
              <form class="input-group input-group-sm"
                method="GET"
                role="form"
                action ="/admin/licitacoes/busca">
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
            @if(isset($licitacoes))
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
            @else
            Nenhuma licitação encontrada
            <a href="/admin/licitacoes" class="badge badge-primary d-inline ml-2">Mostrar todas</a>
            @endif
          </div>
          <div class="card-footer">
            @if(isset($licitacoes))
            <div class="row">
              <div class="col-sm-5 align-self-center">
                @if($licitacoes->count() > 1)
                Exibindo {{ $licitacoes->firstItem() }} a {{ $licitacoes->lastItem() }} licitações de {{ $licitacoes->total() }} resultados.
                @endif
              </div>
              <div class="col-sm-7">
                <div class="float-right">
                  {{ $licitacoes->links() }}
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