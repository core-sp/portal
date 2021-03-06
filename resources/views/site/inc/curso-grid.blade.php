@php
use \App\Http\Controllers\Helper;
use \App\Http\Controllers\CursoInscritoController;
use \App\Http\Controllers\CursoSiteController;
@endphp

<div class="col-lg-4 col-sm-6 mb-3">
  <div class="h-100 d-flex flex-column">
    <a href="{{ route('cursos.show', $curso->idcurso) }}">
      <div class="curso-grid">
        <img src="{{ asset(Helper::imgToThumb($curso->img)) }}" class="bn-img" />
        <div class="curso-grid-txt">
          <h6 class="light cinza-claro">{{ $curso->regional->regional }} - {{ Helper::onlyDate($curso->datarealizacao) }}</h6>
          <h5 class="branco mt-1">{{ $curso->tipo }} - {{ $curso->tema }}</h5>
        </div>
      </div>
    </a>
    <div class="curso-grid-content text-center">
      <p>{!! $curso->resumo !!}</p>
      @if(CursoSiteController::checkCurso($curso->idcurso))
        @if(CursoInscritoController::permiteInscricao($curso->idcurso))
          <a href="{{ route('cursos.inscricao.website', $curso->idcurso) }}" class="btn-curso-grid mt-3">Inscrever-se</a>
        @else
          <button class="btn-esgotado mt-3">Vagas esgotadas</button>
        @endif
      @else
        @if(!empty(CursoSiteController::getNoticia($curso->idcurso)))
        <a href="noticia/{{ CursoSiteController::getNoticia($curso->idcurso) }}" class="btn-como-foi mt-3">Veja como foi</a>
        @endif
      @endif
    </div>
  </div>
</div>