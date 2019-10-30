@extends('site.layout.app', ['title' => isset($pagina) ? $pagina->titulo : 'Página'])

@section('description')
  <meta name="description" content="{!! retornaDescription($pagina->conteudo) !!}">
@endsection

@section('meta')
  <meta property="og:url" content="{{ url('/') . '/' . $pagina->slug }}">
  <meta property="og:type" content="article" />
  <meta property="og:title" content="{{ $pagina->titulo }}">
  <meta property="og:description" content="{!! retornaDescription($pagina->conteudo) !!}">
  <meta property="og:image" content="{{ isset($pagina->img) ? formataImageUrl(url('/') . $pagina->img) : asset('img/news-generica-2.png') }}">
  <meta property="og:image:secure_url" content="{{ isset($pagina->img) ? formataImageUrl(url('/') . $pagina->img) : asset('img/news-generica-2.png') }}">

  <meta name="twitter:title" content="{{ $pagina->titulo }}">
  <meta name="twitter:description" content="{!! retornaDescription($pagina->conteudo) !!}">
  <meta name="twitter:image" content="{{ isset($pagina->img) ? formataImageUrl(url('/') . $pagina->img) : asset('img/news-generica-2.png') }}">
@endsection

@section('content')

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    @if(isset($pagina->img))
    <img src="{{asset($pagina->img)}}" />
    @else
    <img src="{{asset('img/institucional.png')}}" alt="CORE-SP">
    @endif
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          {{ $pagina->titulo }}
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-conteudo">
  <div class="container">
    <div class="row" id="conteudo-principal">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-4 align-self-center">
            <h2 class="stronger">{{ $pagina->subtitulo }}</h2>
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mt-2">
	    <div class="col-lg-8 conteudo-txt pr-4">
	      {!! $pagina->conteudo !!}
	    </div>
      <div class="col-lg-4">
        @include('site.inc.content-sidebar')
      </div>
	  </div>
  </div>
</section>

@endsection