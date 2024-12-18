<!-- Sidebar Menu -->
<nav class="mt-2 mb-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

        <!-- Usuários -->
        @if(auth()->user()->perfil->temPermissao('UserController', 'index'))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fa fa-users"></i>
                <p>Usuários<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('usuarios.lista') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todos os usuários</p>
                    </a>
                </li>

                @can('onlyAdmin', auth()->user())
                <li class="nav-item">
                    <a href="/admin/usuarios/criar" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Novo usuário</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('perfis.lista') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Perfis</p>
                    </a>
                </li>
                @endcan
            </ul>
        </li>
        @endif

        @can('onlyAdmin', auth()->user())
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-tools"></i>
                <p>Suporte<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('suporte.log.externo.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Logs</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('suporte.erros.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Erros</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('suporte.ips.view') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Desbloquear IP</p>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a href="{{ route('chamados.lista') }}" class="nav-link">
                <i class="nav-icon fas fa-ticket-alt"></i>
                <p>Chamados</p>
            </a>
        </li>
        @endcan 
        <li class="nav-item">
            <a href="{{ route('regionais.index') }}" class="nav-link">
                <i class="nav-icon fas fa-globe-americas"></i>
                <p>Regionais</p>
            </a>
        </li>

        <!-- Conteúdo -->
        @if(auth()->user()->perfil->podeAcessarMenuConteudo())
        <li class="nav-header">CONTEÚDO</li>

        @if(auth()->user()->perfil->temPermissao('PaginaController', 'index'))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-file-alt"></i>
                <p>Páginas<i class="right fa fa-angle-left"></i></p>
            </a>
        
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('paginas.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todas as páginas</p>
                    </a>
                </li>

                @if(auth()->user()->perfil->temPermissao('PaginaController', 'create'))
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

        @if(auth()->user()->perfil->temPermissao('NoticiaController', 'index'))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon far fa-newspaper"></i>
                <p>Notícias<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('noticias.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todas as notícias</p>
                    </a>
                </li>

                @if(auth()->user()->perfil->temPermissao('NoticiaController', 'create'))
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

        @if(auth()->user()->perfil->temPermissao('PostsController', 'index'))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-rss"></i>
                <p>Blog<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('posts.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todos os posts</p>
                    </a>
                </li>

                @if(auth()->user()->perfil->temPermissao('PostsController', 'create'))
                <li class="nav-item">
                    <a href="{{ route('posts.create') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Criar novo post</p>
                    </a>
                </li>
                @endif

            </ul>
        @endif

        @if(auth()->user()->perfil->temPermissao('CursoController', 'index'))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-graduation-cap"></i>
                <p>Cursos<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('cursos.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todos os cursos</p>
                    </a>
                </li>

                @if(auth()->user()->perfil->temPermissao('CursoController', 'create'))
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

        @if(auth()->user()->perfil->podeAcessarSubMenuBalcao())
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-briefcase"></i>
                <p>B. de Oportunidades<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
            @if(auth()->user()->perfil->temPermissao('BdoEmpresaController', 'index'))
                <li class="nav-item">
                    <a href="{{ route('bdoempresas.lista') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Empresas</p>
                    </a>
                </li>
                @endif

                @if(auth()->user()->perfil->temPermissao('BdoOportunidadeController', 'index'))
                <li class="nav-item">
                    <a href="{{ route('bdooportunidades.lista') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Oportunidades</p>
                    </a>
                </li>
                @endif

            </ul>
        </li>
        @endif

        @if(auth()->user()->perfil->temPermissao('CompromissoController', 'index'))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-calendar-alt"></i>
                <p>Compromissos<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('compromisso.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todos os compromissos</p>
                    </a>
                </li>

                @if(auth()->user()->perfil->temPermissao('CompromissoController', 'create'))
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

        @if(auth()->user()->perfil->temPermissao('HomeImagemController', 'edit'))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon far fa-image"></i>
                <p>Imagens<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('imagens.banner') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Banner principal</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('imagens.itens.home') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Itens home</p>
                    </a>
                </li>
            </ul>
        </li>
        @endif

        @if(auth()->user()->perfil->temPermissao('AvisoController', 'index'))
        <li class="nav-item">
            <a href="{{ route('avisos.index') }}" class="nav-link">
                <i class="nav-icon fas fa-info-circle"></i>
                <p>Avisos &nbsp;&nbsp;{!! $ativado !!}</p>
            </a>
        </li>
        @endif

        @if(auth()->user()->perfil->temPermissao('CartaServicos', 'index'))
        <li class="nav-item">
            <a href="{{ route('textos.view', 'carta-servicos') }}" class="nav-link">
                <i class="nav-icon fas fa-envelope"></i>
                <p>Carta de Serviços</p>
            </a>
        </li>
        @endif
        @endif
         
        <!-- Atendimento -->
        @if(auth()->user()->perfil->podeAcessarMenuAtendimento())
        <li class="nav-header">ATENDIMENTO</li>
        
        @if(auth()->user()->perfil->podeAcessarSubMenuAgendamento())
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon far fa-clock"></i>
                <p>Agendamentos<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                @if(auth()->user()->perfil->temPermissao('AgendamentoController', 'index'))
                <li class="nav-item">
                    <a href="{{ route('agendamentos.lista') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todos os agendamentos</p>
                    </a>
                </li>
                @endif

                @if(auth()->user()->perfil->temPermissao('AgendamentoBloqueioController', 'index'))
                <li class="nav-item">
                    <a href="{{ route('agendamentobloqueios.lista') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Bloqueios</p>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif
        
        @if(auth()->user()->perfil->podeAcessarSubMenuRepresentante())
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fa fa-users"></i>
                <p>Representantes<i class="right fa fa-angle-left"></i></p>
            </a>

            <ul class="nav nav-treeview">
                @if(auth()->user()->perfil->temPermissao('RepresentanteController', 'index'))
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

                @if(auth()->user()->perfil->temPermissao('RepresentanteEnderecoController', 'index'))
                <li class="nav-item">
                    <a href="/admin/representante-enderecos" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Endereços</p>
                    </a>
                </li>    
                @endif

                @if(auth()->user()->perfil->temPermissao('SolicitaCedulaController', 'index'))
                <li class="nav-item">
                    <a href="{{ route('solicita-cedula.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Cédulas</p>
                    </a>
                </li>    
                @endif

            </ul>
        </li>
        @endif

        @if(auth()->user()->perfil->podeAcessarSubMenuSalaReuniao())
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-building"></i>
                <p>Reunião / Coworking<i class="right fa fa-angle-left"></i></p>
            </a>

            <ul class="nav nav-treeview">
                @if(auth()->user()->perfil->temPermissao('SalaReuniaoController', 'index'))
                <li class="nav-item">
                    <a href="{{ route('sala.reuniao.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Salas</p>
                    </a>
                </li>
                @endif

                @if(auth()->user()->perfil->temPermissao('AgendamentoController', 'index'))
                <li class="nav-item">
                    <a href="{{ route('sala.reuniao.agendados.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Agendados</p>
                    </a>
                </li>
                @endif

                @if(auth()->user()->perfil->temPermissao('AgendamentoBloqueioController', 'index'))
                <li class="nav-item">
                    <a href="{{ route('sala.reuniao.bloqueio.lista') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Bloqueios</p>
                    </a>
                </li>
                @endif

                @if(auth()->user()->perfil->temPermissao('SuspensaoExcecaoController', 'index'))
                <li class="nav-item">
                    <a href="{{ route('sala.reuniao.suspensao.lista') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Suspensos / Exceções</p>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif
        @endif

        <!-- Jurídico -->
        @if(auth()->user()->perfil->podeAcessarMenuJuridico())
        <li class="nav-header">JURÍDICO</li>

        @if(auth()->user()->perfil->temPermissao('LicitacaoController', 'index'))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon far fa-file-alt"></i>
                <p>Licitações<i class="right fa fa-angle-left"></i></p>
            </a>

            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('licitacoes.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todas as licitações</p>
                    </a>
                </li>

                @if(auth()->user()->perfil->temPermissao('LicitacaoController', 'create'))
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

        @if(auth()->user()->perfil->temPermissao('ConcursoController', 'index'))
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon far fa-edit"></i>
                <p>Concursos<i class="right fa fa-angle-left"></i></p>
            </a>
        
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('concursos.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todos os concursos</p>
                    </a>
                </li>

                @if(auth()->user()->perfil->temPermissao('ConcursoController', 'create'))
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

        @if(auth()->user()->perfil->podeAcessarSubMenuPlantao())
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-calendar-alt"></i>
                <p>Plantão Jurídico<i class="right fa fa-angle-left"></i></p>
            </a>
            
            <ul class="nav nav-treeview">
                @if(auth()->user()->perfil->temPermissao('PlantaoJuridicoController', 'index'))
                <li class="nav-item">
                    <a href="{{ route('plantao.juridico.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Plantões</p>
                    </a>
                </li>
                @endif

                @if(auth()->user()->perfil->temPermissao('PlantaoJuridicoBloqueioController', 'index'))
                <li class="nav-item">
                    <a href="{{ route('plantao.juridico.bloqueios.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Bloqueios</p>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif

        @endif
    
        <!-- Fiscalização -->
        @if(auth()->user()->perfil->podeAcessarMenuFiscal())
        <li class="nav-header">FISCALIZAÇÃO</li>
        
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon far fa-file-alt"></i>
                <p>Dados de Fiscalização<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('fiscalizacao.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todos os anos</p>
                    </a>
                    </li>

                @if(auth()->user()->perfil->temPermissao('FiscalizacaoController', 'create'))
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