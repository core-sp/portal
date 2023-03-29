<div class="card-body">

    <div id="accordion">

        <button class="btn btn-primary btn-block" data-toggle="collapse" data-target="#basico">Funções Básicas</button>
        <div id="basico" class="collapse" data-parent="#accordion">
        
            <div class="row mt-2">
                <div class="col-sm-4">
                    <p class="font-weight-bolder">Menus</p>
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
                    <p class="font-weight-bolder">Atalhos - Home do Admin</p>
                    <a href="{{ route('admin.manual', 'basico_atalho_home.gif') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>

                <div class="col-sm-4">
                    <p class="font-weight-bolder">Atalhos - Perfil no Admin</p>
                    <a href="{{ route('admin.manual', 'basico_atalho_perfil.gif') }}" 
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
                    <p class="font-weight-bolder">Atalhos - Abrir Chamados</p>
                    <a href="{{ route('admin.manual', 'basico_atalho_chamados.gif') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>

                <div class="col-sm-4">
                    <p class="font-weight-bolder">Atalhos - Perfil no Admin pelo menu vertical</p>
                    <a href="{{ route('admin.manual', 'basico_atalho_perfil_vertical.gif') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>

                <div class="col-sm-4">
                    <p class="font-weight-bolder">Alterar senha</p>
                    <a href="{{ route('admin.manual', 'basico_alterar_senha.gif') }}" 
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
                    <p class="font-weight-bolder">Desconectar do Admin</p>
                    <a href="{{ route('admin.manual', 'basico_logout.gif') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>

                <div class="col-sm-4">
                    <p class="font-weight-bolder">Representante - Cadastro</p>
                    <p class="font-weight-bolder">
                        <em>Link: <a href="{{ route('representante.cadastro') }}" target="_blank" >{{ route('representante.cadastro') }}</a></em>
                    </p>
                    <a href="{{ route('admin.manual', 'basico_rep_cadastro.gif') }}" 
                        target="_blank" 
                        rel="noopener" 
                        type="button" 
                        class="btn btn-secondary"
                    >
                        Abrir <small>(animação)</small>
                    </a>
                </div>

                <div class="col-sm-4">
                    <p class="font-weight-bolder">Representante - Alterar Senha</p>
                    <p class="font-weight-bolder">
                        <em>Link: <a href="{{ route('representante.password.request') }}" target="_blank" >{{ route('representante.password.request') }}</a></em>
                    </p>
                    <a href="{{ route('admin.manual', 'basico_rep_alterar_senha.gif') }}" 
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
                    <p class="font-weight-bolder">Representante - Alterar E-mail</p>
                    <p class="font-weight-bolder">
                        <em>Link: <a href="{{ route('representante.email.reset.view') }}" target="_blank" >{{ route('representante.email.reset.view') }}</a></em>
                    </p>
                    <a href="{{ route('admin.manual', 'basico_rep_alterar_email.gif') }}" 
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

        <button class="btn btn-primary btn-block" data-toggle="collapse" data-target="#area_rep">Área do Representante</button>
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
                    <a href="{{ route('admin.manual', 'area_rep_dados_gerais.gif') }}" 
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
                    <a href="{{ route('admin.manual', 'area_rep_contatos_inserir.gif') }}" 
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
                    <h5>Falta imagem</h5>
                    <p class="font-weight-bolder">Aba - End. de Correspondência > Inserir Endereço</p>
                    <em>Atualiza endereço no Gerenti após aprovação do atendente.</em>
                    <p class="font-weight-bolder">
                        <em>Link: <a href="{{ route('representante.inserir-endereco.view') }}" target="_blank" >{{ route('representante.inserir-endereco.view') }}</a></em>
                    </p>
                    <a href="{{ route('admin.manual', 'area_rep_endereco_inserir.gif') }}" 
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

        <button class="btn btn-primary btn-block" data-toggle="collapse" data-target="#duvidas_frequentes">Dúvidas Frequentes</button>
        <div id="duvidas_frequentes" class="collapse" data-parent="#accordion">
        
            <div class="row mt-2">
                <div class="col-sm-4">
                    <p class="font-weight-bolder">Representante com agendamento bloqueado</p>
                    <p><span class="font-weight-bolder">Solução: </span>
                        <em>Usuário com permissão deve buscar pelo cpf os últimos 3 agendamentos do representante, então atualizá-los para 'Cancelado'.</em>
                    </p>
                    <a href="{{ route('admin.manual', 'duvidas_agend_bloqueado.gif') }}" 
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
                    <em>Usuário com permissão deve verificar se o CPF/CNPJ existe no Gerenti pelo Portal.</em>
                    <p><span class="font-weight-bolder">Solução 1: </span>
                        <em> Representante deve se registrar no Core-SP como Representante.</em>
                    </p>
                    <a href="{{ route('admin.manual', 'duvidas_rep_login_invalido_1.gif') }}" 
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
                    <em>Usuário com permissão deve verificar pelo CPF/CNPJ <strong>sem pontuação</strong> ou nome ou registro ou email se está cadastrado e ativo no Portal.</em>
                    <p class="m-0"><span class="font-weight-bolder">Condição: </span>
                        <em> Representante cadastrado no Gerenti.</em>
                    </p>
                    <p><span class="font-weight-bolder">Solução 2: </span>
                        <em> Representante deve ativar em 24 horas o cadastro pelo e-mail, caso contrário deve se recadastrar no Portal e ativar pelo novo e-mail enviado.</em>
                    </p>
                    <a href="{{ route('admin.manual', 'duvidas_rep_login_invalido_2.gif') }}" 
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
                    <a href="{{ route('admin.manual', 'duvidas_rep_login_invalido_3.gif') }}" 
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
                        <em> Representante cadastrado no Gerenti e cadastrado no Portal, independe se está ativo ou não.</em>
                    </p>
                    <p><span class="font-weight-bolder">Solução: </span>
                        <em>Token do link do e-mail enviado expirou após 60 minutos, então deve refazer a solicitação e acessar o link do novo e-mail enviado.</em>
                    </p>
                    <a href="{{ route('admin.manual', 'duvidas_rep_erro_trocar_senha.gif') }}" 
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
                        <em> Representante cadastrado no Gerenti e cadastrado no Portal, independe se está ativo ou não.</em>
                    </p>
                    <p><span class="font-weight-bolder">Solução 1: </span>
                        <em>Usuário com permissão deve verificar se o novo e-mail está cadastrado no Gerenti.</em>
                    </p>
                    <a href="{{ route('admin.manual', 'duvidas_rep_erro_trocar_email_1.gif') }}" 
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
                        <em> Representante cadastrado no Gerenti e cadastrado no Portal, independe se está ativo ou não.</em>
                    </p>
                    <p><span class="font-weight-bolder">Solução 2: </span>
                        <em>Usuário com permissão deve verificar se o e-mail a ser trocado está cadastrado no Portal.</em>
                    </p>
                    <a href="{{ route('admin.manual', 'duvidas_rep_erro_trocar_email_2.gif') }}" 
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

</div>