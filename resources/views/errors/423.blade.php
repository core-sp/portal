@extends('site.layout.app', ['title' => '423'])

@section('content')

<section id="pagina-cabecalho">
    <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
        <img src="{{ asset('img/banner-erro.png') }}" />
        <div class="row position-absolute pagina-titulo">
            <div class="container text-center">
                <h1 class="branco text-uppercase">
                Erro 423
                </h1>
            </div>
        </div>
    </div>
</section>

<section id="pagina-erro">
  <div class="container text-center">
    <h5>Por questão de segurança, este acesso foi bloqueado. Entre em contato com o CORE-SP e informe:<br>IP {{ $exception->getMessage() }}.</h5>
  </div>
</section>

@endsection