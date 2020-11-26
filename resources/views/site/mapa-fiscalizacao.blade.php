@extends('site.layout.app', ['title' => 'Mapa da Fiscalização'])

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/banner-SIG.jpg') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Fiscalização
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-noticias">
  <div class="container">
    <div class="row" id="conteudo-principal">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-3 align-self-center">
            <h2 class="stronger">Mapa da Fiscalização do Core-SP</h2>
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>

    <div class="linha-lg"></div>

    <div class="row justify-content-center">
      <div class="col-md-2">
          @if(empty($anos))
          <select id="ano-mapa" class="form-control" disabled>
          <option value="Ano indisponível" selected>Ano indisponível</option>

          @else
          <select id="ano-mapa" class="form-control">
          @foreach($anos as $ano)
          @if($anoSelecionado->ano == $ano)
          <option value="{{ $ano }}" selected>{{ $ano }}</option>
          @else
          <option value="{{ $ano }}">{{ $ano }}</option>
          @endif
          @endforeach
          @endif
        </select>
      </div>
    </div>

    @if(!empty($anos))
    <div class="row justify-content-center">
      <div class="col-lg-8">
        {!! file_get_contents((public_path() . '/img/sp.svg')) !!}
      </div>

      <div id="dados-fiscalizacao" class="col-lg-4 align-self-center text-center">
        <div id="instrucao-mapa" class="conteudo-txt">
          <p>Clique em uma das regionais para obter mais detalhes sobre fiscalização do ano {{ $anoSelecionado->ano }}.<p>
        </div>

        @foreach($anoSelecionado->dadoFiscalizacao as $r)
        <div id="dado-{{ $r->regional->prefixo }}" class="card bg-light dado-regional d-none">
          <div class="card-header">
            <h5>{{ $r->regional->prefixo }} - {{ $r->regional->regional }}</h5>
          </div>
          <div class="card-body">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th style="width:60%">Ação</th>
                  <th style="width:20%">PF</th>
                  <th style="width:20%">PJ</th>
                <tr>
              </thead>
              <tbody>
                <tr>
                  <td style="width:60%">Notificação</td>
                  <td style="width:20%">{{ $r->notificacaopf }}</td>
                  <td style="width:20%">{{ $r->notificacaopj }}</td>
                </tr>
                <tr>
                  <td style="width:60%">Auto de Constatação</td>
                  <td style="width:20%">{{ $r->constatacaopf }}</td>
                  <td style="width:20%">{{ $r->constatacaopj }}</td>
                </tr>
                <tr>
                  <td style="width:60%">Auto de Infração</td>
                  <td style="width:20%">{{ $r->infracaopf }}</td>
                  <td style="width:20%">{{ $r->infracaopj }}</td>
                </tr>
                <tr>
                  <td style="width:60%">Registro Convertido</td>
                  <td style="width:20%">{{ $r->convertidopf }}</td>
                  <td style="width:20%">{{ $r->convertidopj }}</td>
                </tr>
                <tr>
                  <td style="width:60%">Orientação</td>
                  <td class="text-center" colspan="2">{{ $r->orientacao }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        @endforeach

      </div>
    </div>  
    @endif

  </div>
</section>

@endsection