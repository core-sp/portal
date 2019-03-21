@extends('admin.layout.app')

@section('content')

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1 class="d-inline mr-3 align-middle">Regional: {{ $regional->regional }}</h1>
        <a href="/admin/regionais" class="btn btn-warning">Lista de Regionais</a>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="invoice p-3 mb-3">
          <div class="row">
            <div class="col-12">
              <h4>
                <i class="fa fa-globe"></i>
                {{ $regional->regional }}
              </h4>
            </div>
          </div>
          <div class="row invoice-info mt-2">
            <div class="col-sm-4 invoice-col">
              <address>
                <strong>Endereço</strong>
                <br>
                {{
                  $regional->endereco.', '.
                  $regional->numero
                }}
                <br>
                @if(isset($regional->complemento))
                  {{ $regional->complemento }}
                @endif
                <br>
                {{ $regional->cep.' - ' }}
                {{ $regional->bairro }}
              </address>
            </div>
            <div class="col-sm-4 invoice-col">
              <address>
                <strong>Contato</strong>
                <br>
                Telefone: {{ $regional->telefone }}
                <br>
                Fax: {{ $regional->fax }}
                <br>
                Email: {{ $regional->email }}
              </address>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <strong>Descrição</strong>
              <br />
              {!! $regional->descricao !!}
            </div>
          </div>
          <div class="row mt-2">
            <div class="col-12">
              <p class="mb-2">
                <strong>
                <i class="fa fa-newspaper-o"></i>
                Notícias
                </strong>
              </p>
            </div>
          </div>
          <div class="row">
            <div class="col-12 table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Código</th>
                    <th>Título</th>
                    <th>Publicada</th>
                    <th>Última atualização</th>
                    <th>Ações</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($noticias as $noticia)
                  <tr>
                    <td>{{ $noticia->idnoticia }}</td>
                    <td>{{ $noticia->titulo }}</td>
                    <td>{{ $noticia->publicada == 1 ? 'Sim' : 'Não' }}</td>
                    <td>{{ $noticia->updated_at }}</td>
                    <td>
                      <a href="/noticia/{{ $noticia->slug }}" class="btn btn-sm btn-default" target="_blank">Ver</a>
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
  </div>
</section>

@endsection


<td>
  
</td>