@extends('site.layout.app', ['title' => 'Consulta Pública'])

@section('description')
  <meta name="description" content="A Consulta Pública é uma solução informatizada que permite verificar a situação do Representante Comercial junto ao Conselho." />
@endsection

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/banner-consulta.jpg') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Consulta Pública
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-busca">
  <div class="container">
    <div class="row" id="conteudo-principal">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-4 align-self-center">
            <h2 class="stronger">Representante já pode consultar, com mais facilidade, sua situação junto ao Conselho!</h2>
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mt-2" id="conteudo-principal">
      <div class="col-lg-8 consulta-linha">
        <div class="row nomargin mb-4">
          <p class="mb-2 light">A consulta pública, é uma solução informatizada que permite verificar a situação do Representante Comercial junto ao Conselho.</p>
          <p class="light">Um recurso simples, ágil e moderno que visa contribuir para uma melhor administração do tempo de Representantes Comerciais e de seus contadores.</p>
          <p class="light mt-2">Ao consultar, os seus dados serão apenas utilizados para consulta, não sendo utilizados para outros fins além do serviço solicitado. Para mais informações, verifique a nossa <a href="/politica-de-privacidade"  target="_blank"><strong><u>Política de Privacidade</u></strong></a>.
        </p>
        </div>
        {{--
        <div class="row nomargin consulta">
          <form method="post" class="d-flex w-100">
            @csrf
            <div class="flex-one">
              <label for="cpfCnpj">Insira o CPF/CNPJ abaixo:</label>
              <input
                type="text"
                id="cpfCnpj"
                name="cpfCnpj"
                class="form-control cpfOuCnpj {{ $errors->has('cpfCnpj') ? 'is-invalid' : '' }}"
                placeholder="CPF ou CNPJ"
              />
              @if($errors->has('cpfCnpj'))
                <div class="invalid-feedback">
                  {{ $errors->first('cpfCnpj') }}
                </div>
              @endif
            </div>
            <div class="ml-2 align-self-end">
              <button
                type="submit"
                class="btn btn-primary"
                onClick="gtag('event', 'consultar', {
                  'event_category': 'situação',
                  'event_label': 'Consulta de Situação'
                });"
              >
                Consultar {{ Request::input('cpfCnpj') ? 'novamente' : '' }}
              </button>
            </div>
          </form>
        </div>
        @if(isset($resultado) && count($resultado) === 1)
          <div class="mt-3">
            <div>
              <p class="light"><i>Resultados para a busca do CPF/CNPJ:</i> <strong data-clarity-mask="True">{{ Request::input('cpfCnpj') }}</strong></p>
            </div>
            <hr class="mb-4">
            @if(utf8_encode($resultado[0]['SITUACAO']) === 'Não encontrado')
              <p><strong>Nenhum Representante Comercial encontrado com o CPF/CNPJ fornecido!</strong></p>
            @else
              <div class="consulta-box">
                <div class="consulta-avatar d-flex">
                  <div>
                    <img src="{{ strlen(Request::input('cpfCnpj')) === 11 ? asset('img/icon-rc.png') : asset('img/icon-empresa.png') }}" alt="Avatar Representante Comercial" />
                  </div>
                  <div class="flex-one align-self-center ml-3" data-clarity-mask="True">
                    <h5>{{ utf8_encode($resultado[0]['NOME']) }}</h5>
                    <p><strong>Registro:</strong> {{ substr_replace($resultado[0]['REGISTRONUM'], '/', -4, 0) }}</p>
                    <p><strong>{{ strlen(Request::input('cpfCnpj')) === 14 ? 'CNPJ:' : 'CPF:' }}</strong> {{ Request::input('cpfCnpj') }}</p>
                    <p class="mt-2"><strong>Situação:</strong> {!! badgeConsulta($resultado[0]['SITUACAO']) !!}</p>
                  </div>
                </div>
              </div>
            @endif
          </div>
        @endif
        --}}

        <div class="embed-responsive embed-responsive-4by3">
          <iframe class="embed-responsive-item" src="https://consultarep.confere.org.br"></iframe>
        </div>

        <hr class="mt-4">
        <div class="row nomargin mt-4">
          <div class="alert alert-warning consulta-alert" role="alert">
            <h6>IMPORTANTE:</h6>
            <p class="mt-1 mb-1">O teor desta consulta é meramente informativo, não valendo como certidão.</p>
            <p>Caso seja constatada qualquer divergência de dados, solicitamos a gentileza de entrar em contato conosco através do email atendimento.sede@core-sp.org.br ou pelo telefone (11) 3243-5500.</p>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        @include('site.inc.content-sidebar')
      </div>
    </div>
  </div>
</section>

@endsection