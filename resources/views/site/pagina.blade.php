@extends('layout.app', ['title' => $pagina->titulo])

@section('content')

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{asset($pagina->img)}}" />
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
    <div class="row">
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