@extends('site.layout.app', ['title' => 'Busca'])

@section('content')

@php
use App\Http\Controllers\Helper;
@endphp

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/banner-busca.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Busca
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-busca">
  <div class="container">
    <div class="row" id="conteudo-principal">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-4 align-self-center">
            <h4 class="stronger">Busca por: {{ $busca }}</h4>
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mt-2">
      <div class="col-sm-8">
        @if($resultados->isEmpty())
          Sua busca não retornou nenhum resultado!
        @else
          @foreach($resultados as $resultado)
          <div class="box-resultado">
            @if($resultado->tipo == "Notícia")
            <a href="/noticia/{{ $resultado->slug }}"">
              <h5>{{ $resultado->tipo }} - {{ $resultado->titulo }}</h5>
            </a>
            <h6 class="cinza">Publicado em: {{ Helper::onlyDate($resultado->created_at) }}</h6>
            @else
            <a href="/{{ $resultado->slug }}">
              <h5>{{ $resultado->tipo }} - {{ $resultado->titulo }}</h5>
            </a>
            @endif
            <p class="mt-2">{{ Helper::resumo($resultado->conteudo) }}</p>
            @if($resultado->tipo == "Notícia")
            <a href="/noticia/{{ $resultado->slug }}" class="btn-curso-grid mt-3">Confira</a>
            @else
            <a href="/{{ $resultado->slug }}" class="btn-curso-grid mt-3">Confira</a>
            @endif
          </div>
          @endforeach
        @endif
      </div>
      <div class="col-sm-4">
        @include('site.inc.content-sidebar')
      </div>
    </div>
  </div>
</section>



@endsection