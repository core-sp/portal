@extends('site.layout.app', ['title' => '405'])

@section('content')

<section id="pagina-cabecalho">
    <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
        <img src="{{ asset('img/banner-erro.png') }}" />
        <div class="row position-absolute pagina-titulo">
            <div class="container text-center">
                <h1 class="branco text-uppercase">
                Erro 405
                </h1>
            </div>
        </div>
    </div>
</section>

<section id="pagina-erro">
  <div class="container text-center">
    @if($exception->getMessage())
      <p>{{ $exception->getMessage() }}</p>
    @else
      Oops, houve algum problema!
    @endif
  </div>
</section>

@endsection