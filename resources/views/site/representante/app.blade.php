@extends('site.layout.app', ['title' => 'Área do Representante'])

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/institucional.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Área do Representante
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-noticias">
  <div class="container">
    <div class="row nomargin" id="conteudo-principal">
      <div class="flex-one pr-4 align-self-center">
        <h2 class="stronger">Logado como Representante Comercial</h2>
      </div>
      <div class="align-self-center">
        <a href="/" class="btn-voltar">Voltar</a>
      </div>
    </div>
    <div class="linha-lg"></div>
    <!-- Área do aviso -->
    @if(isset($aviso))
      @component('components.aviso-representante', 
        [
          'cor_fundo_titulo' => $aviso->cor_fundo_titulo,
          'titulo' => $aviso->titulo, 
          'conteudo' => $aviso->conteudo
        ]
      )
      @endcomponent
    @endif
    <!-- Fim da Área do aviso -->
    <div class="row">
      <div class="col-xl-3 pb-15-992">
        <div class="menu-representante">
          <div class="p-3 border-one-mr azul-escuro-bg">
            <h6 class="branco" data-clarity-mask="True"><strong>{{ Auth::guard('representante')->user()->nome }}</strong></h6>
            <i class="fas fa-bars show-992" id="bars-representante"></i>
          </div>
          <div id="mobile-menu-representante" class="hide-992">
            <div class="mr-item bt-unset {{ Route::currentRouteName() === 'representante.dashboard' ? 'mr-item-selected' : '' }}">
              <a href="{{ route('representante.dashboard') }}" onclick="showLoading()">
                <h6 class="brancar"><i class="fas fa-home"></i>&nbsp;&nbsp;Home</h6>
              </a>
            </div>
            <div class="mr-item bt-unset {{ Route::currentRouteName() === 'representante.dados-gerais' ? 'mr-item-selected' : '' }}">
              <a href="{{ route('representante.dados-gerais') }}" onclick="showLoading()">
                <h6 class="brancar"><i class="fas fa-table"></i>&nbsp;&nbsp;Dados gerais</h6>
              </a>
            </div>
            <div class="mr-item bt-unset {{ Route::currentRouteName() === 'representante.contatos.view' ? 'mr-item-selected' : '' }}">
              <a href="{{ route('representante.contatos.view') }}" onclick="showLoading()">
                <h6 class="brancar"><i class="fas fa-phone"></i>&nbsp;&nbsp;Contatos</h6>
              </a>
            </div>
            <div class="mr-item bt-unset {{ Route::currentRouteName() === 'representante.enderecos.view' ? 'mr-item-selected' : '' }}">
              <a href="{{ route('representante.enderecos.view') }}" onclick="showLoading()">
                <h6 class="brancar"><i class="fas fa-map-marker-alt"></i>&nbsp;&nbsp;End. de Correspondência</h6>
              </a>
            </div>
            <div class="mr-item bt-unset {{ Route::currentRouteName() === 'representante.lista-cobrancas' ? 'mr-item-selected' : '' }}">
              <a href="{{ route('representante.lista-cobrancas') }}" onclick="showLoading()">
                <h6 class="brancar"><i class="fas fa-file-invoice"></i>&nbsp;&nbsp;Situação Financeira</h6>
              </a>
            </div>

            <div class="mr-item bt-unset {{ (Route::currentRouteName() === 'representante.emitirCertidaoView')  ? 'mr-item-selected' : '' }}">
              <a href="{{ route('representante.emitirCertidaoView') }}" onclick="showLoading()">
                <h6 class="brancar"><i class="fas fa-file-invoice"></i>&nbsp;&nbsp;Emitir Certidão</h6>
              </a>
            </div>

            <div class="mr-item bt-unset {{ (Route::currentRouteName() === 'representante.bdo')  ? 'mr-item-selected' : '' }}">  
              <a href="{{ route('representante.bdo') }}" onclick="showLoading()">
                <h6 class="brancar"><i class="fas fa-briefcase"></i>&nbsp;&nbsp;Oportunidades&nbsp;&nbsp;&nbsp;</h6>
              </a>
            </div>

            {{-- SIMULADOR_REFIS --}}
            {{-- <div class="mr-item bt-unset {{ (Route::currentRouteName() === 'representante.simuladorRefis')  ? 'mr-item-selected' : '' }}">
              <a href="{{ route('representante.simuladorRefis') }}" onclick="showLoading()">
                <h6 class="brancar"><i class="fas fa-file-invoice"></i>&nbsp;&nbsp;Simulador Refis</h6>
              </a>
            </div> --}}

            <div class="mr-item bt-unset {{ (Route::currentRouteName() === 'representante.solicitarCedulaView')  ? 'mr-item-selected' : '' }}">
              <a href="{{ route('representante.solicitarCedulaView') }}" onclick="showLoading()">
                <h6 class="brancar">
                  <i class="fas fa-id-card"></i>&nbsp;&nbsp;Solicitação de Cédula&nbsp;&nbsp;&nbsp;
                </h6>
              </a>
            </div>

            <div class="mr-item bt-unset {{ Route::is('representante.agendar.*') ? 'mr-item-selected' : '' }}">  
              <a href="{{ route('representante.agendar.inserir.view') }}" onclick="showLoading()">
                <h6 class="brancar">
                  <i class="fas fa-business-time"></i>&nbsp;&nbsp;Agendar Salas&nbsp;&nbsp;&nbsp;
                  <span class="badge badge-warning">NOVO <span class="spinner-grow spinner-grow-sm align-middle"></span>
                  </span>
                </h6>
              </a>
            </div>

            <div class="mr-item bt-unset {{ Route::is('representante.cursos')  ? 'mr-item-selected' : '' }}">
              <a href="{{ route('representante.cursos') }}" onclick="showLoading()">
                <h6 class="brancar">
                  <i class="nav-icon fas fa-graduation-cap"></i>&nbsp;&nbsp;Cursos&nbsp;&nbsp;&nbsp;
                </h6>
              </a>
            </div>

          </div>
        </div>
      </div>
      <div class="col-xl-9">
        <div id="loading" class="row nomargin">
          <div class="representante-content w-100">
            <h2><i class="fas fa-cog fa-spin"></i></h2>
          </div>
        </div>
        <div class="row nomargin" id="rc-main">
          @yield('content-representante')
        </div>
      </div>
    </div>
  </div>
</section>

@endsection