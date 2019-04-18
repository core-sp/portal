@extends('site.layout.app', ['title' => 'Cursos'])

@section('content')

@php
use \App\Http\Controllers\Helper;
use \App\Http\Controllers\CursoInscritoController;
@endphp

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/cursos.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Cursos
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-cursos">
  <div class="container">
    <div class="row pb-3" id="conteudo-principal">
      <div class="col">
        <div class="home-title">
          <blockquote>
            <i></i>
            <h4 class="pr-3 ml-1">Cursos Anteriores</h4>
          </blockquote>
          <h5 class="float-right cinza-claro-bg">
            <a href="/cursos"><i class="fas fa-history icon-title"></i> Próximos cursos</a>
          </h5>
        </div>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="row mb-3">
      @foreach($cursos as $curso)
        @include('site.inc.curso-grid')
      @endforeach
    </div>
    <div class="text-right">
      @if(isset($cursos))
        {{ $cursos->links() }}
      @endif
    </div>
  </div>
</section>

@endsection