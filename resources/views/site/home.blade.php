@extends('layout.app', ['title' => 'Home'])

@section('content')

@php
use \App\Http\Controllers\Helper;
@endphp

<section id="banner-principal" class="mt-1">
  <div class="container-fluid">
    <div class="row">
      <div id="carousel" class="carousel slide" data-ride="carousel">
        <ol class="carousel-indicators">
          <li data-target="#carousel" data-slide-to="0" class="active"></li>
          <li data-target="#carousel" data-slide-to="1"></li>
          <li data-target="#carousel" data-slide-to="2"></li>
        </ol>
        <div class="carousel-inner h-100">
          <div class="carousel-item h-100 active">
            <img class="d-block w-100" src="{{ asset('img/banner-03.jpg') }}" alt="First slide">
          </div>
          <div class="carousel-item h-100">
            <img class="d-block w-100" src="{{ asset('img/banner-02.jpg') }}" alt="Second slide">
          </div>
          <div class="carousel-item h-100">
            <img class="d-block w-100" src="{{ asset('img/banner-04.jpg') }}" alt="Third slide">
          </div>
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

<section id="espaco-representante" class="pt-5 pb-5 cinza-claro-bg">
  <div class="container">
    <div class="row mb-2">
      <div class="col-12">
        <div class="home-title">
          <blockquote>
            <h4 class="pr-3">Espaço do Representante</h4>
          </blockquote>
          <h5 class="float-right cinza-claro-bg"><i class="fas fa-user"></i> Acessar o portal</h5>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-3">
        <div class="box text-center azul-escuro-bg">
          <div class="inside-box">
            <img src="{{ asset('img/002-money.png') }}" />
            <p class="text-uppercase mt-3 branco">Simulador de Cálculos</p>
            <a href="#" class="btn-box mt-4">Acessar</a>
          </div>
        </div>
      </div>
      <div class="col-sm-3">
        <div class="box text-center azul-bg">
          <div class="inside-box">
            <img src="{{ asset('img/001-work.png') }}" />
            <p class="text-uppercase mt-3 branco">Balcão de Oportunidades</p>
            <a href="#" class="btn-box mt-4">Acessar</a>
          </div>
        </div>
      </div>
      <div class="col-sm-3">
        <div class="box text-center azul-escuro-bg">
          <div class="inside-box">
            <img src="{{ asset('img/003-bill.png') }}" />
            <p class="text-uppercase mt-3 branco">Emissão de boleto<br>anuidade 2019</p>
            <a href="#" class="btn-box mt-4">Acessar</a>
          </div>
        </div>
      </div>
      <div class="col-sm-3">
        <div class="box text-center azul-bg">
          <div class="inside-box">
            <img src="{{ asset('img/004-note.png') }}" />
            <p class="text-uppercase mt-3 branco">Registre-se no portal<br>do representante</p>
            <a href="#" class="btn-box mt-4">Acessar</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="novo-core" class="mb-5">
  <div class="container">
    <div class="row mt-5 mb-2">
      <div class="col-12">
        <div class="home-title">
          <blockquote>
            <h4 class="pr-3">Um novo CORE-SP para você</h4>
          </blockquote>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-6 position-relative">
        <div class="novo-core-element position-relative">
          <figure class="nomargin">
            <img src="{{ asset('img/002-calendar.png') }}" />
          </figure>
          <div class="nce-text">
            <div class="nce-text-border">
              <h3 class="d-inline align-self-center">AGENDA</h3>
              <i class="fas fa-angle-double-right float-right"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 position-relative">
        <div class="novo-core-element position-relative">
          <figure class="nomargin">
            <img src="{{ asset('img/001-test.png') }}" />
          </figure>
          <div class="nce-text">
            <div class="nce-text-border">
              <h3 class="d-inline align-self-center">CONCURSOS</h3>
              <i class="fas fa-angle-double-right float-right"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 position-relative">
        <div class="novo-core-element position-relative">
          <figure class="text-right nomargin">
            <img src="{{ asset('img/statistics.png') }}" />
          </figure>
          <div class="nce-text-right">
            <div class="nce-text-border-right text-right">
              <h3 class="d-inline align-self-center">FEIRAS</h3>
              <i class="fas fa-angle-double-right float-left"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 position-relative">
        <div class="novo-core-element position-relative">
          <figure class="text-right nomargin">
            <img src="{{ asset('img/004-blackboard.png') }}" />
          </figure>
          <div class="nce-text-right">
            <div class="nce-text-border-right text-right">
              <h3 class="d-inline align-self-center">CURSOS</h3>
              <i class="fas fa-angle-double-right float-left"></i>
            </div>
          </div>
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
            <h4 class="pr-3">Notícias</h4>
          </blockquote>
          <h5 class="float-right branco-bg"><i class="fas fa-plus-circle"></i> Ver mais notícias</h5>
        </div>
      </div>
    </div>
    <div class="row">
      @foreach($noticias as $noticia)
      <div class="col-sm-4">
        <div class="box-news">
          <img src="{{asset($noticia->img)}}" class="bn-img" />
          <div class="box-news-txt">
            <h6 class="light cinza-claro">{{ Helper::newsData($noticia->updated_at) }}</h6>
            <h5 class="branco mt-1">{{ $noticia->titulo }}</h5>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

@endsection
