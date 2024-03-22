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

    @if($curso->tipoParaCertificado() && $curso->encerrado() && Session::has('message'))
        <p class="alert {{ Session::get('class') }}">{!! Session::get('message') !!}</p>
    @endif

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
          @if($curso->tipoParaCertificado() && $curso->encerrado())
            <p class="mt-2"><i class="fas fa-award"></i> <strong>Certificado</strong> - Acesse a área restrita para realizar o download do certificado.</p>
          @endif
        </div>
        @elseif($curso->podeInscreverExterno())
          <div class="center-992">
            <a href="{{ route('cursos.inscricao.website', $curso->idcurso) }}" class="btn-curso-interna">Inscrever-se</a>
          </div>
        @endif

        @if($curso->tipoParaCertificado() && $curso->encerrado() && !auth()->guard('representante')->check())
        <div class="center-992">
          <form method="POST" action="{{ route('cursos.certificado', $curso->idcurso) }}">
            @csrf
            <label for="codigo_certificado"><i class="fas fa-award"></i> Certificado</label>
            <input type="text"
              class="form-control {{ $errors->has('codigo_certificado') || $errors->has('inscrito') ? 'is-invalid' : '' }}"
              name="codigo_certificado"
              id="codigo_certificado"
              minlength="36"
              maxlength="36"
              placeholder="Insira o código gerado na inscrição"
              value="{{ request()->query('certificado') }}"
              required
            />
            @if($errors->has('codigo_certificado') || $errors->has('inscrito'))
            <div class="invalid-feedback">
              {{  $errors->has('codigo_certificado') ? $errors->first('codigo_certificado') : $errors->first('inscrito') }}
            </div>
            @endif
            <button type="submit" class="btn btn-sm btn-primary float-right mt-1">Download</button>
          </form>
        </div>
        @endif

      </div>
      <div class="col-lg-8 mt-2-992">
        <div class="curso-img">
          <img src="{{ asset($curso->img) }}" class="bn-img" />
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