@extends('site.layout.app', ['title' => 'Prestação de Contas do Core-SP'])

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/banner-prestacao-de-contas.jpg') }}" alt="CORE-SP" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
        Prestação de Contas do Core-SP
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-conteudo">
  <div class="container">
    <div class="row" id="conteudo-principal">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-4 align-self-center">
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mt-2">
	    <div class="col-lg conteudo-txt prestacao-txt">
        <p class="text-justify">
          O Conselho Regional dos Representantes Comerciais no Estado de São Paulo, em consonância com a Instrução Normativa nº 84/2020 e a Decisão Normativa nº 187/2020, 
          ambas do Tribunal de Contas da União, implementou novo modelo de prestação de contas, garantindo maior transparência, completude e clareza na disponibilização 
          das informações acerca da gestão e dos atos emanados pelo Core-SP.
        </p>
        <p class="text-justify">
          Dessa forma, todas as informações referentes à prestação de contas serão permanentemente disponibilizadas e atualizadas ao longo do exercício financeiro, de 
          acordo com os prazos definidos pelo Tribunal de Contas da União, possibilitando o imediato acesso por qualquer cidadão ou interessado por meio dos seguintes 
          links:
        </p>

        {!! $resultado !!}
        
      </div>
      <div class="col-lg-4">
        @include('site.inc.content-sidebar')
      </div>
	  </div>
  </div>
</section>

@endsection