@extends('site.layout.app', ['title' => '419'])

@section('content')

<section id="pagina-cabecalho">
    <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
        <img src="{{ asset('img/banner-erro.png') }}" />
        <div class="row position-absolute pagina-titulo">
            <div class="container text-center">
                <h1 class="branco text-uppercase">
                Erro 419
                </h1>
            </div>
        </div>
    </div>
</section>

<section id="pagina-erro">
  <div class="container text-center">
    <p>Por favor, verifique se sua sessão está válida e se o uso de cookies está habilitado e tente novamente.</p>
  </div>
</section>

@endsection