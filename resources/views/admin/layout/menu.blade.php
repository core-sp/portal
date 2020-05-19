@php
  use App\Http\Controllers\ControleController;
@endphp
<!-- Sidebar Menu -->
<nav class="mt-2 mb-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Usuários -->
        @if(ControleController::mostra('UserController', 'index'))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fa fa-users"></i>
                <p>Usuários<i class="right fa fa-angle-left"></i></p>
            </a>
        @endif
        <ul class="nav nav-treeview">
            @if(ControleController::mostra('UserController', 'index'))
            <li class="nav-item">
                <a href="/admin/usuarios" class="nav-link">
                    <i class="nav-icon fa fa-angle-right"></i>
                    <p>Todos os usuários</p>
                </a>
            </li>
            @endif
            @if(ControleController::mostraStatic(['1']))
            <li class="nav-item">
                <a href="/admin/usuarios/criar" class="nav-link">
                    <i class="nav-icon fa fa-angle-right"></i>
                    <p>Novo usuário</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/usuarios/perfis" class="nav-link">
                    <i class="nav-icon fa fa-angle-right"></i>
                    <p>Perfis</p>
                </a>
            </li>
            @endif
        </ul>
        </li>
        @if(ControleController::mostraStatic(['1']))
        <li class="nav-item">
            <a href="/admin/chamados" class="nav-link">
                <i class="nav-icon fas fa-ticket-alt"></i>
                <p>Chamados</p>
            </a>
        </li>
        @endif
        <li class="nav-item">
            <a href="/admin/regionais" class="nav-link">
                <i class="nav-icon fas fa-globe-americas"></i>
                <p>Regionais</p>
            </a>
        </li>
        <!-- Conteúdo -->
        @if(ControleController::mostra('PaginaController', 'index') ||
            ControleController::mostra('NoticiaController', 'index') ||
            ControleController::mostra('CursoController', 'index') ||
            ControleController::mostra('BdoEmpresaController', 'index') ||
            ControleController::mostra('BdoOportunidadeController', 'index') ||
            ControleController::mostra('PostsController', 'index'))
        <li class="nav-header">CONTEÚDO</li>
        @endif
        @if(ControleController::mostra('PaginaController', 'index'))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-file-alt"></i>
                <p>Páginas<i class="right fa fa-angle-left"></i></p>
            </a>
        @endif
            <ul class="nav nav-treeview">
                @if(ControleController::mostra('PaginaController', 'index'))
                <li class="nav-item">
                    <a href="{{ route('paginas.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todas as páginas</p>
                    </a>
                </li>
                @endif
                @if(ControleController::mostra('PaginaController', 'create'))
                <li class="nav-item">
                    <a href="{{ route('paginas.create') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Criar nova página</p>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @if(ControleController::mostra('NoticiaController', 'index'))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon far fa-newspaper"></i>
                <p>Notícias<i class="right fa fa-angle-left"></i></p>
            </a>
        @endif
            <ul class="nav nav-treeview">
                @if(ControleController::mostra('NoticiaController', 'index'))
                <li class="nav-item">
                    <a href="{{ route('noticias.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todas as notícias</p>
                    </a>
                </li>
                @endif
                @if(ControleController::mostra('NoticiaController', 'create'))
                <li class="nav-item">
                    <a href="{{ route('noticias.create') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Criar nova notícia</p>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @if(auth()->user()->isAdmin() || auth()->user()->isEditor())
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-rss"></i>
                <p>Blog<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                @if(auth()->user()->isAdmin() || auth()->user()->isEditor())
                <li class="nav-item">
                    <a href="{{ route('posts.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todos os posts</p>
                    </a>
                </li>
                @endif
                @if(auth()->user()->isAdmin() || auth()->user()->isEditor())
                <li class="nav-item">
                    <a href="{{ route('posts.create') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Criar novo post</p>
                    </a>
                </li>
                @endif
            </ul>
        @endif
        @if(ControleController::mostra('CursoController', 'index'))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-graduation-cap"></i>
                <p>Cursos<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                @if(ControleController::mostra('CursoController', 'index'))
                <li class="nav-item">
                    <a href="/admin/cursos" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todos os cursos</p>
                    </a>
                </li>
                @endif
                @if(ControleController::mostra('CursoController', 'create'))
                <li class="nav-item">
                    <a href="/admin/cursos/criar" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Criar novo curso</p>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif
        @if(ControleController::mostra('BdoEmpresaController', 'index') ||
            ControleController::mostra('BdoOportunidadeController', 'index'))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-briefcase"></i>
                <p>B. de Oportunidades<i class="right fa fa-angle-left"></i></p>
            </a>
        @endif
            <ul class="nav nav-treeview">
                @if(ControleController::mostra('BdoEmpresaController', 'index'))
                <li class="nav-item">
                    <a href="/admin/bdo/empresas" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Empresas</p>
                    </a>
                </li>
                @endif
                @if(ControleController::mostra('BdoOportunidadeController', 'index'))
                <li class="nav-item">
                    <a href="/admin/bdo" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Oportunidades</p>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        <!-- Imagens na Home -->
        @if(ControleController::mostra('HomeImagemController', 'edit'))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon far fa-image"></i>
                <p>Imagens<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="/admin/imagens/bannerprincipal" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Banner principal</p>
                    </a>
                </li>
            </ul>
        </li>
        @endif
        <!-- Atendimento -->
        @if(ControleController::mostra('AgendamentoController', 'index') ||
            ControleController::mostra('AgendamentoBloqueioController', 'index') ||
            ControleController::mostra('RepresentanteEnderecoController', 'index'))
        <li class="nav-header">ATENDIMENTO</li>
        @endif
        @if(ControleController::mostra('AgendamentoController', 'index'))
        <li class="nav-item">
            <a href="/admin/agendamentos" class="nav-link">
                <i class="nav-icon far fa-clock"></i>
                <p>Agendamentos</p>
            </a>
        </li>
        @endif
        @if(ControleController::mostra('AgendamentoBloqueioController', 'index'))
        <li class="nav-item">
            <a href="/admin/agendamentos/bloqueios" class="nav-link">
                <i class="nav-icon fas fa-ban"></i>
                <p>Bloqueios</p>
            </a>
        </li>
        @endif
        @if (ControleController::mostra('RepresentanteEnderecoController', 'index') ||
            ControleController::mostra('RepresentanteController', 'index'))
            <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                    <i class="nav-icon fa fa-users"></i>
                    <p>Representantes<i class="right fa fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                    @if (ControleController::mostra('RepresentanteController', 'index'))
                    <li class="nav-item">
                        <a href="/admin/representantes/buscaGerenti" class="nav-link">
                            <i class="nav-icon fa fa-angle-right"></i>
                            <p>Busca Gerenti</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/representantes" class="nav-link">
                            <i class="nav-icon fa fa-angle-right"></i>
                            <p>Cadastrados</p>
                        </a>
                    </li>
                    @endif
                    @if (ControleController::mostra('RepresentanteEnderecoController', 'index'))
                    <li class="nav-item">
                        <a href="/admin/representante-enderecos" class="nav-link">
                            <i class="nav-icon fa fa-angle-right"></i>
                            <p>Endereços</p>
                        </a>
                    </li>    
                    @endif
                </ul>
            </li>
        @endif
        <!-- Jurídico -->
        @if(ControleController::mostra('LicitacaoController', 'index') ||
            ControleController::mostra('ConcursoController', 'index'))
        <li class="nav-header">JURÍDICO</li>
        @endif
        @if(ControleController::mostra('LicitacaoController', 'index'))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon far fa-file-alt"></i>
                <p>Licitações<i class="right fa fa-angle-left"></i></p>
            </a>
        @endif
            <ul class="nav nav-treeview">
                @if(ControleController::mostra('LicitacaoController', 'index'))
                <li class="nav-item">
                    <a href="{{ route('licitacoes.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todas as licitações</p>
                    </a>
                </li>
                @endif
                @if(ControleController::mostra('LicitacaoController', 'create'))
                <li class="nav-item">
                    <a href="{{ route('licitacoes.create') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Criar nova licitação</p>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @if(ControleController::mostra('ConcursoController', 'index'))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon far fa-edit"></i>
                <p>Concursos<i class="right fa fa-angle-left"></i></p>
            </a>
        @endif
            <ul class="nav nav-treeview">
                @if(ControleController::mostra('ConcursoController', 'index'))
                <li class="nav-item">
                    <a href="/admin/concursos" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todos os concursos</p>
                    </a>
                </li>
                @endif
                @if(ControleController::mostra('ConcursoController', 'create'))
                <li class="nav-item">
                    <a href="/admin/concursos/criar" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Criar novo concurso</p>
                    </a>
                </li>
                @endif
            </ul>
        </li>
    </ul>
</nav>
<!-- /.sidebar-menu -->