@extends('site.layout.app', ['title' => 'Cursos'])

@section('content')

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
            <h2 class="pr-3 ml-1">Cursos Anteriores</h2>
          </blockquote>
          <h5 class="float-right cinza-claro-bg">
            <a href="{{ route('cursos.index.website') }}"><i class="fas fa-history fa-flip-horizontal icon-title-prox"></i> Próximos cursos</a>
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