@extends('layout.app', ['title' => 'Cursos'])

@section('content')

@php
use \App\Http\Controllers\Helper;
use \App\Http\Controllers\CursoInscritoController;
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
        <h4 class="stronger">{{ $curso->tipo }} - {{ $curso->tema }} ({{ $curso->idcurso }})</h4>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mt-4">
      <div class="col-sm-4 edital-info">
        <table class="table table-bordered">
          <tbody>
            <tr>
              <td class="quarenta"><h6>Status</h6></td>
              <td><h6 class="light">
                @if(CursoInscritoController::permiteInscricao($curso->idcurso))
                  Vagas abertas
                @else
                  Inscrições esgotadas
                @endif
              </h6></td>
            </tr>
            <tr>
              <td><h6>Data de Realização</h6></td>
              <td><h6 class="light">{{ Helper::onlyDate($curso->datarealizacao) }}</h6></td>
            </tr>
            <tr>
              <td><h6>Horário</h6></td>
              <td><h6 class="light">{{ Helper::onlyHour($curso->datarealizacao) }}</h6></td>
            </tr>
            <tr>
              <td><h6>Nº de vagas</h6></td>
              <td><h6 class="light">{{ $curso->nrvagas }}</h6></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="col-sm-8">
        <div class="curso-img">
          <img src="{{asset($curso->img)}}" class="bn-img" />
        </div>
        <div class="edital-download mt-3 conteudo-txt">
          <h5>Descrição</h5>
          <div class="linha-lg"></div>
          {!! $curso->descricao !!}
        </div>
      </div>
    </div>
  </div>
</section>

@endsection