@extends('site.layout.app', ['title' => '404'])

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/banner-erro.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          404
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-erro">
  <div class="containter text-center">
    <img src="{{ asset('img/404.png') }}" alt="Erro 404" />
  </div>
</section>

@endsection