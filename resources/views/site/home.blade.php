@extends('layout.app', ['title' => 'Home'])

@section('content')

@php
use \App\Http\Controllers\Helper;
@endphp

<section id="banner-principal" class="mt-1">
  <div class="container-fluid">
    <div class="row">
      <div id="carousel" class="carousel slide" data-ride="carousel">
        <!--
        <ol class="carousel-indicators">
          <li data-target="#carousel" data-slide-to="0" class="active"></li>
          <li data-target="#carousel" data-slide-to="1"></li>
          <li data-target="#carousel" data-slide-to="2"></li>
        </ol>
      -->
        <div class="carousel-inner h-100">
          <div class="carousel-item h-100 active">
            <img class="d-block w-100" src="{{ asset('img/banner-v3.png') }}" alt="First slide">
          </div>
          <!--
          <div class="carousel-item h-100">
            <img class="d-block w-100" src="{{ asset('img/banner-02.jpg') }}" alt="Second slide">
          </div>
          <div class="carousel-item h-100">
            <img class="d-block w-100" src="{{ asset('img/banner-04.jpg') }}" alt="Third slide">
          </div>
        -->
        </div>
        <a class="carousel-control-prev" href="#carousel" role="button" data-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carousel" role="button" data-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="sr-only">Next</span>
        </a>
      </div>
    </div>
  </div>
</section>

<section id="espaco-representante" class="pt-5">
  <div class="container">
    <div class="row mb-2">
      <div class="col-12">
        <div class="home-title">
          <blockquote>
            <i></i>
            <h4 class="pr-3 ml-1">Espaço do Representante</h4>
          </blockquote>
          <h5 class="float-right cinza-claro-bg">
            <a href="#"><i class="fas fa-user icon-title"></i> Acessar o portal</a>
          </h5>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-3">
        <div class="box text-center azul-escuro-bg h-100">
          <div class="inside-box">
            <img src="{{ asset('img/002-money.png') }}" class="inside-img" />
            <p class="text-uppercase mt-3 branco">Simulador de<br /> Cálculos</p>
            <a href="#" class="btn-box mt-4">Calcular</a>
          </div>
        </div>
      </div>
      <div class="col-sm-3">
        <a href="/balcao-de-oportunidades">
          <div class="box text-center azul-bg h-100">
            <div class="inside-box">
              <img src="{{ asset('img/001-work.png') }}" class="inside-img" />
              <p class="text-uppercase mt-3 branco">Balcão de Oportunidades</p>
              <div class="btn-box mt-4">Acessar</div>
            </div>
          </div>
        </a>
      </div>
      <div class="col-sm-3">
        <div class="box text-center azul-escuro-bg h-100">
          <div class="inside-box">
            <img src="{{ asset('img/003-bill.png') }}" class="inside-img" />
            <p class="text-uppercase mt-3 branco">Emissão de boleto<br>anuidade 2019</p>
            <a href="#" class="btn-box mt-4">EMITIR</a>
          </div>
        </div>
      </div>
      <div class="col-sm-3">
        <div class="box text-center azul-bg h-100">
          <div class="inside-box">
            <img src="{{ asset('img/004-note.png') }}" class="inside-img" />
            <p class="text-uppercase mt-3 branco">Registre-se no portal<br>do representante</p>
            <a href="#" class="btn-box mt-4">Registrar</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="novo-core" class="mb-5">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="pb-5 pt-5 pl-2 pr-2 text-center novo-core-box">
          <h2 class="stronger branco text-uppercase mb-5">um novo core-sp para você!</h2>
          <a href="#" class="btn-novo-core"><h4 class="normal">Agenda</h4></a>
          <a href="#" class="btn-novo-core"><h4 class="normal">Concursos</h4></a>
          <a href="#" class="btn-novo-core"><h4 class="normal">Feiras</h4></a>
          <a href="/cursos" class="btn-novo-core"><h4 class="normal">Cursos</h4></a>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="home-news" class="pb-5">
  <div class="container">
    <div class="row mb-2">
      <div class="col-12">
        <div class="home-title">
          <blockquote>
            <i></i>
            <h4 class="pr-3 ml-1">Notícias</h4>
          </blockquote>
          <h5 class="float-right branco-bg">
            <a href="/noticias"><i class="fas fa-plus-circle icon-title"></i> Ver mais notícias</a>
          </h5>
        </div>
      </div>
    </div>
    <div class="row">
      @foreach($noticias as $noticia)
        @include('site.inc.noticia-grid')
      @endforeach
    </div>
  </div>
</section>

<section id="beneficios" class="pt-5">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="beneficios-box row nomargin">
          <div class="col-sm-5">
            <img src="{{ asset('img/benef-v2.png') }}" id="computer" />
          </div>
          <div class="col-sm-7 beneficios-txt">
            <h2 class="stronger branco text-uppercase">Programa de Benefícios</h2>
            <p class="branco light">Novo Core-SP traz benefícios diferenciados para representantes comerciais</p>
            <div>
              <a href="#" class="btn-beneficios">saiba mais</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="eouv-calendario" class="pb-5">
  <div class="container">
    <div class="row">
      <div class="col-6">
        <div class="home-title">
          <blockquote>
            <i></i>
            <h4 class="pr-3 ml-1">E-ouv</h4>
          </blockquote>
        </div>
        <div class="row">
          <div class="col-8">
            <img src="{{ asset('img/computer.png') }}" />
          </div>
          <div class="col-4 eouv-imgs align-self-center pl-3">
            <div class="m-auto pb-3">
              <img src="{{ asset('img/icon-eouv-01.png') }}" class="azul-bg" data-toggle="tooltip" title="Fale Conosco" />
              <img src="{{ asset('img/icon-eouv-02.png') }}" class="azul-escuro-bg" />
            </div>
            <div class="m-auto pb-3">
              <img src="{{ asset('img/icon-eouv-03.png') }}" class="verde-escuro-bg" />
              <img src="{{ asset('img/icon-eouv-04.png') }}" class="azul-bg" />
            </div>
            <div class="m-auto">
              <img src="{{ asset('img/icon-eouv-05.png') }}" class="azul-escuro-bg" />
              <img src="{{ asset('img/icon-eouv-06.png') }}" class="verde-escuro-bg" />
            </div>
          </div>
        </div>
      </div>
      <div class="col-6">
        <div class="home-title">
          <blockquote>
            <i></i>
            <h4 class="pr-3 ml-1">Calendário</h4>
          </blockquote>
        </div>
        <div id="calendario" class="row">
          <div class="col-8">
            <img src="{{ asset('img/calendario.png') }}" />
          </div>
          <div class="col-4 align-self-center text-right pr-4">
            <div class="calendario-txt">
              <p class="preto">Confira o calendário completo de<br>atendimento e expediente <br>de sua região.</p>
              <a href="#" class="btn-calendario mt-4">
                <h4 class="normal">confira</h4>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection
