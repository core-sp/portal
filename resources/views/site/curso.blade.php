@extends('site.layout.app', ['title' => 'Cursos'])

@section('description')
  <meta name="description" content="{!! retornaDescription($curso->descricao) !!}" />
@endsection

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/cursos.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          @if(isset($curso))
          {{ $curso->tipo }} - {{ $curso->tema }}
          @else
          erro
          @endif
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-licitacao">
  <div class="container">
    @if(isset($curso))
    <div class="row" id="conteudo-principal">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-3 align-self-center">
            <h2 class="stronger">{{ $curso->tipo }} - {{ $curso->tema }} ({{ $curso->idcurso }})</h2>
          </div>
          <div class="align-self-center">
            <a href="{{ route('cursos.index.website') }}" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mt-2">
      <div class="col-lg-4 edital-info">
        <table class="table table-bordered mb-4">
          <tbody>
            <tr>
              <td class="quarenta"><h6>Status</h6></td>
              <td><h6 class="light">
                {!! $curso->btnSituacao() !!}
              </h6></td>
            </tr>
            <tr>
              <td><h6>Onde</h6></td>
              <td><h6 class="light">{{ $curso->regional->regional }}</h6></td>
            </tr>
            <tr>
              <td><h6>Início</h6></td>
              <td><h6 class="light">{{ onlyDate($curso->datarealizacao) }}</h6></td>
            </tr>
            <tr>
              <td><h6>Término</h6></td>
              <td><h6 class="light">{{ onlyDate($curso->datatermino) }}</h6></td>
            </tr>
            <tr>
              <td><h6>Horário</h6></td>
              <td><h6 class="light">
                @if(onlyDate($curso->datarealizacao) == onlyDate($curso->datatermino))
                  Das {{ onlyHour($curso->datarealizacao) }} às {{ onlyHour($curso->datatermino) }}
                @else
                  A partir das {{ onlyHour($curso->datarealizacao) }}
                @endif
              </h6></td>
            </tr>
            <tr>
              <td><h6>Endereço</h6></td>
              <td><h6 class="light">{{ isset($curso->endereco) ? $curso->endereco : 'Evento online' }}</h6></td>
            </tr>
            <tr>
              <td><h6>Nº de vagas</h6></td>
              <td><h6 class="light">{{ $curso->nrvagas }}</h6></td>
            </tr>
            <tr>
              <td><h6>Inscrição</h6></td>
              <td><h6 class="light">{{ $curso->textoAcesso() }}</h6></td>
            </tr>
          </tbody>
        </table>
        @if(auth()->guard('representante')->check() && $curso->representanteInscrito(auth()->guard('representante')->user()->cpf_cnpj))
        <div class="center-992">
          <span class="{{ $curso::TEXTO_BTN_INSCRITO }} btn-curso-inscrito">Inscrição realizada</span>
        </div>
        @elseif($curso->podeInscreverExterno())
          <div class="center-992">
            <a href="{{ route('cursos.inscricao.website', $curso->idcurso) }}" class="btn-curso-interna">Inscrever-se</a>
          </div>
        @endif
      </div>
      <div class="col-lg-8 mt-2-992">
        <div class="curso-img">
          <img class="lazy-loaded-image lazy bn-img" src="{{ isset($curso->img) ? $curso->imgBlur() : asset('img/small-news-generica-thumb.png') }}" data-src="{{ isset($curso->img) ? asset($curso->img) : asset('img/news-generica-thumb.png') }}" />
        </div>
        <div class="edital-download mt-3 conteudo-txt-mini">
          <h4 class="stronger">Descrição</h4>
          <div class="linha-lg"></div>
          {!! $curso->descricao !!}
        </div>
      </div>
    </div>
    @else
      @include('site.inc.content-error')
    @endif
  </div>
</section>

@endsection