@extends('site.layout.app', ['title' => 'Cursos'])

@section('content')

@php
use \App\Http\Controllers\Helper;
use \App\Http\Controllers\CursoInscritoController;
$now = now();
@endphp

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/cursos.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          @if(isset($curso))
          {{ $curso->tipo }} - {{ $curso->tema }}
          @else
          erro
          @endif
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-licitacao">
  <div class="container">
    @if(isset($curso))
    <div class="row" id="conteudo-principal">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-3 align-self-center">
            <h4 class="stronger">{{ $curso->tipo }} - {{ $curso->tema }} ({{ $curso->idcurso }})</h4>
          </div>
          <div class="align-self-center">
            <a href="/cursos" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mt-2">
      <div class="col-xl-4 col-lg-5 edital-info">
        <table class="table table-bordered mb-4">
          <tbody>
            <tr>
              <td class="quarenta"><h6>Status</h6></td>
              <td><h6 class="light">
                {{ CursoInscritoController::btnSituacao($curso->idcurso) }}
              </h6></td>
            </tr>
            <tr>
              <td><h6>Aonde</h6></td>
              <td><h6 class="light">{{ $curso->regional->regional }}</h6></td>
            </tr>
            <tr>
              <td><h6>Início</h6></td>
              <td><h6 class="light">{{ Helper::onlyDate($curso->datarealizacao) }}</h6></td>
            </tr>
            <tr>
              <td><h6>Término</h6></td>
              <td><h6 class="light">{{ Helper::onlyDate($curso->datatermino) }}</h6></td>
            </tr>
            <tr>
              <td><h6>Horário</h6></td>
              <td><h6 class="light">
                @if(Helper::onlyDate($curso->datarealizacao) == Helper::onlyDate($curso->datatermino))
                  Das {{ Helper::onlyHour($curso->datarealizacao) }} às {{ Helper::onlyHour($curso->datatermino) }}
                @else
                  A partir das {{ Helper::onlyHour($curso->datarealizacao) }}
                @endif
              </h6></td>
            </tr>
            <tr>
              <td><h6>Nº de vagas</h6></td>
              <td><h6 class="light">{{ $curso->nrvagas }}</h6></td>
            </tr>
          </tbody>
        </table>
        @if($curso->datarealizacao > $now)
          @if(CursoInscritoController::permiteInscricao($curso->idcurso))
            <div class="center-992">
              <a href="/curso/inscricao/{{ $curso->idcurso }}" class="btn-curso-interna">Inscrever-se</a>
            </div>
          @endif
        @endif
      </div>
      <div class="col-xl-8 col-lg-7 mt-2-992">
        <div class="curso-img">
          <img src="{{asset($curso->img)}}" class="bn-img" />
        </div>
        <div class="edital-download mt-3 conteudo-txt-mini">
          <h4 class="stronger">Descrição</h4>
          <div class="linha-lg"></div>
          {!! $curso->descricao !!}
        </div>
      </div>
    </div>
    @else
      @include('site.inc.content-error')
    @endif
  </div>
</section>

@endsection