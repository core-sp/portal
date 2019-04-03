@extends('layout.app', ['title' => $curso->tipo.' - '.$curso->tema.' ('.$curso->idcurso.')'])

@section('content')

@php
use \App\Http\Controllers\Helper;
use \App\Http\Controllers\CursoInscritoController;
$datarealizacao = Helper::onlyDate($curso->datarealizacao);
$datatermino = Helper::onlyDate($curso->datatermino);
@endphp

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/cursos.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          {{ $curso->tipo }} - {{ $curso->tema }}
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-licitacao">
  <div class="container">
    <div class="row">
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
    <div class="row mt-4">
      <div class="col-sm-4 edital-info">
        <table class="table table-bordered mb-4">
          <tbody>
            <tr>
              <td class="quarenta"><h6>Status</h6></td>
              <td><h6 class="light">
                {{ CursoInscritoController::btnSituacao($curso->idcurso) }}
              </h6></td>
            </tr>
            <tr>
              <td><h6>Início</h6></td>
              <td><h6 class="light">{{ $datarealizacao }}</h6></td>
            </tr>
            <tr>
              <td><h6>Término</h6></td>
              <td><h6 class="light">{{ $datatermino }}</h6></td>
            </tr>
            <tr>
              <td><h6>Horário</h6></td>
              <td><h6 class="light">
                @if($datarealizacao == $datatermino)
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
        <a href="/curso/inscricao/{{ $curso->idcurso }}" class="btn-curso-interna">Inscrever-se</a>
      </div>
      <div class="col-sm-8">
        <div class="curso-img">
          <img src="{{asset($curso->img)}}" class="bn-img" />
        </div>
        <div class="edital-download mt-3 conteudo-txt">
          <h4 class="stronger">Descrição</h4>
          <div class="linha-lg"></div>
          {!! $curso->descricao !!}
        </div>
      </div>
    </div>
  </div>
</section>

@endsection