@extends('site.layout.app', ['title' => 'Concursos'])

@section('content')

@php
use App\Http\Controllers\Helper;
@endphp

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/concursos.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          {{ $concurso->modalidade }} {{ $concurso->nrprocesso }}
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-concursos">
  <div class="container">
    <div class="row" id="conteudo-principal">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-3 align-self-center">
            <h4 class="stronger">{{ $concurso->titulo }}</h4>
          </div>
          <div class="align-self-center">
            <a href="/concursos" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mt-2">
      <div class="col-sm-4 edital-info">
        <table class="table table-bordered">
          <tbody>
            <tr>
              <td class="quarenta"><h6>Situação</h6></td>
              <td><h6 class="light">{{ Helper::btnSituacao($concurso->situacao) }}</h6></td>
            </tr>
            <tr>
              <td><h6>Modalidade</h6></td>
              <td><h6 class="light">{{ $concurso->modalidade }}</h6></td>
            </tr>
            <tr>
              <td><h6>Nº do Processo</h6></td>
              <td><h6 class="light">{{ $concurso->nrprocesso }}</h6></td>
            </tr>
            <tr>
              <td><h6>Data de realização</h6></td>
              <td><h6 class="light">{{ Helper::formataData($concurso->datarealizacao) }}</h6></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="col-sm-8">
        <a href="{{ $concurso->linkexterno }}" target="_blank" />
          <div class="edital-download d-flex">
            <div class="flex-one">
              <h5>Link Oficial do Concurso</h5>
              <h6 class="light">Clique aqui para acompanhar todos os detalhes do concurso</h6>
            </div>
            <button class="btn-edital"><i class="fas fa-download"></i>&nbsp;&nbsp;Acessar</button>
          </div>
     	  </a>
        <div class="edital-download mt-3">
          <h5>Objeto</h5>
           <div class="linha-lg-mini"></div>
           <h6 class="light">{!! $concurso->objeto !!}</h6>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection