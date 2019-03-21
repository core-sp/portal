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
            <h3 class="card-title">
              Lista de regionais do CORE-SP
            </h3>
          </div>
          <div class="card-body">
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
          </div>
          <div class="card-footer">
            {{ $regionais->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection