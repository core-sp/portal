@extends('layout.app', ['title' => $noticia->titulo])

@section('content')

@php
  use App\Http\Controllers\Helper;
@endphp

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/noticias.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Notícias
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-noticias">
  <div class="container">
    <div class="row">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-4 align-self-center">
            <h4 class="stronger">{{ $noticia->titulo }}</h4>
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg-mini"></div>
    <div class="row">
      <div class="col">
      <p class="light mb-4"><span class="normal">Por: </span>{{ $noticia->user->nome }} | <span class="normal">{{ Helper::onlyDate($noticia->created_at) }}</span> | <span class="normal">{{ Helper::onlyHour($noticia->created_at) }}</span></p>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-8">
        <img src="{{asset($noticia->img)}}" />
        <div class="mt-4 conteudo-txt">
          {!! $noticia->conteudo !!}
        </div>
      </div>
      <div class="col-sm-4">
        <a href="/balcao-de-oportunidades">
          <div class="box azul-bg">
            <div class="inside-box-dois d-flex">
                <div class="align-self-center">
                <img src="http://127.0.0.1:8000/img/001-work.png" class="inside-img">
                </div>
                <div class="flex-one align-self-center pl-4">
                <h5 class="text-uppercase normal branco">Balcão de Oportunidades</h5>
                </div>
            </div>
          </div>
        </a>
        <a href="/cursos">
          <div class="box azul-escuro-bg mt-3">
            <div class="inside-box-dois d-flex">
                <div class="align-self-center">
                <img src="http://127.0.0.1:8000/img/teacher.png" class="inside-img">
                </div>
                <div class="flex-one align-self-center pl-4">
                <h5 class="text-uppercase normal branco">Conheça nossos cursos</h5>
                </div>
            </div>
          </div>
        </a>
        <a href="#">
          <div class="box azul-bg mt-3">
            <div class="inside-box-dois d-flex">
                <div class="align-self-center">
                <img src="http://127.0.0.1:8000/img/003-bill.png" class="inside-img">
                </div>
                <div class="flex-one align-self-center pl-4">
                <h5 class="text-uppercase normal branco">Emissão de Boleto Anuidade 2019</h5>
                </div>
            </div>
          </div>
        </a>
      </div>
    </div>
  </div>
</section>

@endsection