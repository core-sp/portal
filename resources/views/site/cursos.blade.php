@extends('site.layout.app', ['title' => 'Cursos'])

@section('content')

@php
use \App\Http\Controllers\Helper;
use \App\Http\Controllers\CursoInscritoController;
@endphp

<section id="pagina-cabecalho">
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
            <h2 class="pr-3 ml-1">Próximos cursos</h2>
          </blockquote>
          <h5 class="float-right cinza-claro-bg">
            <a href="/cursos-anteriores"><i class="fas fa-history icon-title"></i> Cursos anteriores</a>
          </h5>
        </div>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="row mb-3">
      @if($cursos->isNotEmpty())
        @foreach($cursos as $curso)
          @include('site.inc.curso-grid')
        @endforeach
      @else
        <div class="col mt-3">
          <p><i class="fas fa-calendar-times"></i>&nbsp;&nbsp;<i>Nenhum curso agendado nos próximos meses</i></p>
        </div>
      @endif
    </div>
    <div class="text-right">
      @if(isset($cursos))
        {{ $cursos->links() }}
      @endif
    </div>
  </div>
</section>

@endsection