<div class="card-body">

    <h5 class="text-danger mb-4"><strong>ATENÇÃO!</strong> <em>Estes arquivos são de uso exclusivo por funcionários do CORE-SP.</em></h5>

    <div id="accordion">

    <!-- ÁREA: FUNÇÕES BÁSICAS **************************************************************************************************************************** -->
        <button class="btn btn-primary btn-block font-weight-bolder" data-toggle="collapse" data-target="#basico">Funções Básicas <small>(Admin, Representante)</small></button>
        <div id="basico" class="collapse" data-parent="#accordion">
        
            <div class="table-responsive-sm mt-3 mb-3">
                <table class="table table-hover table-bordered">
                    <thead>
                    <tr>
                        <th>Situação</th>
                        <th>Sobre</th>
                        <th>Link</th>
                        <th>Ver</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Admin - Menus</td>
                            <td>Os serviços no menu vertical são disponibilizados conforme o perfil do usuário.</td>
                            <td>-----</td>
                            <td>
                                <a href="{{ route('admin.manual', 'basico_menus.png') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-image fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Admin - Home</td>
                            <td>A home sofre algumas alterações conforme o perfil do usuário.</td>
                            <td>-----</td>
                            <td>
                                <a href="{{ route('admin.manual', 'basico_atalho_home.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Admin - Perfil</td>
                            <td>-----</td>
                            <td>-----</td>
                            <td>
                                <a href="{{ route('admin.manual', 'basico_atalho_perfil.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Admin - Perfil pelo menu vertical</td>
                            <td>-----</td>
                            <td>-----</td>
                            <td>
                                <a href="{{ route('admin.manual', 'basico_atalho_perfil_vertical.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Admin - Abrir Chamados</td>
                            <td>-----</td>
                            <td>-----</td>
                            <td>
                                <a href="{{ route('admin.manual', 'basico_atalho_chamados.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Admin - Alterar senha</td>
                            <td>-----</td>
                            <td>-----</td>
                            <td>
                                <a href="{{ route('admin.manual', 'basico_alterar_senha.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Admin - Desconectar</td>
                            <td>-----</td>
                            <td>-----</td>
                            <td>
                                <a href="{{ route('admin.manual', 'basico_logout.png') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-image fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Admin - Upload de arquivo</td>
                            <td>Serviços com opção de upload: Chamados, Páginas, Notícias, Blog, Cursos, Imagens, Licitações.<br>
                                Dependendo do serviço o nome altera podendo ser: Alterar / Inserir Imagem; Inserir Edital.<br>
                                <strong>Todos os arquivos são somente visíveis para o próprio usuário que fez upload.</strong>
                            </td>
                            <td>-----</td>
                            <td>
                                <a href="{{ route('admin.manual', 'basico_admin_upload_lfm.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Admin - Ações para gerenciar arquivo anexado</td>
                            <td>
                                <strong>Todos os arquivos são somente visíveis para o próprio usuário que fez upload.</strong>
                            </td>
                            <td>-----</td>
                            <td>
                                <a href="{{ route('admin.manual', 'basico_admin_acoes_lfm.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Representante - Cadastro</td>
                            <td>Os dados devem constar no Gerenti e situação deve ser 'Ativo'.</td>
                            <td><a href="{{ route('representante.cadastro') }}" target="_blank" >{{ route('representante.cadastro') }}</a></td>
                            <td>
                                <a href="{{ route('admin.manual', 'basico_rep_cadastro.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Representante - Alterar senha</td>
                            <td>-----</td>
                            <td><a href="{{ route('representante.password.request') }}" target="_blank" >{{ route('representante.password.request') }}</a></td>
                            <td>
                                <a href="{{ route('admin.manual', 'basico_rep_alterar_senha.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Representante - Alterar e-mail</td>
                            <td>-----</td>
                            <td><a href="{{ route('representante.email.reset.view') }}" target="_blank" >{{ route('representante.email.reset.view') }}</a></td>
                            <td>
                                <a href="{{ route('admin.manual', 'basico_rep_alterar_email.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Representante - Desconectar</td>
                            <td>-----</td>
                            <td>-----</td>
                            <td>
                                <a href="{{ route('admin.manual', 'basico_rep_logout.png') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-image fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
           
        </div>
    <!-- ************************************************************************************************************************************************** -->

        <hr />

    <!-- ÁREA: SERVIÇO REGIONAIS **************************************************************************************************************************** -->
        <button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_regional">Serviço: Regionais&nbsp;&nbsp;<i class="nav-icon fas fa-globe-americas"></i></button>
        <div id="serv_regional" class="collapse" data-parent="#accordion">

            <div class="table-responsive-sm mt-3 mb-3">
                <table class="table table-hover table-bordered">
                    <thead>
                    <tr>
                        <th>Situação</th>
                        <th>Sobre</th>
                        <th>Ver</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Campos do formulário</td>
                            <td>Detalhes dos campos do formulário. O que é obrigatório e para que serve.</td>
                            <td>
                                <a href="{{ route('admin.manual', 'serv_regional_campos_form.jpg') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-image fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Editar</td>
                            <td>Ao editar o formulário da regional, o usuário com permissão irá alterar o texto que aparece nos links da página <a href="{{ route('regionais.siteGrid') }}" target="_blank" >{{ route('regionais.siteGrid') }}</a> , poderá alterar a quantidade de atendentes para agendamento e os horários permitidos.</td>
                            <td>
                                <a href="{{ route('admin.manual', 'serv_regional_editar.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
        </div>
    <!-- *********************************************************************************************************************************************************** -->
    
        <hr />

    <!-- ÁREA: SERVIÇO NOTÍCIAS **************************************************************************************************************************** -->
        <button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_noticia">Serviço: Notícias&nbsp;&nbsp;<i class="nav-icon far fa-newspaper"></i></button>
        <div id="serv_noticia" class="collapse" data-parent="#accordion">

            <div class="table-responsive-sm mt-3 mb-3">
                <table class="table table-hover table-bordered">
                    <thead>
                    <tr>
                        <th>Situação</th>
                        <th>Sobre</th>
                        <th>Ver</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Campos do formulário</td>
                            <td>Detalhes dos campos do formulário. O que é obrigatório e para que serve.</td>
                            <td>
                                <a href="{{ route('admin.manual', 'serv_noticia_campos_form.jpg') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-image fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Criar / Editar</td>
                            <td>
                                Somente usuários com permissão.<br>
                                Ao criar ou editar uma notícia, ela ficará disponível no Portal para todos através do link gerado pelo título inserido.<br>
                                Não pode ser criada ou editada uma notícia com título igual a uma existente.<br>
                                Dependendo da categoria escolhida, a notícia é visualizada em áreas diferentes do Portal. <br>
                                Exemplo: categoria "Espaço do Contador" aparece na página do Espaço do Contador no menu da Fiscalização.<br>
                                <span class="text-danger">Cuidado ao editar o título de uma notícia já em uso, pois o link retornará com erro 404. Certifique-se onde ela é usada nos textos e menus do Portal.</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.manual', 'serv_noticia_criar_editar.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Ver</td>
                            <td>
                                Somente usuários com permissão.<br>
                                Após criar ou editar uma notícia é possível visualizar através do botão "Ver".<br>
                            </td>
                            <td>
                                <a href="{{ route('admin.manual', 'serv_noticia_ver.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Buscar</td>
                            <td>
                                Somente usuários com permissão.<br>
                                A busca pode ser feita por: título ou conteúdo.<br>
                            </td>
                            <td>
                                <a href="{{ route('admin.manual', 'serv_noticia_buscar.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Apagar</td>
                            <td>
                                Somente usuários com permissão.<br>
                                Através do botão "Apagar" a notícia é excluída e o link retorna erro 404.<br>
                            </td>
                            <td>
                                <a href="{{ route('admin.manual', 'serv_noticia_apagar.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Restaurar</td>
                            <td>
                                Somente administradores do Portal.<br>
                                Através do botão "Restaurar" a notícia é restaurada e o link retorna o conteúdo.<br>
                            </td>
                            <td>
                                <a href="{{ route('admin.manual', 'serv_noticia_restaurar.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
        </div>
    <!-- *********************************************************************************************************************************************************** -->

        <hr />

    <!-- ÁREA: SERVIÇO POSTS **************************************************************************************************************************** -->
        <button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_post">Serviço: Blog&nbsp;&nbsp;<i class="nav-icon fas fa-rss"></i></button>
        <div id="serv_post" class="collapse" data-parent="#accordion">

            <div class="table-responsive-sm mt-3 mb-3">
                <table class="table table-hover table-bordered">
                    <thead>
                    <tr>
                        <th>Situação</th>
                        <th>Sobre</th>
                        <th>Ver</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Campos do formulário</td>
                            <td>Detalhes dos campos do formulário. O que é obrigatório e para que serve.</td>
                            <td>
                                <a href="{{ route('admin.manual', 'serv_post_campos_form.jpg') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-image fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Criar / Editar</td>
                            <td>
                                Somente usuários com permissão.<br>
                                Ao criar ou editar um post, ele ficará disponível no Portal para todos através do link gerado pelo título inserido.<br>
                                Não pode ser criado ou editado um post com título igual a um existente.<br>
                                <span class="text-danger">Cuidado ao editar o título de um post já em uso, pois o link retornará com erro 404. Certifique-se onde ele é usado nos textos e menus do Portal.</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.manual', 'serv_post_criar_editar.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Ver</td>
                            <td>
                                Somente usuários com permissão.<br>
                                Após criar ou editar um post é possível visualizar através do botão "Ver".<br>
                            </td>
                            <td>
                                <a href="{{ route('admin.manual', 'serv_post_ver.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Buscar</td>
                            <td>
                                Somente usuários com permissão.<br>
                                A busca pode ser feita por: título ou conteúdo.<br>
                            </td>
                            <td>
                                <a href="{{ route('admin.manual', 'serv_post_buscar.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Apagar</td>
                            <td>
                                Somente usuários com permissão.<br>
                                Através do botão "Apagar" o post é excluído e o link retorna erro 404.<br>
                            </td>
                            <td>
                                <a href="{{ route('admin.manual', 'serv_post_apagar.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
        </div>
    <!-- *********************************************************************************************************************************************************** -->

        <hr />

    <!-- ÁREA: SERVIÇO AGENDAMENTO **************************************************************************************************************************** -->
        <button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_agendamento">Serviço: Agendamento&nbsp;&nbsp;<i class="nav-icon far fa-clock"></i></button>
        <div id="serv_agendamento" class="collapse" data-parent="#accordion">

            <div class="table-responsive-sm mt-3 mb-3">
                <table class="table table-hover table-bordered">
                    <thead>
                    <tr>
                        <th>Situação</th>
                        <th>Sobre</th>
                        <th>Ver</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Campos do formulário</td>
                            <td>Detalhes dos campos do formulário. O que é obrigatório e para que serve.</td>
                            <td>
                                <a href="{{ route('admin.manual', 'serv_agenda_campos_form.jpg') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-image fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Editar</td>
                            <td>
                                Somente usuários com permissão e dependendo da permissão somente agendamentos da regional do próprio perfil.<br>
                                Agendamento com o dia futuro pode editar o cadastro.<br>
                                Agendamento do dia atual ou passado, somente pode editar o serviço, status e atendente.
                            </td>
                            <td>
                                <a href="{{ route('admin.manual', 'serv_agenda_editar.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Atualizar status</td>
                            <td>
                                Somente usuários com permissão e dependendo da permissão somente agendamentos da regional do próprio perfil.<br>
                                Somente agendamentos do dia atual ou passado.<br>
                                Apenas status "Compareceu" e "Não Compareceu".
                            </td>
                            <td>
                                <a href="{{ route('admin.manual', 'serv_agenda_editar_status.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
        </div>
    <!-- *********************************************************************************************************************************************************** -->

        <hr />

    <!-- ÁREA: ÁREA RESTRITA DO REPRESENTANTE **************************************************************************************************************************** -->
        <button class="btn btn-success btn-block font-weight-bolder" data-toggle="collapse" data-target="#area_rep">Área Restrita do Representante</button>
        <div id="area_rep" class="collapse" data-parent="#accordion">

            <div class="table-responsive-sm mt-3 mb-3">
                <table class="table table-hover table-bordered">
                    <thead>
                    <tr>
                        <th>Situação</th>
                        <th>Sobre</th>
                        <th>Link</th>
                        <th>Ver</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Aba - Home</td>
                            <td>Página inicial da área do representante.</td>
                            <td><a href="{{ route('representante.dashboard') }}" target="_blank" >{{ route('representante.dashboard') }}</a></td>
                            <td>
                                <a href="{{ route('admin.manual', 'area_rep_home.JPG') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-image fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Aba - Dados Gerais</td>
                            <td>Detalhes do cadastro.</td>
                            <td><a href="{{ route('representante.dados-gerais') }}" target="_blank" >{{ route('representante.dados-gerais') }}</a></td>
                            <td>
                                <a href="{{ route('admin.manual', 'area_rep_dados_gerais.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Aba - Contatos</td>
                            <td>Gerenciar contatos no Gerenti. Ativar, desativar e inserir.</td>
                            <td><a href="{{ route('representante.contatos.view') }}" target="_blank" >{{ route('representante.contatos.view') }}</a></td>
                            <td>
                                <a href="{{ route('admin.manual', 'area_rep_contatos.JPG') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-image fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Aba - Contatos > Inserir Contato</td>
                            <td>Adiciona contato no Gerenti.</td>
                            <td><a href="{{ route('representante.inserir-ou-alterar-contato.view') }}" target="_blank" >{{ route('representante.inserir-ou-alterar-contato.view') }}</a></td>
                            <td>
                                <a href="{{ route('admin.manual', 'area_rep_contatos_inserir.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Aba - End. de Correspondência</td>
                            <td>Solicitar alteração de endereço.</td>
                            <td><a href="{{ route('representante.enderecos.view') }}" target="_blank" >{{ route('representante.enderecos.view') }}</a></td>
                            <td>
                                <a href="{{ route('admin.manual', 'area_rep_endereco.JPG') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-image fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Aba - End. de Correspondência > Inserir Endereço</td>
                            <td>Atualiza endereço no Gerenti após aprovação do atendente.</td>
                            <td><a href="{{ route('representante.inserir-endereco.view') }}" target="_blank" >{{ route('representante.inserir-endereco.view') }}</a></td>
                            <td>
                                <a href="{{ route('admin.manual', 'area_rep_endereco_inserir.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Aba - Situação Financeira</td>
                            <td>Disponibiliza os boletos dentro do vencimento para download e situação da anuidade vigente.</td>
                            <td><a href="{{ route('representante.lista-cobrancas') }}" target="_blank" >{{ route('representante.lista-cobrancas') }}</a></td>
                            <td>
                                <a href="{{ route('admin.manual', 'area_rep_financeiro.JPG') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-image fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Aba - Emitir Certidão</td>
                            <td>Realizar download da certidão e listagem das certidões anteriores.</td>
                            <td><a href="{{ route('representante.emitirCertidaoView') }}" target="_blank" >{{ route('representante.emitirCertidaoView') }}</a></td>
                            <td>
                                <a href="{{ route('admin.manual', 'area_rep_certidao.JPG') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-image fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Aba - Oportunidades</td>
                            <td>Oportunidades do Balcão de Oportunidades baseado no segmento e seccional do cadastro.</td>
                            <td><a href="{{ route('representante.bdo') }}" target="_blank" >{{ route('representante.bdo') }}</a></td>
                            <td>
                                <a href="{{ route('admin.manual', 'area_rep_oportunidades.JPG') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-image fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Aba - Solicitação de Cédula</td>
                            <td>Solicitar a cédula profissional.</td>
                            <td><a href="{{ route('representante.solicitarCedulaView') }}" target="_blank" >{{ route('representante.solicitarCedulaView') }}</a></td>
                            <td>
                                <a href="{{ route('admin.manual', 'area_rep_cedula.JPG') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-image fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Aba - Solicitação de Cédula > Solicitar Cédula</td>
                            <td>Cédula (impressa e/ou digital) é enviada após aprovação do atendente.</td>
                            <td><a href="{{ route('representante.inserirSolicitarCedulaView') }}" target="_blank" >{{ route('representante.inserirSolicitarCedulaView') }}</a></td>
                            <td>
                                <a href="{{ route('admin.manual', 'area_rep_cedula_solicitar.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    <!-- *********************************************************************************************************************************************************** -->
        
        <hr />

    <!-- ÁREA: DÚVIDAS FREQUENTES **************************************************************************************************************************** -->
        <button class="btn btn-warning btn-block font-weight-bolder" data-toggle="collapse" data-target="#duvidas_frequentes">Dúvidas Frequentes</button>
        <div id="duvidas_frequentes" class="collapse" data-parent="#accordion">
        
            <div class="table-responsive-sm mt-3 mb-3">
                <table class="table table-hover table-bordered">
                    <thead>
                    <tr>
                        <th>Situação</th>
                        <th>Condição</th>
                        <th>Solução</th>
                        <th>Ver</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Representante com agendamento bloqueado</td>
                            <td>-----</td>
                            <td>Usuário com permissão deve buscar os agendamentos do representante pelo cpf e atualizar os últimos 3 com o status 'Não Compareceu' para 'Cancelado'.</td>
                            <td>
                                <a href="{{ route('admin.manual', 'duvidas_agend_bloqueado.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Representante não consegue fazer login - Caso 1</td>
                            <td>-----</td>
                            <td>
                                Usuário com permissão deve verificar se o CPF/CNPJ existe no Gerenti.<br>
                                Caso não exista pode orientar a se registrar no Core-SP como Representante.
                            </td>
                            <td>
                                <a href="{{ route('admin.manual', 'duvidas_rep_login_invalido_1.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Representante não consegue fazer login - Caso 2</td>
                            <td>Representante cadastrado no Gerenti.</td>
                            <td>
                                Usuário com permissão deve verificar pelo CPF/CNPJ <strong>sem pontuação</strong> se está cadastrado e ativo no Portal.<br>
                                Caso não esteja cadastrado deve se cadastrar, e caso não esteja ativo deve ativar em 24 horas o cadastro pelo e-mail, caso contrário deve se recadastrar no Portal e ativar pelo novo e-mail enviado.
                            </td>
                            <td>
                                <a href="{{ route('admin.manual', 'duvidas_rep_login_invalido_2.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Representante não consegue fazer login - Caso 3</td>
                            <td>Representante cadastrado no Gerenti e ativo no Portal.</td>
                            <td>
                                Usuário pode orientar o Representante como alterar a senha.<br>
                                Representante deve solicitar troca da senha com o CPF/CNPJ usado no cadastro no Portal pelo link <a href="{{ route('representante.password.request') }}" target="_blank" >{{ route('representante.password.request') }}</a> e alterá-la pelo link enviado no e-mail.
                            </td>
                            <td>
                                <a href="{{ route('admin.manual', 'basico_rep_alterar_senha.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Representante não consegue alterar a senha</td>
                            <td>Representante cadastrado no Gerenti e cadastrado no Portal, independente se está ativo ou não no Portal.</td>
                            <td>
                                Usuário pode orientar o Representante a solicitar novamente a troca de senha pelo link <a href="{{ route('representante.password.request') }}" target="_blank" >{{ route('representante.password.request') }}</a>.<br>
                                Token do link do e-mail enviado expirou após 60 minutos, então deve refazer a solicitação e acessar o link do novo e-mail enviado.
                            </td>
                            <td>
                                <a href="{{ route('admin.manual', 'duvidas_rep_erro_trocar_senha.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Representante não consegue alterar o e-mail - Caso 1</td>
                            <td>Representante cadastrado no Gerenti e cadastrado no Portal, independente se está ativo ou não no Portal.</td>
                            <td>Usuário com permissão deve verificar se o novo e-mail (campo "Novo e-mail") está cadastrado e ativo no Gerenti.</td>
                            <td>
                                <a href="{{ route('admin.manual', 'duvidas_rep_erro_trocar_email_1.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Representante não consegue alterar o e-mail - Caso 2</td>
                            <td>Representante cadastrado no Gerenti e cadastrado no Portal, independente se está ativo ou não no Portal.</td>
                            <td>Usuário com permissão deve verificar se o e-mail a ser trocado (campo "E-mail antigo") está cadastrado no Portal.</td>
                            <td>
                                <a href="{{ route('admin.manual', 'duvidas_rep_erro_trocar_email_2.mp4') }}" 
                                    target="_blank" 
                                    rel="noopener" 
                                    type="button" 
                                    class="btn btn-info"
                                >
                                    <i class="fas fa-play fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    <!-- ****************************************************************************************************************************************************** -->
    
    </div>

    <hr />
    
    <div class="float-right mt-2">
        <p class="m-0">
            <em><strong>Última atualização:</strong> 28/04/2023</em>
        </p>
    </div>

</div>