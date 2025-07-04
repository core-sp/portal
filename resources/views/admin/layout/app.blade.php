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
        <link type="text/css" href="{{ asset('/css/custom.css?'.time()) }}" rel="stylesheet">
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
              <li class="nav-item d-none d-sm-inline-block">
                <a href="https://app.jivosite.com/" class="nav-link" target="_blank">Chat</a>
              </li>
              @if(auth()->user()->isAdmin())
              <li class="nav-item d-none d-sm-inline-block">
                <a href="/horizon" class="nav-link" target="_blank">Horizon</a>
              </li>
              @endif
            </ul>

            <ul class="navbar-nav ml-auto">
              @if(Auth::user())
              <a href="/" class="btn btn-sm btn-success" target="_blank">Site</a>
              <a href="#" class="btn btn-sm btn-default ml-1" id="logout-interno">Logout</a>
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
              <div class="user-panel mt-3 pb-3 mb-3 text-center">
                <div class="d-flex">
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
                <span class="sessao-txt font-italic w-auto">
                  <small>
                    <b>Último acesso: </b>
                    {{ formataData(Auth::user()->ultimoAcesso()) }}
                  </small>
                </span>
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
              <b>Versão</b> 3.1.4
            </div>
            <strong>Portal CORE-SP &copy; {{ date('Y') }}.</strong> Todos direitos reservados.
          </footer>

        </div>
        <!-- ./wrapper -->

      @component('components.modal-geral')
      @endcomponent

      @if(config('app.env') == 'local')
      <script type="text/javascript" src="{{ asset('/js/tinymce/tinymce.min.js') }}"></script>
      @else
      <script referrerpolicy="origin" src="{{ 'https://cdn.tiny.cloud/1/' . env('TINY_API_KEY') . '/tinymce/5/tinymce.min.js' }}"></script>
      @endif

      <script type="text/javascript" src="{{ asset('/js/app.js') }}"></script>
      <!-- <script type="text/javascript" src="{{-- asset('/js/jquery-ui.min.js') --}}"></script> -->
      <!-- <script type="text/javascript" src="{{-- asset('/js/jquery.mask.js') --}}"></script> -->
      
      <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha512-uto9mlQzrs59VwILcLiRYeLKPPbS/bT71da/OEBYEwcdNUk8jYIy+D176RYoop1Da+f9mvkYrmj5MCLZWEtQuA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js" integrity="sha512-gDWSGGWzTc0mD1P0NwDUsZsSrSyLsC8Mv0I14JtDFJXu2Pgo3ik5Uk1+BqxosbEJU2gDUD6W5DCpn7l29Ah9Ag==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>      
      <script type="text/javascript" src="{{ asset('/js/pre-init.js?'.hashScriptJs()) }}" data-modulo-versao="{{ 'v=' . versaoScriptJs() }}" id="pre-init"></script>
      <script type="text/javascript" src="{{ asset('/js/interno/custom.js?'.hashScriptJs()) }}"></script>
    </body>
</html>
