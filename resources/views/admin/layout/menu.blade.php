<!-- Sidebar Menu -->
@php
    $permitidos = perfisPermitidosMenu();
    $idperfil = auth()->user()->idperfil;
@endphp
<nav class="mt-2 mb-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

        <!-- Usuários -->
        @if(in_array($idperfil, $permitidos->find(1)['perfis']))
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

        @php
            $pagina = in_array($idperfil, $permitidos->find(3)['perfis']);
            $noticia = in_array($idperfil, $permitidos->find(7)['perfis']);
            $posts = in_array($idperfil, $permitidos->find(43)['perfis']);
            $curso = in_array($idperfil, $permitidos->find(11)['perfis']);
            $bdoEmpresa = in_array($idperfil, $permitidos->find(19)['perfis']);
            $bdoOportunidade = in_array($idperfil, $permitidos->find(23)['perfis']);
            $home = in_array($idperfil, $permitidos->find(42)['perfis']);
            $compromisso = in_array($idperfil, $permitidos->find(53)['perfis']);
            $aviso = in_array($idperfil, $permitidos->find(57)['perfis']);
        @endphp

        @if($pagina || $noticia || $posts || $curso || $bdoEmpresa || $bdoOportunidade || $home || $compromisso || $aviso)
        <li class="nav-header">CONTEÚDO</li>

        @if($pagina)
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

                @if(in_array($idperfil, $permitidos->find(4)['perfis']))
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

        @if($noticia)
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

                @if(in_array($idperfil, $permitidos->find(8)['perfis']))
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

        @if($posts)
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

                @if(in_array($idperfil, $permitidos->find(48)['perfis']))
                <li class="nav-item">
                    <a href="{{ route('posts.create') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Criar novo post</p>
                    </a>
                </li>
                @endif

            </ul>
        @endif

        @if($curso)
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

                @if(in_array($idperfil, $permitidos->find(12)['perfis']))
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

        @if($bdoEmpresa || $bdoOportunidade)
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-briefcase"></i>
                <p>B. de Oportunidades<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                @if($bdoEmpresa)
                <li class="nav-item">
                    <a href="{{ route('bdoempresas.lista') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Empresas</p>
                    </a>
                </li>
                @endif

                @if($bdoOportunidade)
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

        @if($compromisso)
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

                @if(in_array($idperfil, $permitidos->find(54)['perfis']))
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

        @if($home)
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

        @if($aviso)
        <li class="nav-item">
            <a href="{{ route('avisos.index') }}" class="nav-link">
                <i class="nav-icon fas fa-info-circle"></i>
                <p>Avisos</p>
            </a>
        </li>
        @endif
        @endif
         
        <!-- Atendimento -->
        @php
            $agendamento = in_array($idperfil, $permitidos->find(27)['perfis']);
            $agendamentobloqueio = in_array($idperfil, $permitidos->find(29)['perfis']);
            $representante = in_array($idperfil, $permitidos->find(47)['perfis']);
            $representanteEndereco = in_array($idperfil, $permitidos->find(45)['perfis']);
            $representanteCedula = in_array($idperfil, $permitidos->find(59)['perfis']);
        @endphp
        
        @if($agendamento || $agendamentobloqueio || $representante || $representanteEndereco || $representanteCedula)
        <li class="nav-header">ATENDIMENTO</li>
        
        @if($agendamento || $agendamentobloqueio)
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon far fa-clock"></i>
                <p>Agendamentos<i class="right fa fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                @if($agendamento)
                <li class="nav-item">
                    <a href="{{ route('agendamentos.lista') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Todos os agendamentos</p>
                    </a>
                </li>
                @endif

                @if($agendamentobloqueio)
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
        
        @if($representante || $representanteEndereco)
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fa fa-users"></i>
                <p>Representantes<i class="right fa fa-angle-left"></i></p>
            </a>

            <ul class="nav nav-treeview">
                @if($representante)
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

                @if($representanteEndereco)
                <li class="nav-item">
                    <a href="/admin/representante-enderecos" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Endereços</p>
                    </a>
                </li>    
                @endif

                @if($representanteCedula)
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

        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-building"></i>
                <p>Reunião / Coworking<i class="right fa fa-angle-left"></i></p>
            </a>

            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('sala.reuniao.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Salas</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('sala.reuniao.agendados.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Agendados</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#{{-- route('sala.reuniao.index') --}}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Bloqueios</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#{{-- route('sala.reuniao.index') --}}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Suspensos / Exceções</p>
                    </a>
                </li>
            </ul>
        </li>
        @endif

        <!-- Jurídico -->
        @php
            $licitacao = in_array($idperfil, $permitidos->find(33)['perfis']);
            $concurso = in_array($idperfil, $permitidos->find(37)['perfis']);
            $plantao = in_array($idperfil, $permitidos->find(61)['perfis']);
            $plantaoBloqueio = in_array($idperfil, $permitidos->find(63)['perfis']);
        @endphp

        @if($licitacao || $concurso || $plantao | $plantaoBloqueio)
        <li class="nav-header">JURÍDICO</li>

        @if($licitacao)
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

                @if(in_array($idperfil, $permitidos->find(34)['perfis']))
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

        @if($concurso)
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

                @if(in_array($idperfil, $permitidos->find(38)['perfis']))
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

        @if($plantao || $plantaoBloqueio)
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-calendar-alt"></i>
                <p>Plantão Jurídico<i class="right fa fa-angle-left"></i></p>
            </a>
            
            <ul class="nav nav-treeview">
                @if($plantao)
                <li class="nav-item">
                    <a href="{{ route('plantao.juridico.index') }}" class="nav-link">
                        <i class="nav-icon fa fa-angle-right"></i>
                        <p>Plantões</p>
                    </a>
                </li>
                @endif

                @if($plantaoBloqueio)
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
        @if(in_array($idperfil, $permitidos->find(50)['perfis']))
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

                @if(in_array($idperfil, $permitidos->find(51)['perfis']))
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