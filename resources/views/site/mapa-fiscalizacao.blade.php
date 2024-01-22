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
        @if(isset($somaTotal))
        <div class="card bg-light">
          <div class="card-header">
            <h5 class="p-0">Total em {{ $periodoSelecionado->periodo }} - <span class="font-weight-normal">{{ $somaTotal['Total'] }}</span></h5>
          </div>
          <div class="card-body">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th style="width:50%">Ação</th>
                  <th style="width:50%" class="text-nowrap">Resultados em {{ $periodoSelecionado->periodo }}</th>
                <tr>
              </thead>
              <tbody>
              @foreach($somaTotal as $acao => $valor)
                @break($loop->last)
                <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">{!! $acao !!}</td>
                  <td class="text-center">{{ $valor }}</td>
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>
        </div>
        @endif
          <p>Clique em uma das regionais para obter mais detalhes sobre fiscalização do ano {{ $periodoSelecionado->periodo }}.</p>
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
                  <th style="width:50%" colspan="2">Resultados</th>
                <tr>
              </thead>
              <tbody>
                <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">{{ $r->campos()['autoconstatacao'] }}</td>
                  <td class="text-center" colspan="2">{{ $r->autoconstatacao }}</td>
                </tr>
                <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">{{ $r->campos()['autosdeinfracao'] }}</td>
                  <td class="text-center" colspan="2">{{ $r->autosdeinfracao }}</td>
                </tr>
                <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">{{ $r->campos()['multaadministrativa'] }}</td>
                  <td class="text-center" colspan="2">{{ $r->multaadministrativa }}</td>
                </tr>
                <!--<tr>
                  <td class="text-left" style="width:50%; font-size: 15px">{!! $r->campos()['processofiscalizacaopf'] !!} <small class="text-danger">*<small></td>
                  <td style="width:20%">{{-- $r->processofiscalizacaopf --}}</td>
                  <td style="width:20%">{{-- $r->processofiscalizacaopj --}}</td>
                </tr>-->
                <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">{!! $r->campos()['registroconvertidopj'] !!}</td>
                  <!--<td style="width:20%">{{-- $r->registroconvertidopf --}}</td>-->
                  <td style="width:50%">{{ $r->registroconvertidopj }}</td>
                </tr>
                <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">{{ $r->campos()['processoverificacao'] }}</td>
                  <td class="text-center" colspan="2">{{ $r->processoverificacao }}</td>
                </tr>
                <!-- <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">{{-- $r->campos()['dispensaregistro'] --}}</td>
                  <td class="text-center" colspan="2">{{-- $r->dispensaregistro --}}</td>
                </tr> -->
                <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">{{ $r->campos()['notificacaort'] }}</td>
                  <td class="text-center" colspan="2">{{ $r->notificacaort }}</td>
                </tr>
                <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">{{ $r->campos()['orientacaorepresentada'] }}</td>
                  <td class="text-center" colspan="2">{{ $r->orientacaorepresentada }}</td>
                </tr>
                <!-- <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">{{-- $r->campos()['orientacaorepresentante'] --}}</td>
                  <td class="text-center" colspan="2">{{ $r->orientacaorepresentante }}</td>
                </tr> -->
                <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">{{ $r->campos()['cooperacaoinstitucional'] }}</td>
                  <td class="text-center" colspan="2">{{ $r->cooperacaoinstitucional }}</td>
                </tr>
                <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">{{ $r->campos()['orientacaocontabil'] }}</td>
                  <td class="text-center" colspan="2">{{ $r->orientacaocontabil }}</td>
                </tr>
                <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">{{ $r->campos()['oficioprefeitura'] }}</td>
                  <td class="text-center" colspan="2">{{ $r->oficioprefeitura }}</td>
                </tr>
                <tr>
                  <td class="text-left" style="width:60%; font-size: 15px">{{ $r->campos()['oficioincentivo'] }}</td>
                  <td class="align-middle text-center" colspan="2">{{ $r->oficioincentivo }}</td>
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