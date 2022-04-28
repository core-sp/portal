<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Representante;
use App\RepresentanteEndereco;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\CadastroRepresentanteMail;
use App\Mail\RepresentanteResetPasswordMail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RepresentanteTest extends TestCase
{
    use RefreshDatabase;

    /** @test 
     * 
     * Representante Comercial pode se cadastrar no Portal.
    */
    public function register_new_representante()
    {
        Mail::fake();

        $dados = factory('App\Representante')->raw(['password' => 'teste102030']);

        $this->get(route('representante.cadastro'))->assertOk();

        $this->post(route('representante.cadastro.submit'), ['cpfCnpj' => $dados['cpf_cnpj'], 'registro_core' => $dados['registro_core'], 'email' => $dados['email'], 'password' => $dados['password'], 'password_confirmation' => $dados['password'], 'checkbox-tdu' => 'on']);

        // Checa se o e-mail para confirmação do e-mail foi enviado
        Mail::assertQueued(CadastroRepresentanteMail::class);

        // Checa se o representante foi cadastrado no banco de dados. Campo "ativo" deve ser 0 até o representante confirmar seu e-mail
        $this->assertEquals(Representante::count(), 1);
        $this->assertEquals(Representante::first()->ativo, 0);

        // Checa se após acessar o link de confirmação, o campo "ativo" é atualizado para 1
        $this->get(route('representante.verifica-email', Representante::first()->verify_token));
        $this->assertEquals(Representante::first()->ativo, 1);
    }

    /** @test 
     * 
     * Representante Comercial pode logar na área restrita do Portal.
    */
    public function login_on_restrict_area()
    {
        $representante = factory('App\Representante')->create();

        $this->get(route('representante.login'))->assertOk();

        // Checa se depois de enviar dados de login, o representante é redirecionado para a home page da área restrita
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030'])->assertRedirect(route('representante.dashboard'));
    }

    /** @test 
     * 
     * Representante Comercial pode acessar todas as abas na área restrita do Portal.
    */
    public function access_tabs_on_restrict_area()
    {
        // exige ao acessar a aba bdo
        factory('App\Regional')->create([
            'regional' => 'SÃO PAULO',
        ]);
        $representante = factory('App\Representante')->create();
        
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        // Checa acesso a página principal
        $this->get(route('representante.dashboard'))->assertOk();

        // Checa acesso a aba "Dados Gerais"
        $this->get(route('representante.dados-gerais'))->assertOk();

        // Checa acesso a aba "Contatos"
        $this->get(route('representante.contatos.view'))->assertOk();

        // Checa acesso a aba "End. de Correspondência"
        $this->get(route('representante.enderecos.view'))->assertOk();

        // Checa acesso a aba "Situação Financeira"
        $this->get(route('representante.lista-cobrancas'))->assertOk();
        
        // Checa acesso a aba "Oportunidades"
        $this->get(route('representante.bdo'))->assertOk();

        // Checa acesso a aba "Solicitação de Cédula"
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
    }

    /** @test 
     * 
     * Representante Comercial pode inserir contato.
    */
    public function insert_new_contact_for_representante()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $this->get(route('representante.inserir-ou-alterar-contato.view'))->assertOk();

        // Checa inserção de novo contato (teste não cobre integração com o GERENTI)
        $this->post(route('representante.inserir-ou-alterar-contato'), ['tipo' => 7, 'contato' => '(11) 9999-9999'])->assertRedirect(route('representante.contatos.view'));
    }

    /** @test 
     * 
     * Representante Comercial pode ativar/desativar contato existente.
    */
    public function remove_contact_from_representante()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        // Checa desativar/ativar contato (teste não cobre integração com o GERENTI)
        $this->post(route('representante.deletar-contato'), ['id' => 1, 'status' => 0])->assertRedirect(route('representante.contatos.view'));
        $this->post(route('representante.deletar-contato'), ['id' => 1, 'status' => 1])->assertRedirect(route('representante.contatos.view'));
    }

    /** @test 
     * 
     * Representante Comercial pode ativar/desativar contato existente. 
     * 
     * TODO - Após modificar a forma de guardar os comprovantes (anexos), adicionar
     * "Storage::fake('public'); ... Storage::disk('public')->assertExists('file/' . $file->hashName());"
     * para simular o registro do anexo e verificar se e;e é salvo corretamente.
    */
    public function insert_new_address_for_representante()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        
        $this->get(route('representante.inserir-endereco.view'))->assertOk();
        
        // Checa criação de soliticação para mudança de endereço
        $this->post(route('representante.inserir-endereco'), ['cep' => '000-000', 'bairro' => 'Bairro Teste', 'logradouro' => 'Rua Teste', 'numero' => 999, 'complemento' => null, 'estado' => 'SP', 'municipio' => 'São Paulo', 'crimage' => $file = UploadedFile::fake()->image('random.jpg')])->assertRedirect(route('representante.enderecos.view'));

        // Checa se a solicitação de mudança de endereço foi registrado no banco de dados
        $this->assertEquals(RepresentanteEndereco::count(), 1);
    }

    /** @test 
     * 
     * Representante Comercial pode resetar senha.
    */
    public function reset_password_for_representante()
    {
        Mail::fake();

        $representante = factory('App\Representante')->create();

        $this->get(route('representante.password.request'))->assertOk();

        $this->post(route('representante.password.email'), ['cpf_cnpj' => $representante['cpf_cnpj']]);

        // Checa se e-mail contendo link para resetar senha foi enviado
        Mail::assertSent(RepresentanteResetPasswordMail::class);
    }

    /** @test 
     * 
     * Representante Comercial pode mudar e-mail.
    */
    public function modify_email_for_representante()
    {
        // os emails devem ser iguais ao do GerentiMock
        $representante = factory('App\Representante')->create([
            'email' => 'desenvolvimento@core-sp.org.br'
        ]);
        $emailNovo = 'desenvolvimento2@core-sp.org.br';

        $this->get(route('representante.email.reset.view'))->assertOk();

        $this->post(route('representante.email.reset'), ['cpf_cnpj' => $representante->cpf_cnpj, 'registro_core' => $representante->registro_core, 'email_antigo' => $representante->email, 'email_novo' => $emailNovo]);

        // Checa se o e-mail do representante foi atualizado no banco de dados
        $this->assertDatabaseHas('representantes', [
            'email' => $emailNovo
        ]);
    }

    /** @test 
     * 
     * Erro ao não informar dados obrigatórios ao cadastrar representante.
    */
    public function register_missing_mandatory_input_cannot_be_created()
    {
        $dados = factory('App\Representante')->raw(['password' => 'teste102030']);

        $this->get(route('representante.cadastro'))->assertOk();

        // Checa se erros nos campos foram retornados
        $this->post(route('representante.cadastro.submit'), ['cpfCnpj' => null, 'registro_core' => null, 'email' => null, 'password' => null, 'password_confirmation' => null, 'checkbox-tdu' => null])
            ->assertSessionHasErrors([
                'cpfCnpj',
                'registro_core',
                'email',
                'password',
                'checkbox-tdu'
            ]);
    }

    /** @test 
     * 
     * Erro ao falhar na confirmação da senha no cadastro do representante.
    */
    public function register_without_confirmed_password_cannot_be_created()
    {
        $dados = factory('App\Representante')->raw(['password' => 'teste102030']);

        $this->get(route('representante.cadastro'))->assertOk();

        // Checa se erro no campo foi retornado
        $this->post(route('representante.cadastro.submit'), ['cpfCnpj' => $dados['cpf_cnpj'], 'registro_core' => $dados['registro_core'], 'email' => $dados['email'], 'password' => $dados['password'], 'password_confirmation' => null, 'checkbox-tdu' => 'on'])
            ->assertSessionHasErrors([
                'password'
            ]);
    }

    /** @test 
     * 
     * Erro ao tentar cadastrar representante com CPF/CNPJ já existente.
    */
    public function register_with_already_existing_cpf_cnpj_cannot_be_created()
    {
        $representante = factory('App\Representante')->create();

        // Checa se erro no campo foi retornado
        $this->post(route('representante.cadastro.submit'), ['cpfCnpj' => $representante['cpf_cnpj'], 'registro_core' => $representante['registro_core'], 'email' => $representante['email'], 'password' => $representante['password'], 'password_confirmation' => $representante['password'], 'checkbox-tdu' => 'on'])
            ->assertSessionHasErrors([
                'cpfCnpj'
            ]);
    }

    /** @test 
     * 
     * Erro ao tentar cadastrar representante com CPF/CNPJ já existente.
    */
    public function register_with_nonexistent_representante_on_gerenti_cannot_be_created()
    {
        // Editar o GerentiMock para não encontrar o representante
        $representante = factory('App\Representante')->raw(['password' => 'teste102030']);

        $this->post(route('representante.cadastro.submit'), ['cpfCnpj' => '04377629042', 'registro_core' => $representante['registro_core'], 'email' => $representante['email'], 'password' => $representante['password'], 'password_confirmation' => $representante['password'], 'checkbox-tdu' => 'on'])
            ->assertRedirect(route('representante.cadastro'));
            
        // Checa se mensagem do erro foi retornada
        $this->assertEquals(session('message'), 'O cadastro informado não está corretamente inscrito no Core-SP. Por favor, verifique as informações inseridas.');
    }

    /** @test 
     * 
     * Erro ao tentar cadastrar representante com e-mail inconsistente com o cadastrado no GERENTI.
    */
    public function register_with_nonexistent_email_on_gerenti_cannot_be_created()
    {
        $representante = factory('App\Representante')->raw(['password' => 'teste102030']);

        $this->post(route('representante.cadastro.submit'), ['cpfCnpj' => $representante['cpf_cnpj'], 'registro_core' => $representante['registro_core'], 'email' => 'email@email.com', 'password' => $representante['password'], 'password_confirmation' => $representante['password'], 'checkbox-tdu' => 'on'])
            ->assertRedirect(route('representante.cadastro'));
            
        // Checa se mensagem do erro foi retornada
        $this->assertEquals(session('message'), 'O email informado não corresponde ao cadastro informado. Por favor, insira o email correto.');
    }

    /** @test 
     * 
     * Erro ao não informar dados obrigatórios ao inserir novo contato.
    */
    public function insert_new_contact_with_missing_mandatory_input_cannot_be_created()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        // Checa se erro no campo foi retornado
        $this->post(route('representante.inserir-ou-alterar-contato'), ['tipo' => null, 'contato' => null])
            ->assertSessionHasErrors([
                'contato'
            ]);
    }

    /** @test 
     * 
     * Erro ao não informar dados obrigatórios ao inserir novo endereço. 
    */
    public function insert_new_address_with_missing_mandatory_input_cannot_be_created()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $this->post(route('representante.inserir-endereco'), [
            'cep' => null, 
            'bairro' => null, 
            'logradouro' => null, 
            'numero' => null, 
            'complemento' => null, 
            'estado' => null, 
            'municipio' => null, 
            'crimage' => null
            ])
            ->assertSessionHasErrors([
                'cep',
                'bairro',
                'logradouro',
                'numero',
                'estado',
                'municipio',
                'crimage'
            ]);

        // Checa se a solicitação de mudança de endereço não foi registrado no banco de dados
        $this->assertEquals(RepresentanteEndereco::count(), 0);
    }

    /** @test 
     * 
     * Representante Comercial não pode logar na área restrita do Portal com a senha errada.
    */
    public function cannot_login_on_restrict_area_with_wrong_password()
    {
        $representante = factory('App\Representante')->create();

        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => '1234']);

        // Checa se mensagem do erro foi retornada
        $this->assertEquals(session('message'), 'Login inválido.');
    }

    /** @test 
     * 
     * Abas da área restrita não são acessíveis sem o login.
    */
    public function cannot_access_tabs_on_restrict_area_without_login()
    {
        // exige ao acessar a aba bdo
        factory('App\Regional')->create([
            'regional' => 'SÃO PAULO',
        ]);

        // Checa acesso a página principal é bloqueado e redirecionado para tela de login
        $this->get(route('representante.dashboard'))->assertRedirect(route('representante.login'));

        // Checa acesso a aba "Dados Gerais" é bloqueado e redirecionado para tela de login
        $this->get(route('representante.dados-gerais'))->assertRedirect(route('representante.login'));

        // Checa acesso a aba "Contatos" é bloqueado e redirecionado para tela de login
        $this->get(route('representante.contatos.view'))->assertRedirect(route('representante.login'));

        // Checa acesso a aba "End. de Correspondência" é bloqueado e redirecionado para tela de login
        $this->get(route('representante.enderecos.view'))->assertRedirect(route('representante.login'));

        // Checa acesso a aba "Situação Financeira" é bloqueado e redirecionado para tela de login
        $this->get(route('representante.lista-cobrancas'))->assertRedirect(route('representante.login'));
        
        // Checa acesso a aba "Oportunidades" é bloqueado e redirecionado para tela de login
        $this->get(route('representante.bdo'))->assertRedirect(route('representante.login'));

        // Checa acesso a aba "Solicitação de Cédula" é bloqueado e redirecionado para tela de login
        $this->get(route('representante.solicitarCedulaView'))->assertRedirect(route('representante.login'));
    }

    /** @test 
     * 
     * Senha não pode ser reconfigurada quando CPF/CNPJ não está registrado no Portal.
    */
    public function cannot_reset_password_for_representante_with_nonexistent_cpf_cnpj()
    {
        $this->post(route('representante.password.email'), ['cpf_cnpj' => '04377629042']);

        // Checa se mensagem do erro foi retornada
        $this->assertEquals(session('message'), 'CPF ou CNPJ não cadastrado.');
    }

    /** @test 
     * 
     *  E-mail não pode ser modificado quando dados obrigatórios não são informados.
    */
    public function cannot_modify_email_for_representante_with_missing_mandatory_input()
    {
        // Checa se erros nos campos foram retornados
        $this->post(route('representante.email.reset'), ['cpf_cnpj' => null, 'registro_core' => null, 'email_antigo' => null, 'email_novo' => null])
            ->assertSessionHasErrors([
                'cpf_cnpj',
                'registro_core',
                'email_antigo',
                'email_novo'
            ]);
    }

    /** @test 
     * 
     * Visualiza a opção de outras oportunidades no bdo.
     * 
    */
    public function view_option_outras_oportunidades()
    {
        factory('App\Regional')->create([
            'regional' => 'SÃO PAULO',
        ]);
        $bdo = factory('App\BdoOportunidade', 5)->create([
            'segmento' => 'Alimentício',
        ]);
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.bdo'))->assertSee('Outras oportunidades');
    }
    
    /** @test 
     *
     * Visualiza as oportunidades no bdo se possuir segmento.
     * Tem de visualizar qual segmento está no GerentiMock 
    */
    public function view_alert_bdo_if_has_segment()
    {
        factory('App\Regional')->create([
            'regional' => 'SÃO PAULO',
        ]);
        $bdo = factory('App\BdoOportunidade', 5)->create([
            'segmento' => 'Alimentício',
        ]);
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.bdo'))->assertSee('Foram encontradas');
    }
     
    /** @test 
     * 
     * Não visualiza as oportunidades no bdo se não possuir segmento. 
     * Tem de comentar o array do segmento no GerentiMock
    */
    public function cannot_view_alert_bdo_if_hasnt_segment()
    {
        // Tem de comentar o array do segmento no GerentiMock
        factory('App\Regional')->create([
            'regional' => 'SÃO PAULO',
        ]);
        $bdo = factory('App\BdoOportunidade')->create([
            'segmento' => 'Alimentício',
        ]);
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.bdo'))->assertDontSee($bdo->segmento);
    }

    /** @test 
    *  
    * Não visualiza as oportunidades no bdo se não possuir oportunidade do mesmo segmento. 
    */
    public function cannot_view_alert_bdo_if_hasnt_bdo()
    {
        factory('App\Regional')->create([
            'regional' => 'SÃO PAULO',
        ]);
        factory('App\BdoOportunidade', 5)->create();
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.bdo'))->assertSee('Não foi encontrada');
    }

    /** @test 
     * 
     * Não visualiza as oportunidades no bdo se não possuir oportunidade do mesmo segmento com status em andamento. 
    */
    public function cannot_view_alert_bdo_if_hasnt_bdo_status_em_andamento()
    {
        factory('App\Regional')->create([
            'regional' => 'SÃO PAULO',
        ]);
        $bdo = factory('App\BdoOportunidade', 5)->create([
            'segmento' => 'Alimentício',
            'status' => 'Expirado'
        ]);
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.bdo'))->assertSee('Não foi encontrada');
    }

    /** @test 
     *  
    * Não visualiza o aviso do bdo se não possuir oportunidade e segmento. 
    */
    public function cannot_view_alert_bdo_if_hasnt_bdo_and_segmento()
    {
        // Comentar o segmento no GerentiMock
        factory('App\Regional')->create([
            'regional' => 'SÃO PAULO',
        ]);
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.bdo'))->assertDontSee('Alimentício');
    }

    /** @test 
     * 
     * Visualiza o aviso que está ativado.
     * 
    */
    public function view_aviso_with_status_ativado()
    {
        $aviso = factory('App\Aviso')->create([
            'status' => 'Ativado',
            'idusuario' => factory('App\User')
        ]);
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.dashboard'))->assertSee($aviso->titulo);
    }

    /** @test 
     * 
     * Não visualiza o aviso que está desativado.
     * 
    */
    public function cannot_view_aviso_with_status_desativado()
    {
        $aviso = factory('App\Aviso')->create();
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.dashboard'))->assertDontSee($aviso->titulo);
    }

    /** @test 
     *
     * Não visualiza o aviso que não existe.
     * 
    */
    public function cannot_view_aviso_uncreated()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.dashboard'))->assertViewMissing('aviso');
    }

    /** @test 
     * 
     * Visualiza o aviso ativado em todas as rotas do RC após logon.
     * 
    */
    public function view_aviso_ativado_in_all_routes_get_after_logon()
    {
        $aviso = factory('App\Aviso')->create([
            'status' => 'Ativado',
            'idusuario' => factory('App\User')
        ]);
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.dashboard'))->assertSee($aviso->titulo);
        $this->get(route('representante.dados-gerais'))->assertSee($aviso->titulo);
        $this->get(route('representante.contatos.view'))->assertSee($aviso->titulo);
        $this->get(route('representante.enderecos.view'))->assertSee($aviso->titulo);
        $this->get(route('representante.inserir-ou-alterar-contato.view'))->assertSee($aviso->titulo);
        $this->get(route('representante.inserir-endereco.view'))->assertSee($aviso->titulo);
        $this->get(route('representante.lista-cobrancas'))->assertSee($aviso->titulo);
        factory('App\Regional')->create([
            'regional' => 'SÃO PAULO',
        ]);
        factory('App\BdoOportunidade')->create();
        $this->get(route('representante.bdo'))->assertSee($aviso->titulo);
        $this->get(route('representante.solicitarCedulaView'))->assertSee($aviso->titulo);
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertSee($aviso->titulo);
    }

    /** @test 
     *
     * Não visualiza o aviso desativado em todas as rotas do RC após logon.
     * 
    */
    public function cannot_view_aviso_desativado_in_all_routes_get_after_logon()
    {
        $aviso = factory('App\Aviso')->create([
            'idusuario' => factory('App\User')
        ]);
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.dashboard'))->assertDontSee($aviso->titulo);
        $this->get(route('representante.dados-gerais'))->assertDontSee($aviso->titulo);
        $this->get(route('representante.contatos.view'))->assertDontSee($aviso->titulo);
        $this->get(route('representante.enderecos.view'))->assertDontSee($aviso->titulo);
        $this->get(route('representante.inserir-ou-alterar-contato.view'))->assertDontSee($aviso->titulo);
        $this->get(route('representante.inserir-endereco.view'))->assertDontSee($aviso->titulo);
        $this->get(route('representante.lista-cobrancas'))->assertDontSee($aviso->titulo);
        factory('App\Regional')->create([
            'regional' => 'SÃO PAULO',
        ]);
        factory('App\BdoOportunidade')->create();
        $this->get(route('representante.bdo'))->assertDontSee($aviso->titulo);
        $this->get(route('representante.solicitarCedulaView'))->assertDontSee($aviso->titulo);
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertDontSee($aviso->titulo);
    }

    /** @test 
     * 
     * Representante que não está em dia não pode solicitar cédula pela primeira vez
    */
    public function cannot_insert_new_solicitacao_cedula_hasnt_em_dia()
    {
        // Editar GerentiMock gerentiStatus($ass_id) para situação sem ser 'em dia'
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        factory('App\Regional')->create([
            'regional' => 'SÃO PAULO - Alameda Santos'
        ]);
        $representante = factory('App\Representante')->create();
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertStatus(302);
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)->assertStatus(302);
        $this->assertDatabaseMissing('solicitacoes_cedulas', [
            'idrepresentante' => $representante->id,
            'bairro' => $cedula['bairro'], 
            'logradouro' => $cedula['logradouro'],
            'numero' => $cedula['numero']
        ]);
    }

    /** @test 
     * 
     * Representante que está em dia, mas já tem cédula em andamento não pode solicitar cédula
    */
    public function cannot_insert_new_solicitacao_cedula_has_solicitacao_em_andamento()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        factory('App\Regional')->create([
            'regional' => 'SÃO PAULO - Alameda Santos'
        ]);
        $representante = factory('App\Representante')->create();
        $cedula = factory('App\SolicitaCedula')->create([
            'idrepresentante' => $representante->id
        ]);
        $cedulaNova = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertStatus(302);
        $this->post(route('representante.inserirSolicitarCedula'), $cedulaNova)->assertStatus(302);
        $this->assertDatabaseMissing('solicitacoes_cedulas', [
            'idrepresentante' => $representante->id,
            'bairro' => $cedulaNova['bairro'], 
            'logradouro' => $cedulaNova['logradouro'],
            'numero' => $cedulaNova['numero']
        ]);
    }

    /** @test 
     * 
     * Representante que está em dia, já tem cédulas solicitadas, mas nenhuma em andamento, pode solicitar cédula
    */
    public function insert_new_solicitacao_cedula_has_cedulas_but_hasnt_em_andamento()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        factory('App\Regional')->create([
            'regional' => 'SÃO PAULO - Alameda Santos'
        ]);
        $representante = factory('App\Representante')->create();
        factory('App\SolicitaCedula', 5)->create([
            'idrepresentante' => $representante->id,
            'status' => 'Aceito'
        ]);
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)->assertStatus(302);
        $this->assertDatabaseHas('solicitacoes_cedulas', [
            'idrepresentante' => $representante->id,
            'bairro' => $cedula['bairro'], 
            'logradouro' => $cedula['logradouro'],
            'numero' => $cedula['numero']
        ]);
    }

    /** @test 
     * 
     * Não pode criar solicitação de cédula com o formulário vazio
    */
    public function cannot_insert_new_solicitacao_cedula_with_missing_mandatory_input()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $dados = [
            'nome' => '',
            'cpf' => '',
            'rg' => '',
            'cep' => null, 
            'bairro' => null, 
            'logradouro' => null, 
            'numero' => null, 
            'estado' => null, 
            'municipio' => null
        ];
        $this->post(route('representante.inserirSolicitarCedula'), $dados)
        ->assertSessionHasErrors([
            'nome',
            'cpf',
            'rg',
            'cep', 
            'bairro', 
            'logradouro', 
            'numero', 
            'estado', 
            'municipio'
        ]);
        $this->assertDatabaseMissing('solicitacoes_cedulas', [
            'idrepresentante' => $representante->id
        ]);
    }

    /** @test 
     * 
     * Erro ao não informar alguns dados obrigatórios ao inserir nova solicitação de cédula.
    */
    public function cannot_insert_new_solicitacao_cedula_with_missing_mandatory_input_cep_and_numero()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create();
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'cep' => null,
            'numero' => null
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)
            ->assertSessionHasErrors([
                'cep', 
                'numero'
            ]);
    }

    /** @test 
     * 
     * Erro ao não informar o nome ao inserir nova solicitação de cédula.
    */
    public function cannot_insert_new_solicitacao_cedula_with_missing_mandatory_input_nome()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        // Colocar um CNPJ, pois não carrega dados como nome
        $representante = factory('App\Representante')->create([
            'cpf_cnpj' => '30735253000174'
        ]);
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'nome' => null
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)
            ->assertSessionHasErrors([
                'nome'
            ]);
    }

    /** @test 
     * 
     * Erro ao não informar rg ao inserir nova solicitação de cédula.
    */
    public function cannot_insert_new_solicitacao_cedula_with_missing_mandatory_input_rg()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        // Colocar um CNPJ, pois não carrega dados como rg
        $representante = factory('App\Representante')->create([
            'cpf_cnpj' => '30735253000174'
        ]);
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'rg' => null
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)
            ->assertSessionHasErrors([
                'rg'
            ]);
    }

    /** @test 
     * 
     * Erro ao não informar cpf ao inserir nova solicitação de cédula.
    */
    public function cannot_insert_new_solicitacao_cedula_with_missing_mandatory_input_cpf()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create();
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'cpf' => null
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)
            ->assertSessionHasErrors([
                'cpf'
            ]);
    }

    /** @test 
     * 
     * Deve criar a solicitação de cédula ao não informar o complemento
    */
    public function insert_new_solicitacao_cedula_with_missing_complemento_input()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
        ]);
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('solicitacoes_cedulas', [
            'rg' => $cedula['rg'],
            'cep' => $cedula['cep']
        ]);
    }

    /** @test 
     * 
     * Deve trazer nome, rg e cpf do gerenti e do bd se for PF ao solicitar a cedula
    */
    public function fill_nome_rg_cpf_if_pf_in_solicitacao_cedula()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $response = $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'nome' => $response->getOriginalContent()->getData()['nome'],
            'rg' => apenasNumerosLetras($response->getOriginalContent()->getData()['rg']),
            'cpf' => apenasNumeros($response->getOriginalContent()->getData()['cpf'])
        ]);
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('solicitacoes_cedulas', [
            'cpf' => null,
            'rg' => $cedula['rg'],
            'nome' => null,
            'cep' => $cedula['cep']
        ]);
    }

    /** @test 
     * 
     * Não deve preencher os campos nome, rg e cpf se for PJ ao solicitar a cedula
    */
    public function if_pj_inputs_nome_rg_cpf_empty_in_solicitacao_cedula()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create([
            'cpf_cnpj' => '30735253000174'
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $response = $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'nome' => $response->getOriginalContent()->getData()['nome'],
            'rg' => apenasNumerosLetras($response->getOriginalContent()->getData()['rg']),
            'cpf' => apenasNumeros($response->getOriginalContent()->getData()['cpf'])
        ]);
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)->assertSessionHasErrors([
            'nome',
            'rg',
            'cpf'
        ]);
    }

    /** @test 
     * 
     * Deve listar solicitações de cédula do representante logado
    */
    public function list_solicitacoes_cedula_only_representante_authenticated()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create();
        $cedula = factory('App\SolicitaCedula')->create([
            'idrepresentante' => $representante->id
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))
        ->assertSeeText($cedula->cep);
    }

    /** @test 
     * 
     * Não deve listar solicitações de cédula de representante diferente do que está logado
    */
    public function cannot_list_solicitacoes_cedula_representante_different_authenticated()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create();
        $fake = factory('App\Representante')->create([
            'cpf_cnpj' => '65736926083'
        ]);
        $cedula = factory('App\SolicitaCedula')->create([
            'idrepresentante' => $fake->id
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))
        ->assertDontSeeText($cedula->cep);
    }

    /** @test 
     * 
     * Representante em dia pode solicitar cédula pela primeira vez
    */
    public function insert_new_solicitacao_cedula()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        factory('App\Regional')->create([
            'regional' => 'SÃO PAULO - Alameda Santos'
        ]);
        $representante = factory('App\Representante')->create();
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)
        ->assertRedirect(route('representante.solicitarCedulaView'));
        $this->assertDatabaseHas('solicitacoes_cedulas', [
            'idrepresentante' => $representante->id,
            'rg' => $cedula['rg'],
            'bairro' => $cedula['bairro'], 
            'logradouro' => $cedula['logradouro'],
            'numero' => $cedula['numero']
        ]);
    }
}