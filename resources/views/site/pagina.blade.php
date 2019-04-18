@extends('site.layout.app', ['title' => $pagina->titulo])

@section('content')

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    @if(isset($pagina->img))
    <img src="{{asset($pagina->img)}}" />
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
	    <div class="col-sm-8 conteudo-txt pr-4">
	      {!! $pagina->conteudo !!}
	    </div>
	  <div class="col-sm-4">
	  	@include('site.inc.content-sidebar')
	  </div>
	</div>
  </div>
</section>

@endsection