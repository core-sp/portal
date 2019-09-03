@extends('site.layout.app', ['title' => 'Notícias'])

@section('meta')
<meta name="description" content="{!! retornaDescription($noticia->conteudo) !!}" />

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

@php
  use App\Http\Controllers\Helper;
@endphp

<section id="pagina-cabecalho" class="mt-1">
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
      <h6 class="light mb-4"><span class="normal">Por: </span>{{ $noticia->user->nome }} | <span class="normal">{{ Helper::onlyDate($noticia->created_at) }}</span> | <span class="normal">{{ Helper::onlyHour($noticia->created_at) }}</span></h6>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-8 pr-4">
        <div class="noticia-img">
          @if(isset($noticia->img))
          <img src="{{asset($noticia->img)}}" />
          @else
          <img src="{{ asset('img/news-generica-2.png') }}" />
          @endif
        </div>
        <div class="mt-4 conteudo-txt">
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
      @php $i = 0; @endphp
      @foreach($tres as $noticia)
        @php $i++; @endphp
        @include('site.inc.noticia-grid')
      @endforeach
    </div>
    @else
      @include('site.inc.content-error')
    @endif
  </div>
</section>

@endsection