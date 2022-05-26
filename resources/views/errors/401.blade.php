@extends('site.layout.app', ['title' => '401'])

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/banner-erro.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Erro 401
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-erro">
  <div class="container text-center">
    @if($exception->getMessage())
      <h4 class="font-normal">{{ $exception->getMessage() }}</h4>
    @else
      Oops, houve algum problema!
    @endif
  </div>
</section>

@endsection