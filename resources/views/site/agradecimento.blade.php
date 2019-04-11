@extends('layout.app', ['title' => 'Inscrição'])

@section('content')

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/cursos.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Obrigado!
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-agradece">
  <div class="container">
    <div class="row">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-3 align-self-center">
            <h4 class="stronger"><i class="fas fa-check"></i>&nbsp;&nbsp;Sucesso!</h4>
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row">
      <div class="col">
        <div class="pb-5 pt-5 pl-5 pr-5 novo-core-box">
          @if(isset($agradece))
            <h5 class="light branco">{!! $agradece !!}</h5>
          @endif
        </div>
      </div>
    </div>
  </div>
</section>

@endsection