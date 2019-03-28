@extends('admin.layout.app')

@section('content')

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1 class="d-inline mr-3 align-middle">Oportunidades</h1>
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
              Lista de oportunidades do Balcão de Oportunidades do CORE-SP
            </h3>
            @if(isset($busca))
            <a href="/admin/bdo" class="badge badge-primary d-inline ml-2">Mostrar todos</a>
            @endif
            <div class="card-tools">
              <form class="input-group input-group-sm"
                method="GET"
                role="form"
                action ="/admin/bdo/busca">
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
            @if(isset($oportunidades))
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Empresa</th>
                  <th>Segmento</th>
                  <th>Vagas</th>
                  <th>Status</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                @foreach($oportunidades as $oportunidade)
                <tr>
                  <td>{{ $oportunidade->idoportunidade }}</td>
                  <td>{{ $oportunidade->empresa->razaosocial }}</td>
                  <td>{{ $oportunidade->segmento }}</td>
                  <td>
                    @if(isset($oportunidade->vagaspreenchidas))
                    {{ $oportunidade->vagaspreenchidas }} / {{ $oportunidade->vagasdisponiveis }}
                    @else
                    X / {{ $oportunidade->vagasdisponiveis }}
                    @endif
                  </td>
                  <td>{{ $oportunidade->status }}</td>
                  <td>
                    <a href="#" class="btn btn-sm btn-default" target="_blank">Ver</a>
                    <a href="#" class="btn btn-sm btn-primary">Editar</a>
                    <form method="POST" action="#" class="d-inline">
                      @csrf
                      {{ method_field('DELETE') }}
                      <input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm('Tem certeza que deseja excluir a oportunidade?')" />
                    </form>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
            @else
            Nenhuma oportunidade encontrada
            <a href="/admin/bdo" class="badge badge-primary d-inline ml-2">Mostrar todos</a>
            @endif
          </div>
          <div class="card-footer">
            @if(isset($oportunidades))
            <div class="row">
              <div class="col-sm-5 align-self-center">
                @if($oportunidades->count() > 1)
                Exibindo {{ $oportunidades->firstItem() }} a {{ $oportunidades->lastItem() }} oportunidades de {{ $oportunidades->total() }} resultados.
                @endif
              </div>
              <div class="col-sm-7">
                <div class="float-right">
                  {{ $oportunidades->links() }}
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