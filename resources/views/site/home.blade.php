@extends('layout.app', ['title' => 'Home'])

@section('content')

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

<section id="espaco-representante" class="pt-4 pb-3 cinza-claro-bg">
  <div class="container">
    <div class="row mb-3">
      <div class="col-12">
        <div class="home-title">
          <blockquote>
            <h4 class="pr-3">Espaço do Representante</h4>
          </blockquote>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-3">
        <div class="box text-center">
          <i class="fas fa-calculator"></i>
          <p class="text-uppercase">Simulador de Cálculos</p>
        </div>
      </div>
      <div class="col-sm-3">
        <div class="box text-center">
          <i class="fas fa-briefcase"></i>
          <p class="text-uppercase">Balcão de Oportunidades</p>
        </div>
      </div>
      <div class="col-sm-3">
        
      </div>
      <div class="col-sm-3">
        
      </div>
    </div>
  </div>
</section>

@endsection
