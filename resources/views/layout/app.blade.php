@php
use \App\Http\Controllers\Helper;
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>CORE-SP | {{ $title }}</title>

        <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
        <link href="{{ asset('/css/site.css') }}" rel="stylesheet">
    </head>
    <body>

      @section('header')
      <header>
        <div class="container-fluid">
          <div class="row">
            <div class="linha-verde w-100"></div>
          </div>
        </div>
        <div class="container">
          <div class="row mb-3 mt-3">
            <div class="col-sm-3 text-left">
                <img src="{{ asset('img/logo-horizontal.png') }}" alt="CORE-SP" id="logo-header" />
            </div>
            <div class="col-sm-6 align-self-center">
              <div class="w-75 m-auto text-center">
                <div class="acessibilidade mb-2">
                  <button type="button" class="btn btn-sm btn-light" id="btn-contrast">
                    <i class="fas fa-adjust"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-light">
                    <i class="fas fa-map-marker-alt"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-light">
                    <i class="fas fa-wheelchair"></i>
                  </button>
                </div>
                <form class="input-group input-group-sm"
                  method="GET"
                  role="form"
                  action ="/busca">
                  <input type="text"
                    name="q"
                    class="form-control float-right"
                    placeholder="Digite o que você procura" />
                  <div class="input-group-append">
                    <button type="submit" class="btn btn-default">
                      <i class="fas fa-search"></i>
                    </button>
                  </div>
                </form>
              </div>
            </div>
            <div class="col-sm-3 text-right align-self-center">
              <div class="mb-2">
                <button class="btn-atendimento d-inline">Atendimento: <strong>(11) 3243-5500</strong></button>
              </div>
              <div>
                <small class="cinza-escuro">Acompanhe-nos:&nbsp;</small>
                <button type="button" class="btn btn-sm btn-light">
                  <i class="fab fa-youtube"></i>
                </button>
                <button type="button" class="btn btn-sm btn-light">
                  <i class="fab fa-whatsapp"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="container-fluid">
          <div class="row">
            <div class="linha-cinza"></div>
          </div>
        </div>
        <div class="container">
          <div class="row">
              <nav class="menu-principal m-auto">
                <ul class="nomargin nopadding">
                  <li class="nav-item">
                    <a href="/" class="nav-link">Home</a>
                  </li>
                  <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-toggle="dropdown">CORE-SP</a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                      <a href="/legislacao" class="dropdown-item">Legislação</a>
                    </div>
                  </li>
                  <li class="nav-item">
                    <a href="http://core-sp.implanta.net.br/portaltransparencia/#publico/inicio" class="nav-link" target="_blank">Transparência</a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">Licitações</a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">Seccionais</a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">E-OUV</a>
                  </li>
                </ul>
              </nav>              
          </div>
        </div>
      </header>
      @show

      @yield('content')

      <script type="text/javascript" src="{{ asset('/js/app.js') }}"></script>
      <script type="text/javascript" src="{{ asset('/js/site.js') }}"></script>
    </body>
</html>
