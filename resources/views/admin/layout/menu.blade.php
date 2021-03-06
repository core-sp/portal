@php
    $permissoes = permissoesPorPerfil();
@endphp
<!-- Sidebar Menu -->
<nav class="mt-2 mb-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">


        <!-- Usuários -->
        @if(mostraTitulo($permissoes, ['UserController']))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fa fa-users"></i>
                <p>Usuários<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
            @if(mostraItem($permissoes, 'UserController', 'index'))
            <li class="nav-item">
                <a href="/admin/usuarios" class="nav-link">
                    <i class="nav-icon fa fa-angle-right"></i>
                    <p>Todos os usuários</p>
                </a>
            </li>
            @endif

            @if(auth()->user()->isAdmin())
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
        @endif

        @if(auth()->user()->isAdmin())
        <li class="nav-item">
            <a href="/admin/chamados" class="nav-link">
                <i class="nav-icon fas fa-ticket-alt"></i>
                <p>Chamados</p>
            </a>
        </li>
        @endif
        
        <li class="nav-item">
            <a href="{{ route('regionais.index') }}" class="nav-link">
                <i class="nav-icon fas fa-globe-americas"></i>
                <p>Regionais</p>
            </a>
        </li>


        <!-- Conteúdo -->
        @if(mostraTitulo($permissoes, ['PaginaController', 'NoticiaController', 'PostsController', 'CursoController', 'BdoEmpresaController', 'BdoOportunidadeController', 'HomeImagemController', 'CompromissoController']))
        <li class="nav-header">CONTEÚDO</li>

        @if(mostraTitulo($permissoes, ['PaginaController']))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-file-alt"></i>
                <p>Páginas<i class="right fa fa-angle-left"></i></p>
            </a>
        
            <ul class="nav nav-treeview">
                @if(mostraItem($permissoes, 'PaginaController', 'index'))
                <li class="nav-item">
                    <a href="{{ route('paginas.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todas as páginas</p>
                    </a>
                </li>
                @endif

                @if(mostraItem($permissoes, 'PaginaController', 'create'))
                <li class="nav-item">
                    <a href="{{ route('paginas.create') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Criar nova página</p>
                    </a>
                </li>
                @endif

            </ul>
        </li>
        @endif

        @if(mostraTitulo($permissoes, ['NoticiaController']))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon far fa-newspaper"></i>
                <p>Notícias<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                @if(mostraItem($permissoes, 'NoticiaController', 'index'))
                <li class="nav-item">
                    <a href="{{ route('noticias.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todas as notícias</p>
                    </a>
                </li>
                @endif

                @if(mostraItem($permissoes, 'NoticiaController', 'create'))
                <li class="nav-item">
                    <a href="{{ route('noticias.create') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Criar nova notícia</p>
                    </a>
                </li>
                @endif

            </ul>
        </li>
        @endif

        @if(mostraTitulo($permissoes, ['PostsController']))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-rss"></i>
                <p>Blog<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                @if(mostraItem($permissoes, 'PostsController', 'index'))
                <li class="nav-item">
                    <a href="{{ route('posts.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todos os posts</p>
                    </a>
                </li>
                @endif

                @if(mostraItem($permissoes, 'PostsController', 'create'))
                <li class="nav-item">
                    <a href="{{ route('posts.create') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Criar novo post</p>
                    </a>
                </li>
                @endif

            </ul>
        @endif

        @if(mostraTitulo($permissoes, ['CursoController']))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-graduation-cap"></i>
                <p>Cursos<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                @if(mostraItem($permissoes, 'CursoController', 'index'))
                <li class="nav-item">
                    <a href="{{ route('cursos.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todos os cursos</p>
                    </a>
                </li>
                @endif

                @if(mostraItem($permissoes, 'CursoController', 'create'))
                <li class="nav-item">
                    <a href="{{ route('cursos.create') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Criar novo curso</p>
                    </a>
                </li>
                @endif

            </ul>
        </li>
        @endif

        @if(mostraTitulo($permissoes, ['BdoEmpresaController', 'BdoOportunidadeController']))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-briefcase"></i>
                <p>B. de Oportunidades<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                @if(mostraItem($permissoes, 'BdoEmpresaController', 'index'))
                <li class="nav-item">
                    <a href="/admin/bdo/empresas" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Empresas</p>
                    </a>
                </li>
                @endif

                @if(mostraItem($permissoes, 'BdoOportunidadeController', 'index'))
                <li class="nav-item">
                    <a href="/admin/bdo" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Oportunidades</p>
                    </a>
                </li>
                @endif

            </ul>
        </li>
        @endif

      @if(mostraTitulo($permissoes, ['CompromissoController']))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-calendar-alt"></i>
                <p>Compromissos<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                @if(mostraItem($permissoes, 'CompromissoController', 'index'))
                <li class="nav-item">
                    <a href="{{ route('compromisso.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todos os compromissos</p>
                    </a>
                </li>
                @endif

                @if(mostraItem($permissoes, 'CompromissoController', 'create'))
                <li class="nav-item">
                    <a href="{{ route('compromisso.create') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Criar novo compromisso</p>
                    </a>
                </li>
                @endif

            </ul>
        </li>
        @endif

        @if(mostraTitulo($permissoes, ['HomeImagemController']))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon far fa-image"></i>
                <p>Imagens<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                @if(mostraItem($permissoes, 'HomeImagemController', 'edit'))
                <li class="nav-item">
                    <a href="/admin/imagens/bannerprincipal" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Banner principal</p>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif
        @endif


        <!-- Atendimento -->
        @if(mostraTitulo($permissoes, ['AgendamentoController', 'AgendamentoBloqueioController', 'RepresentanteEnderecoController', 'RepresentanteController']))
        <li class="nav-header">ATENDIMENTO</li>
        
        @if(mostraTitulo($permissoes, ['AgendamentoController', 'AgendamentoBloqueioController']))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon far fa-clock"></i>
                <p>Agendamentos<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                @if(mostraItem($permissoes, 'AgendamentoController', 'index'))
                <li class="nav-item">
                    <a href="/admin/agendamentos" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todos os agendamentos</p>
                    </a>
                </li>
                @endif

                @if(mostraItem($permissoes, 'AgendamentoBloqueioController', 'index'))
                <li class="nav-item">
                    <a href="/admin/agendamentos/bloqueios" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Bloqueios</p>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif

        @if (mostraTitulo($permissoes, ['RepresentanteEnderecoController', 'RepresentanteController']))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fa fa-users"></i>
                <p>Representantes<i class="right fa fa-angle-left"></i></p>
            </a>

            <ul class="nav nav-treeview">
                @if (mostraItem($permissoes, 'RepresentanteController', 'index'))
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

                @if (mostraItem($permissoes, 'RepresentanteEnderecoController', 'index'))
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
        @endif


        <!-- Jurídico -->
        @if(mostraTitulo($permissoes, ['LicitacaoController', 'ConcursoController']))
        <li class="nav-header">JURÍDICO</li>

        @if(mostraTitulo($permissoes, ['LicitacaoController']))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon far fa-file-alt"></i>
                <p>Licitações<i class="right fa fa-angle-left"></i></p>
            </a>

            <ul class="nav nav-treeview">
                @if(mostraItem($permissoes, 'LicitacaoController', 'index'))
                <li class="nav-item">
                    <a href="{{ route('licitacoes.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todas as licitações</p>
                    </a>
                </li>
                @endif

                @if(mostraItem($permissoes, 'LicitacaoController', 'create'))
                <li class="nav-item">
                    <a href="{{ route('licitacoes.create') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Criar nova licitação</p>
                    </a>
                </li>
                @endif

            </ul>
        </li>
        @endif

        @if(mostraTitulo($permissoes, ['ConcursoController']))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon far fa-edit"></i>
                <p>Concursos<i class="right fa fa-angle-left"></i></p>
            </a>
        
            <ul class="nav nav-treeview">
                @if(mostraItem($permissoes, 'ConcursoController', 'index'))
                <li class="nav-item">
                    <a href="{{ route('concursos.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todos os concursos</p>
                    </a>
                </li>
                @endif

                @if(mostraItem($permissoes, 'ConcursoController', 'create'))
                <li class="nav-item">
                    <a href="{{ route('concursos.create') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Criar novo concurso</p>
                    </a>
                </li>
                @endif

            </ul>
        </li>
        @endif
        @endif
    

        <!-- Fiscalização -->
        @if(mostraTitulo($permissoes, ['FiscalizacaoController']))
        <li class="nav-header">FISCALIZAÇÃO</li>
        
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon far fa-file-alt"></i>
                <p>Dados de Fiscalização<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                @if(mostraItem($permissoes, 'FiscalizacaoController', 'index'))
                <li class="nav-item">
                    <a href="{{ route('fiscalizacao.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todos os anos</p>
                    </a>
                    </li>
                @endif
                @if(mostraItem($permissoes, 'FiscalizacaoController', 'create'))
                <li class="nav-item">
                    <a href="{{ route('fiscalizacao.createperiodo') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Criar Ano</p>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif

    </ul>
</nav>
<!-- /.sidebar-menu -->