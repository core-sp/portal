@extends('site.layout.app', ['title' => '504'])

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/banner-erro.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Erro 504
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-erro">
  <div class="containter text-center">
    @if($exception->getMessage())
      {{ $exception->getMessage() }}
    @else
      Oops, algo demorou mais do que o esperado!
    @endif
  </div>
</section>

@endsection