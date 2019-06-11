@php
  use \App\Http\Controllers\Helper;
  use App\Http\Controllers\ControleController;
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>CORE-SP | Portal</title>
        <link rel="icon" href="{{ asset('img/favicon.ico') }}" type="image/ico" />

        <link type="text/css" href="{{ asset('/css/app.css') }}" rel="stylesheet">
        <link type="text/css" href="{{ asset('/css/custom.css') }}" rel="stylesheet">
    </head>
    <body class="hold-transition sidebar-mini">

        <div class="wrapper">
          <!-- Navbar -->
          <nav class="main-header navbar navbar-expand bg-white navbar-light border-bottom">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
              <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#"><i class="fa fa-bars"></i></a>
              </li>
              <li class="nav-item d-none d-sm-inline-block">
                <a href="/admin" class="nav-link">Home</a>
              </li>
              <li class="nav-item d-none d-sm-inline-block">
                <a href="/admin/perfil" class="nav-link">Perfil</a>
              </li>
              <li class="nav-item d-none d-sm-inline-block">
                <a href="/admin/chamados/criar" class="nav-link">Contate o CTI</a>
              </li>
              @if(ControleController::mostraStatic(['1']))              
              <li class="nav-item d-none d-sm-inline-block">
                <a href="/admin/docs" class="nav-link" target="_blank">Documentação</a>
              </li>
              <li class="nav-item d-none d-sm-inline-block">
                <a href="/horizon" class="nav-link" target="_blank">Horizon</a>
              </li>
              @endif
            </ul>

            <ul class="navbar-nav ml-auto">
              @if(Auth::user())
              <a href="/admin/logout" class="btn btn-sm btn-default">Logout</a>
              @endif
            </ul>
          </nav>
          <!-- /.navbar -->

          <!-- Main Sidebar Container -->
          <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="/admin" class="brand-link">
              <img src="{{ asset('img/brasao.png') }}" alt="CORE-SP Logo" class="brand-image img-circle elevation-3">
              <span class="brand-text font-weight-light"><strong>CORE-</strong>SP</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
              <!-- Sidebar user (optional) -->
              <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image align-self-center">
                  <a href="#">
                    <strong>C-</strong>SP
                  </a>
                </div>
                <div class="info">
                  <a href="/admin/perfil" class="d-block">
                    @if(Auth::check())
                    |&nbsp;&nbsp;{{ Auth::user()->nome }}
                    @endif
                  </a>
                </div>
              </div>
               
              @include('admin.layout.menu')
              
            </div>
            <!-- /.sidebar -->
          </aside>

          <!-- Content Wrapper. Contains page content -->
          <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            @yield('content')
          </div>
          <!-- /.content-wrapper -->

          <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
              <b>Versão</b> 1.0.9
            </div>
            <strong>Portal CORE-SP &copy; 2019.</strong> Todos direitos reservados.
          </footer>

        </div>
        <!-- ./wrapper -->

      <script type="text/javascript" src="{{ asset('/js/app.js') }}"></script>
      <script type="text/javascript" src="{{ asset('/js/jquery-ui.min.js') }}"></script>
      <script type="text/javascript" src="{{ asset('/js/jquery.mask.js') }}"></script>
      <script type="text/javascript" src="{{ asset('/js/custom.js') }}"></script>
    </body>
</html>
