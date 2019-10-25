@extends('site.layout.app', ['title' => 'Área do Representante'])

@section('content')

<section id="pagina-cabecalho" class="mt-1">
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
    <div class="row">
      <div class="col-sm-3">
        <div class="menu-representante">
          <div class="p-3 border-one-mr azul-escuro-bg">
            <h6 class="branco"><strong>{{ Auth::guard('representante')->user()->nome }}</strong></h6>
          </div>
          <div class="mr-item bt-unset {{ Route::currentRouteName() === 'representante.dashboard' ? 'mr-item-selected' : '' }}">
            <a href="{{ route('representante.dashboard') }}">
              <h6 class="brancar"><i class="fas fa-home"></i>&nbsp;&nbsp;Home</h6>
            </a>
          </div>
          <div class="mr-item bt-unset {{ Route::currentRouteName() === 'representante.dados-gerais' ? 'mr-item-selected' : '' }}">
            <a href="{{ route('representante.dados-gerais') }}">
              <h6 class="brancar"><i class="fas fa-table"></i>&nbsp;&nbsp;Dados gerais</h6>
            </a>
          </div>
          <div class="mr-item bt-unset {{ Route::currentRouteName() === 'representante.contatos.view' ? 'mr-item-selected' : '' }}">
            <a href="{{ route('representante.contatos.view') }}">
              <h6 class="brancar"><i class="fas fa-phone"></i>&nbsp;&nbsp;Contatos</h6>
            </a>
          </div>
          <div class="mr-item bt-unset {{ Route::currentRouteName() === 'representante.enderecos.view' ? 'mr-item-selected' : '' }}">
            <a href="{{ route('representante.enderecos.view') }}">
              <h6 class="brancar"><i class="fas fa-map-marker-alt"></i>&nbsp;&nbsp;Endereços</h6>
            </a>
          </div>
        </div>
      </div>
      <div class="col-sm-9">
        <div class="row nomargin">
          @yield('content-representante')
        </div>
      </div>
    </div>
  </div>
</section>

@endsection