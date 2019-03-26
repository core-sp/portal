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

        <title>CORE-SP | Portal</title>
        <link rel="icon" href="{{ asset('img/favicon.ico') }}" type="image/ico" />

        <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
        <link href="{{ asset('/css/custom.css') }}" rel="stylesheet">
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
            </ul>

            <!--
            <form class="form-inline ml-3" action="/search" method="POST" role="search">
              <div class="input-group input-group-sm">
                <input class="form-control form-control-navbar" type="search" placeholder="Pesquisar..." aria-label="Search">
                <div class="input-group-append">
                  <button class="btn btn-navbar" type="submit">
                    <i class="fa fa-search"></i>
                  </button>
                </div>
              </div>
            </form>
            -->

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
            <a href="/" class="brand-link">
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
                  <a href="/admin/info" class="d-block">
                    @if(Auth::check())
                    |&nbsp;&nbsp;{{ Auth::user()->nome }}
                    @endif
                  </a>
                </div>
              </div>

              <!-- Sidebar Menu -->
              <nav class="mt-2 mb-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                  @if(Auth::user()->hasAnyRole(['admin']))
                  <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                      <i class="nav-icon fa fa-users"></i>
                      <p>
                        Usuários
                        <i class="right fa fa-angle-left"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                        <a href="/admin/usuarios" class="nav-link">
                          <i class="nav-icon fa fa-angle-right"></i>
                          <p>Todos os usuários</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="/admin/usuarios/criar" class="nav-link">
                          <i class="nav-icon fa fa-angle-right"></i>
                          <p>Novo usuário</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <li class="nav-item">
                    <a href="/admin/regionais" class="nav-link">
                      <i class="nav-icon fas fa-globe-americas"></i>
                      <p>
                        Regionais
                      </p>
                    </a>
                  </li>
                  @endif
                  @if(Auth::user()->hasAnyRole(['admin', 'editor']))
                  <li class="nav-header">CONTEÚDO</li>
                  <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                      <i class="nav-icon fas fa-file"></i>
                      <p>
                        Páginas
                        <i class="right fa fa-angle-left"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                        <a href="/admin/paginas" class="nav-link">
                          <i class="nav-icon fa fa-angle-right"></i>
                          <p>Todas as páginas</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="/admin/paginas/criar" class="nav-link">
                          <i class="nav-icon fa fa-angle-right"></i>
                          <p>Criar nova página</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="/admin/paginas/categorias" class="nav-link">
                          <i class="nav-icon fa fa-angle-right"></i>
                          <p>Categorias</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                      <i class="nav-icon far fa-newspaper"></i>
                      <p>
                        Notícias
                        <i class="right fa fa-angle-left"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                        <a href="/admin/noticias" class="nav-link">
                          <i class="nav-icon fa fa-angle-right"></i>
                          <p>Todas as notícias</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="/admin/noticias/criar" class="nav-link">
                          <i class="nav-icon fa fa-angle-right"></i>
                          <p>Criar nova notícia</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                      <i class="nav-icon fas fa-graduation-cap"></i>
                      <p>
                        Cursos
                        <i class="right fa fa-angle-left"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                        <a href="/admin/cursos" class="nav-link">
                          <i class="nav-icon fa fa-angle-right"></i>
                          <p>Todos os cursos</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="/admin/cursos/criar" class="nav-link">
                          <i class="nav-icon fa fa-angle-right"></i>
                          <p>Criar novo curso</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  @endif
                  @if(Auth::user()->hasAnyRole(['admin', 'juridico']))
                  <li class="nav-header">JURÍDICO</li>
                  <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                      <i class="nav-icon far fa-file-alt"></i>
                      <p>
                        Licitações
                        <i class="right fa fa-angle-left"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                        <a href="/admin/licitacoes" class="nav-link">
                          <i class="nav-icon fa fa-angle-right"></i>
                          <p>Todas as licitações</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="/admin/licitacoes/criar" class="nav-link">
                          <i class="nav-icon fa fa-angle-right"></i>
                          <p>Criar nova licitação</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                      <i class="nav-icon far fa-edit"></i>
                      <p>
                        Concursos
                        <i class="right fa fa-angle-left"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                        <a href="/admin/concursos" class="nav-link">
                          <i class="nav-icon fa fa-angle-right"></i>
                          <p>Todos os concursos</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="/admin/concursos/criar" class="nav-link">
                          <i class="nav-icon fa fa-angle-right"></i>
                          <p>Criar novo concurso</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  @endif
                </ul>
              </nav>
              <!-- /.sidebar-menu -->
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
              <b>Versão</b> 1.0.0
            </div>
            <strong>Portal CORE-SP &copy; 2019.</strong> Todos direitos reservados.
          </footer>

        </div>
        <!-- ./wrapper -->

      <script type="text/javascript" src="{{ asset('/js/app.js') }}"></script>
      <script type="text/javascript" src="{{ asset('/js/custom.js') }}"></script>
    </body>
</html>
