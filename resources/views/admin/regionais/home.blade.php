@extends('admin.layout.app')

@section('content')

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1>Regionais</h1>
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
              Lista de regionais do CORE-SP
            </h3>
            @if(isset($busca))
              <a href="/admin/regionais" class="badge badge-primary d-inline ml-2">Mostrar todas</a>
            @endif
            <div class="card-tools">
              <form class="input-group input-group-sm"
                method="GET"
                role="form"
                action ="/admin/regionais/busca">
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
            @if(isset($regionais))
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Regional</th>
                  <th>Telefone</th>
                  <th>Email</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                @foreach($regionais as $regional)
                <tr>
                  <td>{{ $regional->idregional }}</td>
                  <td>{{ $regional->regional }}</td>
                  <td>{{ $regional->telefone }}</td>
                  <td>{{ $regional->email }}</td>
                  <td>
                    <a href="/admin/regionais/mostra/{{ $regional->idregional }}" class="btn btn-sm btn-default">Ver</a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
            @else
            Nenhuma regional encontrada
            <a href="/admin/regionais" class="badge badge-primary d-inline ml-2">Mostrar todas</a>
            @endif
          </div>
          <div class="card-footer">
            @if(isset($regionais))
            <div class="row">
              <div class="col-sm-5 align-self-center">
                @if($regionais->count() > 1)
                Exibindo {{ $regionais->firstItem() }} a {{ $regionais->lastItem() }} regionais de {{ $regionais->total() }} resultados.
                @endif
              </div>
              <div class="col-sm-7">
                <div class="float-right">
                  {{ $regionais->links() }}
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