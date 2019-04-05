@extends('layout.app', ['title' => 'Licitações'])

@section('content')

@php
use App\Http\Controllers\Helper;
use App\Http\Controllers\LicitacaoSiteController;
@endphp

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/licitacoes.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          {{ $licitacao->modalidade }} {{ $licitacao->nrlicitacao }}
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-licitacao">
  <div class="container">
    <div class="row" id="conteudo-principal">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-3 align-self-center">
            <h4 class="stronger">{{ $licitacao->titulo }}</h4>
          </div>
          <div class="align-self-center">
            <a href="/licitacoes" class="btn-voltar">Voltar</a>
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
              <td><h6 class="light">{{ Helper::btnSituacao($licitacao->situacao) }}</h6></td>
            </tr>
            <tr>
              <td><h6>Modalidade</h6></td>
              <td><h6 class="light">{{ $licitacao->modalidade }}</h6></td>
            </tr>
            <tr>
              <td><h6>Nº do Processo</h6></td>
              <td><h6 class="light">{{ $licitacao->nrprocesso }}</h6></td>
            </tr>
            <tr>
              <td><h6>Nº da Licitação</h6></td>
              <td><h6 class="light">{{ $licitacao->nrlicitacao }}</h6></td>
            </tr>
            <tr>
              <td><h6>Data de realização</h6></td>
              <td><h6 class="light">{{ Helper::formataData($licitacao->datarealizacao) }}</h6></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="col-sm-8">
        <a href="{{ $licitacao->edital }}" download />
          <div class="edital-download d-flex">
            <div class="flex-one">
              <h5>Edital disponível para download</h5>
              <h6 class="light">Clique aqui para baixar o edital</h6>
            </div>
            <button class="btn-edital"><i class="fas fa-download"></i>&nbsp;&nbsp;Download</button>
          </div>
     	</a>
        <div class="edital-download mt-3">
          <h5>Objeto</h5>
           <div class="linha-lg-mini"></div>
           <h6 class="light">{!! $licitacao->objeto !!}</h6>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection