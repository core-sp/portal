@extends('layout.app', ['title' => 'Cursos'])

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
    <div class="row pb-4">
      <div class="col">
        <div class="home-title">
          <blockquote>
            <i></i>
            <h4 class="pr-3 ml-1">Próximos cursos</h4>
          </blockquote>
          <h5 class="float-right cinza-claro-bg">
            <a href="#"><i class="fas fa-history icon-title"></i> Cursos anteriores</a>
          </h5>
        </div>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="row mb-3">
      @foreach($cursos as $curso)
        <div class="col-4">
          <div class="h-100 d-flex flex-column">
            <a href="/curso/{{ $curso->idcurso }}">
              <div class="curso-grid">
                <img src="{{asset($curso->img)}}" class="bn-img" />
                <div class="curso-grid-txt">
                    <h6 class="light cinza-claro">{{ $curso->regional->regional }} - {{ Helper::onlyDate($curso->datarealizacao) }}</h6>
                    <h5 class="branco mt-1">{{ $curso->tipo }} - {{ $curso->tema }}</h5>
                </div>
              </div>
            </a>
            <div class="curso-grid-content text-center">
              <p>{!! $curso->resumo !!}</p>
              @if(CursoInscritoController::permiteInscricao($curso->idcurso))
                <a href="/curso/inscricao/{{ $curso->idcurso }}" class="btn-curso-grid mt-3">Inscrever-se</a>
              @else
                Inscrições esgotadas
              @endif
            </div>
          </div>
        </div>
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