@extends('site.layout.app', ['title' => 'Área Restrita'])

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/institucional.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Área Restrita
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-noticias">
  <div class="container">
    <div class="row nomargin" id="conteudo-principal">
      <div class="flex-one pr-4 align-self-center">
        <h2 class="stronger">Logado como Usuário Externo</h2>
      </div>
      <div class="align-self-center">
        <a href="/" class="btn-voltar">Voltar</a>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row">
      <div class="col-xl-3 pb-15-992">
        <div class="menu-representante">
          <div class="p-3 border-one-mr azul-escuro-bg">
            <h6 class="branco"><strong>{{ auth()->guard('user_externo')->user()->nome }}</strong></h6>
            <i class="fas fa-bars show-992" id="bars-representante"></i>
          </div>
          <div id="mobile-menu-representante" class="hide-992">

            <div class="mr-item bt-unset {{ Route::currentRouteName() === 'externo.dashboard' ? 'mr-item-selected' : '' }}">
              <a href="{{ route('externo.dashboard') }}" onclick="showLoading()">
                <h6 class="brancar"><i class="fas fa-home"></i>&nbsp;&nbsp;Home</h6>
              </a>
            </div>

            <div class="mr-item bt-unset {{ (Route::currentRouteName() === 'externo.editar.view') || 
              (Route::currentRouteName() === 'externo.editar.senha.view') ? 'mr-item-selected' : '' }}">
              <a href="{{ route('externo.editar.view') }}" onclick="showLoading()">
                <h6 class="brancar"><i class="fas fa-edit"></i>&nbsp;&nbsp;Alterar dados</h6>
              </a>
            </div>

            <div class="mr-item bt-unset {{ (Route::currentRouteName() === 'externo.preregistro.view') || 
              (Route::currentRouteName() === 'externo.inserir.preregistro.view') ? 'mr-item-selected' : '' }}">
              <a href="{{ route('externo.preregistro.view') }}" onclick="showLoading()">
                <h6 class="brancar"><i class="fas fa-file-alt"></i>&nbsp;&nbsp;Solicitar Registro</h6>
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
          @yield('content-user-externo')
        </div>
      </div>
    </div>
  </div>
</section>

@endsection