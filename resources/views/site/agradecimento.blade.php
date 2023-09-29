@extends('site.layout.app', ['title' => 'Obrigado'])

@section('content')

<section id="pagina-cabecalho">
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
            <h2 class="stronger"><i class="fas fa-check"></i>&nbsp;&nbsp;Sucesso!</h2>
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
        <div class="novo-core-box">
          @if(isset($agradece))
            <h5 class="text-white mb-3"><a href="{{ $link_temp }}">Link temporário de teste para verificar e-mail</a></h5>
            <h5 class="light branco" data-clarity-mask="True">{!! $agradece !!}</h5>
          @endif
        </div>
      </div>
    </div>
    @if(isset($adendo))
    <div class="row mt-3">
      <h4 class="light">{!! $adendo !!}</h4>
    </div>
    @endif
  </div>
</section>

@endsection