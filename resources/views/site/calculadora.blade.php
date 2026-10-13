@extends('site.layout.app', ['title' => 'Calculadora'])

@section('description')
  <meta name="description" content="Calculadora informativa do Representante Comercial." />
@endsection

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/banner-espaco-do-contador.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Calculadora
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-busca">
  <div class="container">
    <div class="row" id="conteudo-principal">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-4 align-self-center">
            <h2 class="stronger">Calculadora do Representante Comercial</h2>
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mt-2" id="conteudo-principal">
      
      <iframe
        src="https://www.core-rj.org.br/calculadora-iframe.php"
        width="100%"
        height="1300"
        style="border: none; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);"
        allow="local-storage"
        scrolling="auto"
        title="Calculadora do Representante Comercial">
      </iframe>

    </div>
  </div>
</section>

@endsection