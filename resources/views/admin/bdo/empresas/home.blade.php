@extends('admin.layout.app')

@section('content')

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1 class="d-inline mr-3 align-middle">Empresas</h1>
        <a href="/admin/bdo/empresas/criar" class="btn btn-primary mr-1">Nova Empresa</a>
        <a href="#" class="btn btn-warning">Empresas Deletadas</a>
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
              Lista de empresas do Banco de Oportunidades do CORE-SP
            </h3>
            @if(isset($busca))
            <a href="/admin/bdo/empresas" class="badge badge-primary d-inline ml-2">Mostrar todos</a>
            @endif
            <div class="card-tools">
              <form class="input-group input-group-sm"
                method="GET"
                role="form"
                action ="/admin/bdo/empresas/busca">
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
            @if(isset($empresas))
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Segmento</th>
                  <th>Razão Social</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                @foreach($empresas as $empresa)
                <tr>
                  <td>{{ $empresa->idempresa }}</td>
                  <td>{{ $empresa->segmento }}</td>
                  <td>{{ $empresa->razaosocial }}</td>
                  <td>
                    <a href="#" class="btn btn-sm btn-default" target="_blank">Ver</a>
                    <a href="#" class="btn btn-sm btn-secondary">Inscritos</a>
                    <a href="#" class="btn btn-sm btn-primary">Editar</a>
                    <form method="POST" action="#" class="d-inline">
                      @csrf
                      {{ method_field('DELETE') }}
                      <input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm('Tem certeza que deseja excluir a empresa?')" />
                    </form>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
            @else
            Nenhum curso encontrado
            <a href="/admin/bdo/empresas" class="badge badge-primary d-inline ml-2">Mostrar todos</a>
            @endif
          </div>
          <div class="card-footer">
            @if(isset($empresas))
            <div class="row">
              <div class="col-sm-5 align-self-center">
                @if($empresas->count() > 1)
                Exibindo {{ $empresas->firstItem() }} a {{ $empresas->lastItem() }} cursos de {{ $empresas->total() }} resultados.
                @endif
              </div>
              <div class="col-sm-7">
                <div class="float-right">
                  {{ $empresas->links() }}
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