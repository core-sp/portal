<div class="col-lg-4 col-sm-6 mb-3">
  <div class="h-100 d-flex flex-column">
    <a href="{{ route('cursos.show', $curso->idcurso) }}">
      <div class="curso-grid">
        <img src="{{ asset(imgToThumb($curso->img)) }}" class="bn-img" />
        <div class="curso-grid-txt">
          <h6 class="light cinza-claro">{{ $curso->regional->regional }} - {{ onlyDate($curso->datarealizacao) }}</h6>
          <h5 class="branco mt-1">{{ $curso->tipo }} - {{ $curso->tema }}</h5>
        </div>
      </div>
    </a>
    <div class="curso-grid-content text-center">
      <p>{!! $curso->resumo !!}</p>
      @if(!$curso->encerrado())
        @if(auth()->guard('representante')->check() && $curso->representanteInscrito(auth()->guard('representante')->user()->cpf_cnpj))
        <div class="center-992">
          <span class="{{ $curso::TEXTO_BTN_INSCRITO }} btn-inscrito-grid"><b>Inscrição realizada</b></span>
        </div>
        @elseif($curso->podeInscreverExterno())
          <a href="{{ route('cursos.inscricao.website', $curso->idcurso) }}" class="btn-curso-grid mt-3">Inscrever-se</a>
        @elseif(!$curso->aguardandoAbrirInscricao())
          <button class="btn-esgotado mt-3">Vagas esgotadas</button>
        @else
          <button class="btn-divulgacao mt-3">Divulgação</button>
        @endif
      @elseif($curso->possuiNoticia())
        <a href="{{ route('noticias.show', $curso->getNoticia()) }}" class="btn-como-foi mt-3">Veja como foi</a>
      @endif
    </div>
  </div>
</div>