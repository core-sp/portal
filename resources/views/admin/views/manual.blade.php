<div class="card-body">

    <h5 class="text-danger mb-4"><strong>ATENÇÃO!</strong> <em>Estes arquivos são de uso exclusivo por funcionários do CORE-SP.</em></h5>

    <div id="accordion">

        <button class="btn btn-primary btn-block font-weight-bolder" data-toggle="collapse" data-target="#basico">Funções Básicas <small>(Admin, Representante)</small></button>
        <div id="basico" class="collapse" data-parent="#accordion">
        
            <div class="row mt-2">
                <div class="col-sm-4">
                    <p class="font-weight-bolder">Admin - Menus</p>
                    <p>
                        <em>Os serviços no menu vertical são disponibilizados conforme o perfil do usuário.</em>
                    </p>
                    <a href="{{ route('admin.manual', 'basico_menus.png') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir
                    </a>
                </div>

                <div class="col-sm-4">
                    <p class="font-weight-bolder">Admin - Home</p>
                    <p>
                        <em>A home sofre algumas alterações conforme o perfil do usuário.</em>
                    </p>
                    <a href="{{ route('admin.manual', 'basico_atalho_home.mp4') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>

                <div class="col-sm-4">
                    <p class="font-weight-bolder">Admin - Perfil</p>
                    <a href="{{ route('admin.manual', 'basico_atalho_perfil.mp4') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>
            </div>

            <hr />

            <div class="row mt-2">
                <div class="col-sm-4">
                    <p class="font-weight-bolder">Admin - Abrir Chamados</p>
                    <a href="{{ route('admin.manual', 'basico_atalho_chamados.mp4') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>

                <div class="col-sm-4">
                    <p class="font-weight-bolder">Admin - Perfil pelo menu vertical</p>
                    <a href="{{ route('admin.manual', 'basico_atalho_perfil_vertical.mp4') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>

                <div class="col-sm-4">
                    <p class="font-weight-bolder">Admin - Alterar senha</p>
                    <a href="{{ route('admin.manual', 'basico_alterar_senha.mp4') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>
            </div>

            <hr />

            <div class="row mt-2">
                <div class="col-sm-4">
                    <p class="font-weight-bolder">Admin - Desconectar</p>
                    <a href="{{ route('admin.manual', 'basico_logout.png') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir
                    </a>
                </div>

                <div class="col-sm-4">
                    <p class="font-weight-bolder">Representante - Cadastro</p>
                    <em>Os dados devem constar no Gerenti e situação deve ser 'Ativo'.</em>
                    <p class="font-weight-bolder">
                        <em>Link: <a href="{{ route('representante.cadastro') }}" target="_blank" >{{ route('representante.cadastro') }}</a></em>
                    </p>
                    <a href="{{ route('admin.manual', 'basico_rep_cadastro.mp4') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>

                <div class="col-sm-4">
                    <p class="font-weight-bolder">Representante - Alterar senha</p>
                    <p class="font-weight-bolder">
                        <em>Link: <a href="{{ route('representante.password.request') }}" target="_blank" >{{ route('representante.password.request') }}</a></em>
                    </p>
                    <a href="{{ route('admin.manual', 'basico_rep_alterar_senha.mp4') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>
            </div>

            <hr />

            <div class="row mt-2">
                <div class="col-sm-4">
                    <p class="font-weight-bolder">Representante - Alterar e-mail</p>
                    <p class="font-weight-bolder">
                        <em>Link: <a href="{{ route('representante.email.reset.view') }}" target="_blank" >{{ route('representante.email.reset.view') }}</a></em>
                    </p>
                    <a href="{{ route('admin.manual', 'basico_rep_alterar_email.mp4') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>

                <div class="col-sm-4">
                    <p class="font-weight-bolder">Representante - Desconectar</p>
                    <a href="{{ route('admin.manual', 'basico_rep_logout.png') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir
                    </a>
                </div>
            </div>
        </div>

        <hr />

        <button class="btn btn-info btn-block font-weight-bolder" data-toggle="collapse" data-target="#serv_regional">Serviço: Regionais</button>
        <div id="serv_regional" class="collapse" data-parent="#accordion">

            <div class="row mt-2">
                <div class="col-sm-4">
                    <p class="font-weight-bolder">Editar</p>
                    <p><em>
                        Ao editar o formulário da regional, o usuário com permissão irá alterar o texto que aparece nos links da página <a href="{{ route('regionais.siteGrid') }}" target="_blank" >{{ route('regionais.siteGrid') }}</a> , poderá alterar a quantidade de atendentes para agendamento e os horários permitidos.
                    </em></p>
                    <a href="{{ route('admin.manual', 'serv_regional_editar.mp4') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>
            </div>
        </div>

        <hr />

        <button class="btn btn-success btn-block font-weight-bolder" data-toggle="collapse" data-target="#area_rep">Área do Representante</button>
        <div id="area_rep" class="collapse" data-parent="#accordion">

            <div class="row mt-2">
                <div class="col-sm-4">
                    <p class="font-weight-bolder">Aba - Home</p>
                    <p class="font-weight-bolder">
                        <em>Link: <a href="{{ route('representante.dashboard') }}" target="_blank" >{{ route('representante.dashboard') }}</a></em>
                    </p>
                    <a href="{{ route('admin.manual', 'area_rep_home.JPG') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir
                    </a>
                </div>

                <div class="col-sm-4">
                    <p class="font-weight-bolder">Aba - Dados Gerais</p>
                    <p class="font-weight-bolder">
                        <em>Link: <a href="{{ route('representante.dados-gerais') }}" target="_blank" >{{ route('representante.dados-gerais') }}</a></em>
                    </p>
                    <a href="{{ route('admin.manual', 'area_rep_dados_gerais.mp4') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>

                <div class="col-sm-4">
                    <p class="font-weight-bolder">Aba - Contatos</p>
                    <em>Gerencia contatos no Gerenti.</em>
                    <p class="font-weight-bolder">
                        <em>Link: <a href="{{ route('representante.contatos.view') }}" target="_blank" >{{ route('representante.contatos.view') }}</a></em>
                    </p>
                    <a href="{{ route('admin.manual', 'area_rep_contatos.JPG') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir
                    </a>
                </div>
            </div>

            <hr />

            <div class="row mt-2">
                <div class="col-sm-4">
                    <p class="font-weight-bolder">Aba - Contatos > Inserir Contato</p>
                    <em>Adiciona contato no Gerenti.</em>
                    <p class="font-weight-bolder">
                        <em>Link: <a href="{{ route('representante.inserir-ou-alterar-contato.view') }}" target="_blank" >{{ route('representante.inserir-ou-alterar-contato.view') }}</a></em>
                    </p>
                    <a href="{{ route('admin.manual', 'area_rep_contatos_inserir.mp4') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>

                <div class="col-sm-4">
                    <p class="font-weight-bolder">Aba - End. de Correspondência</p>
                    <p class="font-weight-bolder">
                        <em>Link: <a href="{{ route('representante.enderecos.view') }}" target="_blank" >{{ route('representante.enderecos.view') }}</a></em>
                    </p>
                    <a href="{{ route('admin.manual', 'area_rep_endereco.JPG') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir
                    </a>
                </div>

                <div class="col-sm-4">
                    <p class="font-weight-bolder">Aba - End. de Correspondência > Inserir Endereço</p>
                    <em>Atualiza endereço no Gerenti após aprovação do atendente.</em>
                    <p class="font-weight-bolder">
                        <em>Link: <a href="{{ route('representante.inserir-endereco.view') }}" target="_blank" >{{ route('representante.inserir-endereco.view') }}</a></em>
                    </p>
                    <a href="{{ route('admin.manual', 'area_rep_endereco_inserir.mp4') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>
            </div>

            <hr />

            <div class="row mt-2">
                <div class="col-sm-4">
                    <p class="font-weight-bolder">Aba - Situação Financeira</p>
                    <p class="font-weight-bolder">
                        <em>Link: <a href="{{ route('representante.lista-cobrancas') }}" target="_blank" >{{ route('representante.lista-cobrancas') }}</a></em>
                    </p>
                    <a href="{{ route('admin.manual', 'area_rep_financeiro.JPG') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir
                    </a>
                </div>

                <div class="col-sm-4">
                    <p class="font-weight-bolder">Aba - Emitir Certidão</p>
                    <p class="font-weight-bolder">
                        <em>Link: <a href="{{ route('representante.emitirCertidaoView') }}" target="_blank" >{{ route('representante.emitirCertidaoView') }}</a></em>
                    </p>
                    <a href="{{ route('admin.manual', 'area_rep_certidao.JPG') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir
                    </a>
                </div>

                <div class="col-sm-4">
                    <p class="font-weight-bolder">Aba - Oportunidades</p>
                    <em>Oportunidades do Balcão de Oportunidades.</em>
                    <p class="font-weight-bolder">
                        <em>Link: <a href="{{ route('representante.bdo') }}" target="_blank" >{{ route('representante.bdo') }}</a></em>
                    </p>
                    <a href="{{ route('admin.manual', 'area_rep_oportunidades.JPG') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir
                    </a>
                </div>
            </div>

            <hr />

            <div class="row mt-2">
                <div class="col-sm-4">
                    <p class="font-weight-bolder">Aba - Solicitação de Cédula</p>
                    <p class="font-weight-bolder">
                        <em>Link: <a href="{{ route('representante.solicitarCedulaView') }}" target="_blank" >{{ route('representante.solicitarCedulaView') }}</a></em>
                    </p>
                    <a href="{{ route('admin.manual', 'area_rep_cedula.JPG') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir
                    </a>
                </div>

                <div class="col-sm-4">
                    <p class="font-weight-bolder">Aba - Solicitação de Cédula > Solicitar Cédula</p>
                    <em>Cédula (impressa e/ou digital) é enviada após aprovação do atendente.</em>
                    <p class="font-weight-bolder">
                        <em>Link: <a href="{{ route('representante.inserirSolicitarCedulaView') }}" target="_blank" >{{ route('representante.inserirSolicitarCedulaView') }}</a></em>
                    </p>
                    <a href="{{ route('admin.manual', 'area_rep_cedula_solicitar.mp4') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>
            </div>

        </div>

        <hr />

        <button class="btn btn-warning btn-block font-weight-bolder" data-toggle="collapse" data-target="#duvidas_frequentes">Dúvidas Frequentes</button>
        <div id="duvidas_frequentes" class="collapse" data-parent="#accordion">
        
            <div class="row mt-2">
                <div class="col-sm-4">
                    <p class="font-weight-bolder">Representante com agendamento bloqueado</p>
                    <p><span class="font-weight-bolder">Solução: </span>
                        <em>Usuário com permissão deve buscar os agendamentos do representante pelo cpf e atualizar os últimos 3 com o status 'Não Compareceu' para 'Cancelado'.</em>
                    </p>
                    <a href="{{ route('admin.manual', 'duvidas_agend_bloqueado.mp4') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>

                <div class="col-sm-4">
                    <p class="font-weight-bolder">Representante não consegue fazer login - Caso 1</p>
                    <em>Usuário com permissão deve verificar se o CPF/CNPJ existe no Gerenti.</em>
                    <p><span class="font-weight-bolder">Solução 1: </span>
                        <em> Representante deve se registrar no Core-SP como Representante.</em>
                    </p>
                    <a href="{{ route('admin.manual', 'duvidas_rep_login_invalido_1.mp4') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>

                <div class="col-sm-4">
                    <p class="font-weight-bolder">Representante não consegue fazer login - Caso 2</p>
                    <em>Usuário com permissão deve verificar pelo CPF/CNPJ <strong>sem pontuação</strong> ou nome ou registro ou e-mail se está cadastrado e ativo no Portal.</em>
                    <p class="m-0"><span class="font-weight-bolder">Condição: </span>
                        <em> Representante cadastrado no Gerenti.</em>
                    </p>
                    <p><span class="font-weight-bolder">Solução 2: </span>
                        <em> Representante deve ativar em 24 horas o cadastro pelo e-mail, caso contrário deve se recadastrar no Portal e ativar pelo novo e-mail enviado.</em>
                    </p>
                    <a href="{{ route('admin.manual', 'duvidas_rep_login_invalido_2.mp4') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>
            </div>

            <hr />

            <div class="row mt-2">
                <div class="col-sm-4">
                    <p class="font-weight-bolder">Representante não consegue fazer login - Caso 3</p>
                    <em>Usuário pode orientar o Representante como alterar a senha.</em>
                    <p class="m-0"><span class="font-weight-bolder">Condição: </span>
                        <em> Representante cadastrado no Gerenti e ativo no Portal.</em>
                    </p>
                    <p><span class="font-weight-bolder">Solução 3: </span>
                        <em> Representante deve solicitar troca da senha com o CPF/CNPJ usado no cadastro no Portal pelo link <a href="{{ route('representante.password.request') }}" target="_blank" >{{ route('representante.password.request') }}</a> e pelo link no e-mail enviado deve alterá-la.</em>
                    </p>
                    <a href="{{ route('admin.manual', 'basico_rep_alterar_senha.mp4') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>

                <div class="col-sm-4">
                    <p class="font-weight-bolder">Representante não consegue alterar a senha</p>
                    <em>Usuário pode orientar o Representante a solicitar novamente a troca de senha pelo link <a href="{{ route('representante.password.request') }}" target="_blank" >{{ route('representante.password.request') }}</a>.</em>
                    <p class="m-0"><span class="font-weight-bolder">Condição: </span>
                        <em> Representante cadastrado no Gerenti e cadastrado no Portal, independente se está ativo ou não no Portal.</em>
                    </p>
                    <p><span class="font-weight-bolder">Solução: </span>
                        <em>Token do link do e-mail enviado expirou após 60 minutos, então deve refazer a solicitação e acessar o link do novo e-mail enviado.</em>
                    </p>
                    <a href="{{ route('admin.manual', 'duvidas_rep_erro_trocar_senha.mp4') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>

                <div class="col-sm-4">
                    <p class="font-weight-bolder">Representante não consegue alterar o e-mail - Caso 1</p>
                    <p class="m-0"><span class="font-weight-bolder">Condição: </span>
                        <em> Representante cadastrado no Gerenti e cadastrado no Portal, independente se está ativo ou não no Portal.</em>
                    </p>
                    <p><span class="font-weight-bolder">Solução 1: </span>
                        <em>Usuário com permissão deve verificar se o novo e-mail (campo Novo e-mail) está cadastrado e ativo no Gerenti.</em>
                    </p>
                    <a href="{{ route('admin.manual', 'duvidas_rep_erro_trocar_email_1.mp4') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>
            </div>

            <hr />

            <div class="row mt-2">
                <div class="col-sm-4">
                    <p class="font-weight-bolder">Representante não consegue alterar o e-mail - Caso 2</p>
                    <p class="m-0"><span class="font-weight-bolder">Condição: </span>
                        <em> Representante cadastrado no Gerenti e cadastrado no Portal, independente se está ativo ou não no Portal.</em>
                    </p>
                    <p><span class="font-weight-bolder">Solução 2: </span>
                        <em>Usuário com permissão deve verificar se o e-mail a ser trocado (campo E-mail antigo) está cadastrado no Portal.</em>
                    </p>
                    <a href="{{ route('admin.manual', 'duvidas_rep_erro_trocar_email_2.mp4') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>
            </div>

        </div>

    </div>

    <hr />
    
    <div class="float-right mt-2">
        <p class="m-0">
            <em><strong>Última atualização:</strong> 03/04/2023</em>
        </p>
    </div>

</div>