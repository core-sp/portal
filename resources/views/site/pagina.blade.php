@extends('site.layout.app', ['title' => $pagina->titulo])

@section('meta')
  <meta property="og:url" content="{{ url('/') . '/' . $pagina->slug }}">
  <meta property="og:type" content="article" />
  <meta property="og:title" content="{{ $pagina->titulo }}">
  <meta property="og:description" content="{!! strip_tags(substr($pagina->conteudo, 0, 100)) !!}">
  <meta property="og:image" content="{{ isset($pagina->img) ? url('/') . $pagina->img : asset('img/news-generica-2.png') }}">

  <meta name="twitter:title" content="{{ $pagina->titulo }}">
  <meta name="twitter:description" content="{!! strip_tags(substr($pagina->conteudo, 0, 100)) !!}">
  <meta name="twitter:image" content="{{ isset($pagina->img) ? url('/') . $pagina->img : asset('img/news-generica-2.png') }}">
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
            <h4 class="stronger">{{ $pagina->subtitulo }}</h4>
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