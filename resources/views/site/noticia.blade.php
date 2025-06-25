@php
  // Devido o include 'noticia-grid', o título é sobrescrito na aba do navegador ao terminar de carregar a página. Isso corrige.
  $titulo = $noticia->titulo;
@endphp
@extends('site.layout.app', ['title' => $titulo ])

@section('description')
  <meta name="description" content="{!! retornaDescription($noticia->conteudo) !!}" />
@endsection

@section('meta')
  <meta property="og:url" content="{{ url('/') . '/noticia/' . $noticia->slug }}" />
  <meta property="og:type" content="article" />
  <meta property="og:title" content="{{ $noticia->titulo }}" />
  <meta property="og:description" content="{!! retornaDescription($noticia->conteudo) !!}" />
  <meta property="og:image" content="{{ isset($noticia->img) ? formataImageUrl(url('/') . $noticia->img) : asset('img/news-generica-2.png') }}" />
  <meta property="og:image:secure_url" content="{{ isset($noticia->img) ? formataImageUrl(url('/') . $noticia->img) : asset('img/news-generica-2.png') }}" />

  <meta name="twitter:title" content="{{ $noticia->titulo }}" />
  <meta name="twitter:description" content="{!! retornaDescription($noticia->conteudo) !!}" />
  <meta name="twitter:image" content="{{ isset($noticia->img) ? formataImageUrl(url('/') . $noticia->img) : asset('img/news-generica-2.png') }}" />
@endsection

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/noticias.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Notícia
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-noticias">
  <div class="container">
    @if(isset($noticia))
    <div class="row" id="conteudo-principal">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-4 align-self-center">
            <h2 class="stronger">{{ $noticia->titulo }}</h2>
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg-mini"></div>
    <div class="row">
      <div class="col">
      <h6 class="light mb-4"><span class="normal">Por: </span>{{ $noticia->user->perfil->nome === 'Editor' ? 'Setor de comunicação' : $noticia->user->nome }} | <span class="normal">{{ onlyDate($noticia->created_at) }}</span> | <span class="normal">{{ onlyHour($noticia->created_at) }}</span></h6>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-8 pr-4">
        @if(isset($noticia->img))
        <div class="noticia-img mb-4">
          <img class="lazy-loaded-image lazy" src="{{ $noticia->imgBlur() }}" data-src="{{ asset($noticia->img) }}" />
        </div>
        @endif
        <div class="conteudo-txt">
          {!! $noticia->conteudo !!}
        </div>
      </div>
      <div class="col-lg-4">
        @include('site.inc.content-sidebar')
      </div>
    </div>
    <div class="row mt-5">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-4 align-self-center">
            <h4 class="stronger">Mais Notícias</h4>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mb-3">
      @foreach($tres as $noticia)
        @include('site.inc.noticia-grid')
      @endforeach
    </div>
    @else
      @include('site.inc.content-error')
    @endif
  </div>
</section>

@endsection