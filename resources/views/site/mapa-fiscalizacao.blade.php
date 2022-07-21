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
            <h2 class="stronger">Sistema de Informação Geográfica (SIG do CORE-SP)</h2>
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
        <select id="ano-mapa" class="form-control" {{ !isset($todosPeriodos) ? 'disabled' : '' }}>
        @if(!isset($todosPeriodos))
          <option value="Indisponível" selected>Indisponível</option>
        @else
          @foreach($todosPeriodos as $periodo)
          <option value="{{ $periodo->id }}" {{ isset($periodoSelecionado) && ($periodoSelecionado->id == $periodo->id) ? 'selected' : '' }}>{{ $periodo->periodo }}</option>
          @endforeach
        @endif
        </select>
      </div>
    </div>

    @if(isset($todosPeriodos))
    <div class="row justify-content-center">
      <div class="col-lg-7">
        {!! file_get_contents((public_path() . '/img/sp.svg')) !!}
      </div>

      @if(isset($periodoSelecionado))
      <div id="dados-fiscalizacao" class="col-lg-5 align-self-center text-center">
        <div id="instrucao-mapa" class="conteudo-txt">
          <p>Clique em uma das regionais para obter mais detalhes sobre fiscalização do ano {{ $periodoSelecionado->periodo }}.<p>
        </div>

        @foreach($periodoSelecionado->dadoFiscalizacao as $r)
        <div id="dado-{{ $r->regional->prefixo }}" class="card bg-light dado-regional d-none">
          <div class="card-header">
            <h5>{{ $r->regional->prefixo }} - {{ $r->regional->regional }}</h5>
          </div>
          <div class="card-body">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th style="width:50%">Ação</th>
                  <th style="width:20%">PF</th>
                  <th style="width:20%">PJ</th>
                <tr>
              </thead>
              <tbody>
                <tr>
                  <td class="text-left" style="width:50%; font-size: 15px">Processos de Fiscalização <!--<small class="text-danger">*<small>--></td>
                  <td style="width:20%">{{-- $r->processofiscalizacaopf --}}</td>
                  <td style="width:20%">{{ $r->processofiscalizacaopj }}</td>
                </tr>
                <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">Registros Convertidos</td>
                  <td style="width:20%">{{-- $r->registroconvertidopf --}}</td>
                  <td style="width:20%">{{ $r->registroconvertidopj }}</td>
                </tr>
                <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">Processos de Verificação</td>
                  <td class="text-center" colspan="2">{{ $r->processoverificacao }}</td>
                </tr>
                <!-- <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">Dispensa de Registro (de ofício)</td>
                  <td class="text-center" colspan="2">{{-- $r->dispensaregistro --}}</td>
                </tr> -->
                <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">Notificações de RT</td>
                  <td class="text-center" colspan="2">{{ $r->notificacaort }}</td>
                </tr>
                <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">Orientações às representadas</td>
                  <td class="text-center" colspan="2">{{ $r->orientacaorepresentada }}</td>
                </tr>
                <!-- <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">Orientações aos representantes</td>
                  <td class="text-center" colspan="2">{{ $r->orientacaorepresentante }}</td>
                </tr> -->
                <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">Cooperação Institucional</td>
                  <td class="text-center" colspan="2">{{ $r->cooperacaoinstitucional }}</td>
                </tr>
                <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">Autos de Constatação</td>
                  <td class="text-center" colspan="2">{{ $r->autoconstatacao }}</td>
                </tr>
                <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">Autos de Infração</td>
                  <td class="text-center" colspan="2">{{ $r->autosdeinfracao }}</td>
                </tr>
                <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">Multa Administrativa</td>
                  <td class="text-center" colspan="2">{{ $r->multaadministrativa }}</td>
                </tr>
              </tbody>
            </table>
            <!-- <p class="text-danger text-left"><small>* notificações, ofícios e autos</small></p> -->
          </div>
        </div>
        @endforeach

        @if(isset($dataAtualizacao))
        <p>Dados atualizados em: {{ $dataAtualizacao }}</p>
        @endif

      </div>
      @endif
    </div>  
    @endif

  </div>
</section>

@endsection