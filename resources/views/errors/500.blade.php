@extends('site.layout.app', ['title' => '404'])

@section('content')

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/concursos.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          500
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-erro">
  <div class="containter text-center">
    Erro 500
  </div>
</section>

@endsection