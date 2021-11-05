<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\PreRepresentante;
use Illuminate\Support\Facades\Mail;
use App\Mail\CadastroPreRepresentanteMail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PreRepresentanteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test 
     * 
     * Não pode criar um registro com formulário em branco.
    */
    public function cannot_register_without_mandatory_inputs()
    {
        $this->get(route('prerepresentante.cadastro'))->assertOk();
        $this->post(route('prerepresentante.cadastro.submit'), [
            'cpf_cnpj' => null, 
            'nome' => null,
            'email' => '', 
            'password' => '', 
            'password_confirmation' => null, 
            'checkbox-tdu' => ''
        ])
        ->assertSessionHasErrors([
            'cpf_cnpj',
            'nome',
            'email',
            'password',
            'checkbox-tdu'
        ]);
    }

    /** @test 
     * 
     * Não pode criar um registro faltando cpf/cnpj.
    */
    public function cannot_register_without_cpfcnpj_input()
    {
        $dados = factory('App\PreRepresentante')->raw([
            'cpf_cnpj' => null, 
            'checkbox-tdu' => 'on',
            'password' => 'Teste102030',
            'password_confirmation' => 'Teste102030'
        ]);
        $this->get(route('prerepresentante.cadastro'))->assertOk();
        $this->post(route('prerepresentante.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'cpf_cnpj'
        ]);

        $this->assertDatabaseMissing('pre_representantes', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test 
     * 
     * Não pode criar um registro faltando nome.
    */
    public function cannot_register_without_nome_input()
    {
        $dados = factory('App\PreRepresentante')->raw([
            'nome' => '', 
            'checkbox-tdu' => 'on',
            'password' => 'Teste102030',
            'password_confirmation' => 'Teste102030'
        ]);
        $this->get(route('prerepresentante.cadastro'))->assertOk();
        $this->post(route('prerepresentante.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'nome'
        ]);

        $this->assertDatabaseMissing('pre_representantes', [
            'email' => $dados['email']
        ]);
    }

    /** @test 
     * 
     * Não pode criar um registro faltando email.
    */
    public function cannot_register_without_email_input()
    {
        $dados = factory('App\PreRepresentante')->raw([
            'email' => '', 
            'checkbox-tdu' => 'on',
            'password' => 'Teste102030',
            'password_confirmation' => 'Teste102030'
        ]);
        $this->get(route('prerepresentante.cadastro'))->assertOk();
        $this->post(route('prerepresentante.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'email'
        ]);

        $this->assertDatabaseMissing('pre_representantes', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test 
     * 
     * Não pode criar um registro faltando concordar com os termos.
    */
    public function cannot_register_without_checkbox_input()
    {
        $dados = factory('App\PreRepresentante')->raw([
            'checkbox-tdu' => '', 
            'password' => 'Teste102030',
            'password_confirmation' => 'Teste102030'
        ]);
        $this->get(route('prerepresentante.cadastro'))->assertOk();
        $this->post(route('prerepresentante.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'checkbox-tdu'
        ]);

        $this->assertDatabaseMissing('pre_representantes', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test 
     * 
     * Não pode criar um registro faltando com a senha sem os requisitos mínimos.
    */
    public function cannot_register_with_password_wrong()
    {
        // Faltando letra maiuscula e mais um caracter
        $dados = factory('App\PreRepresentante')->raw([
            'checkbox-tdu' => 'on',
            'password' => 'teste10',
            'password_confirmation' => 'teste10'
        ]);
        $this->get(route('prerepresentante.cadastro'))->assertOk();
        $this->post(route('prerepresentante.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'password'
        ]);

        $this->assertDatabaseMissing('pre_representantes', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test 
     * 
     * Não pode criar um registro faltando com a senha e confirmação de senha diferentes.
    */
    public function cannot_register_with_password_and_confirmation_differents()
    {
        $dados = factory('App\PreRepresentante')->raw([
            'checkbox-tdu' => 'on',
            'password' => 'Teste102030',
            'password_confirmation' => 'teste102030'
        ]);
        $this->get(route('prerepresentante.cadastro'))->assertOk();
        $this->post(route('prerepresentante.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'password_confirmation'
        ]);

        $this->assertDatabaseMissing('pre_representantes', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test 
     * 
     * Não pode criar um registro com padrão de email errado.
    */
    public function cannot_register_with_email_wrong()
    {
        $dados = factory('App\PreRepresentante')->raw([
            'email' => 'gfgfgf.com', 
            'checkbox-tdu' => 'on',
            'password' => 'Teste102030',
            'password_confirmation' => 'Teste102030'
        ]);
        $this->get(route('prerepresentante.cadastro'))->assertOk();
        $this->post(route('prerepresentante.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'email'
        ]);

        $this->assertDatabaseMissing('pre_representantes', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test 
     * 
     * Não pode criar um registro se o cpf/cnpj já existirem no banco dos Representantes.
    */
    public function cannot_register_if_exist_cpfcnpj_in_table_representantes()
    {
        factory('App\Representante')->create([
            'cpf_cnpj' => '36982299007'
        ]);
        $dados = factory('App\PreRepresentante')->raw([
            'cpf_cnpj' => '36982299007',
            'checkbox-tdu' => 'on',
            'password' => 'Teste102030',
            'password_confirmation' => 'Teste102030'
        ]);
        $this->get(route('prerepresentante.cadastro'))->assertOk();
        $this->post(route('prerepresentante.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'cpf_cnpj'
        ]);
        $this->assertDatabaseMissing('pre_representantes', [
            'cpf_cnpj' => $dados['cpf_cnpj']
        ]);
    }

    /** @test 
     * 
     * Não pode criar um registro se o cpf/cnpj já existirem no banco dos Pré Representantes.
    */
    public function cannot_register_if_exist_cpfcnpj()
    {
        $pre = factory('App\PreRepresentante')->create();
        $dados = factory('App\PreRepresentante')->raw([
            'cpf_cnpj' => $pre->cpf_cnpj,
            'checkbox-tdu' => 'on',
            'password' => 'Teste102030',
            'password_confirmation' => 'Teste102030'
        ]);
        $this->get(route('prerepresentante.cadastro'))->assertOk();
        $this->post(route('prerepresentante.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'cpf_cnpj'
        ]);
        $this->assertDatabaseMissing('pre_representantes', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test 
     * 
     * Pode se registrar com todas as informações certas.
    */
    public function register_new_prerepresentante()
    {
        Mail::fake();
        $dados = factory('App\PreRepresentante')->raw();
        $this->get(route('prerepresentante.cadastro'))->assertOk();
        $this->post(route('prerepresentante.cadastro.submit'), [
            'cpf_cnpj' => $dados['cpf_cnpj'], 
            'nome' => $dados['nome'],
            'email' => $dados['email'], 
            'password' => $dados['password'], 
            'password_confirmation' => $dados['password'], 
            'checkbox-tdu' => 'on'
        ]);

        // Checa se o e-mail para confirmação do e-mail foi enviado
        Mail::assertQueued(CadastroPreRepresentanteMail::class);

        // Checa se foi cadastrado no banco de dados. Campo "ativo" deve ser 0 até confirmar seu e-mail
        $this->assertEquals(PreRepresentante::count(), 1);
        $this->assertEquals(PreRepresentante::first()->ativo, 0);

        // Checa se após acessar o link de confirmação, o campo "ativo" é atualizado para 1
        $this->get(route('prerepresentante.verifica-email', PreRepresentante::first()->verify_token));
        $this->assertEquals(PreRepresentante::first()->ativo, 1);
    }

    /** @test 
     * 
     * Pre Representante Comercial pode logar na área restrita do Portal.
    */
    public function login_on_pre_registro()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $dados = [
            'login' => $prerep['cpf_cnpj'],
            'password' => $prerep['password']
        ];
        $this->get(route('prerepresentante.login'))->assertOk();
        $this->post(route('prerepresentante.login.submit'), $dados)
        ->assertRedirect(route('prerepresentante.dashboard'));
    }

    /** @test 
     * 
     * Pre Representante Comercial não pode logar se não está cadastrado.
    */
    public function cannot_login_on_pre_registro()
    {
        $prerep = factory('App\PreRepresentante')->raw();
        $dados = [
            'login' => $prerep['cpf_cnpj'],
            'password' => $prerep['password']
        ];
        $this->get(route('prerepresentante.login'))->assertOk();
        $this->post(route('prerepresentante.login.submit'), $dados)
        ->assertRedirect(route('prerepresentante.login'));
    }

    /** @test 
     * 
     * Pre Representante Comercial não pode logar se senha está errada.
    */
    public function cannot_login_on_pre_registro_whit_password_wrong()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $dados = [
            'login' => $prerep['cpf_cnpj'],
            'password' => 'teste102030'
        ];
        $this->get(route('prerepresentante.login'))->assertOk();
        $this->post(route('prerepresentante.login.submit'), $dados)
        ->assertSessionHasErrors([
            'password'
        ]);
    }

    // /** @test 
    //  * 
    //  * Representante Comercial pode acessar todas as abas na área restrita do Portal.
    // */
    // public function access_tabs_on_restrict_area()
    // {
    //     $representante = factory('App\Representante')->create();
    //     $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

    //     // Checa acesso a página principal
    //     $this->get(route('representante.dashboard'))->assertOk();

    //     // Checa acesso a aba "Dados Gerais"
    //     $this->get(route('representante.dados-gerais'))->assertOk();

    //     // Checa acesso a aba "Contatos"
    //     $this->get(route('representante.contatos.view'))->assertOk();

    //     // Checa acesso a aba "End. de Correspondência"
    //     $this->get(route('representante.enderecos.view'))->assertOk();

    //     // Checa acesso a aba "Situação Financeira"
    //     $this->get(route('representante.lista-cobrancas'))->assertOk();
    // }

    /** @test 
     * 
     * Pre Representante Comercial pode resetar senha.
    */
    public function reset_password_for_prerepresentante()
    {
        Mail::fake();
        $prerep = factory('App\PreRepresentante')->create();
        $this->get(route('prerepresentante.password.request'))->assertOk();
        $this->post(route('prerepresentante.password.email'), [
            'cpf_cnpj' => $prerep['cpf_cnpj']
        ]);
        // Notification::assertSentTo($prerep, ResetPassword::class);
        // Mail::assertSent(\MailMessage::class);
    }

    // /** @test 
    //  * 
    //  * Representante Comercial pode mudar e-mail.
    // */
    // public function modify_email_for_representante()
    // {
    //     $representante = factory('App\Representante')->create();
    //     $emailNovo = 'novo@core-sp.org.br';

    //     $this->get(route('representante.email.reset.view'))->assertOk();

    //     $this->post(route('representante.email.reset'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'registro_core' => $representante['registro_core'], 'email_antigo' => $representante['email'], 'email_novo' => $emailNovo]);

    //     // Checa se o e-mail do representante foi atualizado no banco de dados
    //     $this->assertEquals(Representante::first()->email, $emailNovo);
    // }

    // /** @test 
    //  * 
    //  * Erro ao não informar dados obrigatórios ao cadastrar representante.
    // */
    // public function register_missing_mandatory_input_cannot_be_created()
    // {
    //     $dados = factory('App\Representante')->raw(['password' => 'teste102030']);

    //     $this->get(route('representante.cadastro'))->assertOk();

    //     // Checa se erros nos campos foram retornados
    //     $this->post(route('representante.cadastro.submit'), ['cpfCnpj' => null, 'registro_core' => null, 'email' => null, 'password' => null, 'password_confirmation' => null, 'checkbox-tdu' => null])
    //         ->assertSessionHasErrors([
    //             'cpfCnpj',
    //             'registro_core',
    //             'email',
    //             'password',
    //             'checkbox-tdu'
    //         ]);
    // }

    // /** @test 
    //  * 
    //  * Erro ao falhar na confirmação da senha no cadastro do representante.
    // */
    // public function register_without_confirmed_password_cannot_be_created()
    // {
    //     $dados = factory('App\Representante')->raw(['password' => 'teste102030']);

    //     $this->get(route('representante.cadastro'))->assertOk();

    //     // Checa se erro no campo foi retornado
    //     $this->post(route('representante.cadastro.submit'), ['cpfCnpj' => $dados['cpf_cnpj'], 'registro_core' => $dados['registro_core'], 'email' => $dados['email'], 'password' => $dados['password'], 'password_confirmation' => null, 'checkbox-tdu' => 'on'])
    //         ->assertSessionHasErrors([
    //             'password'
    //         ]);
    // }

    // /** @test 
    //  * 
    //  * Erro ao tentar cadastrar representante com CPF/CNPJ já existente.
    // */
    // public function register_with_already_existing_cpf_cnpj_cannot_be_created()
    // {
    //     $representante = factory('App\Representante')->create();

    //     // Checa se erro no campo foi retornado
    //     $this->post(route('representante.cadastro.submit'), ['cpfCnpj' => $representante['cpf_cnpj'], 'registro_core' => $representante['registro_core'], 'email' => $representante['email'], 'password' => $representante['password'], 'password_confirmation' => $representante['password'], 'checkbox-tdu' => 'on'])
    //         ->assertSessionHasErrors([
    //             'cpfCnpj'
    //         ]);
    // }

    // /** @test 
    //  * 
    //  * Erro ao tentar cadastrar representante com CPF/CNPJ já existente.
    // */
    // public function register_with_nonexistent_representante_on_gerenti_cannot_be_created()
    // {
    //     $representante = factory('App\Representante')->raw(['password' => 'teste102030']);

    //     $this->post(route('representante.cadastro.submit'), ['cpfCnpj' => '04377629042', 'registro_core' => $representante['registro_core'], 'email' => $representante['email'], 'password' => $representante['password'], 'password_confirmation' => $representante['password'], 'checkbox-tdu' => 'on'])
    //         ->assertRedirect(route('representante.cadastro'));
            
    //     // Checa se mensagem do erro foi retornada
    //     $this->assertEquals(session('message'), 'O cadastro informado não está corretamente inscrito no Core-SP. Por favor, verifique as informações inseridas.');
    // }

    // /** @test 
    //  * 
    //  * Erro ao tentar cadastrar representante com e-mail inconsistente com o cadastrado no GERENTI.
    // */
    // public function register_with_nonexistent_email_on_gerenti_cannot_be_created()
    // {
    //     $representante = factory('App\Representante')->raw(['password' => 'teste102030']);

    //     $this->post(route('representante.cadastro.submit'), ['cpfCnpj' => $representante['cpf_cnpj'], 'registro_core' => $representante['registro_core'], 'email' => 'email@email.com', 'password' => $representante['password'], 'password_confirmation' => $representante['password'], 'checkbox-tdu' => 'on'])
    //         ->assertRedirect(route('representante.cadastro'));
            
    //     // Checa se mensagem do erro foi retornada
    //     $this->assertEquals(session('message'), 'O email informado não corresponde ao cadastro informado. Por favor, insira o email correto.');
    // }

    // /** @test 
    //  * 
    //  * Erro ao não informar dados obrigatórios ao inserir novo contato.
    // */
    // public function insert_new_contact_with_missing_mandatory_input_cannot_be_created()
    // {
    //     $representante = factory('App\Representante')->create();
    //     $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

    //     // Checa se erro no campo foi retornado
    //     $this->post(route('representante.inserir-ou-alterar-contato'), ['tipo' => null, 'contato' => null])
    //         ->assertSessionHasErrors([
    //             'contato'
    //         ]);
    // }

    // /** @test 
    //  * 
    //  * Erro ao não informar dados obrigatórios ao inserir novo endereço. 
    // */
    // public function insert_new_address_with_missing_mandatory_input_cannot_be_created()
    // {
    //     $representante = factory('App\Representante')->create();
    //     $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

    //     $this->post(route('representante.inserir-endereco'), ['cep' => null, 'bairro' => null, 'logradouro' => null, 'numero' => null, 'complemento' => null, 'estado' => null, 'municipio' => null, 'crimage' => null])
    //         ->assertSessionHasErrors([
    //             'cep',
    //             'bairro',
    //             'logradouro',
    //             'numero',
    //             'estado',
    //             'municipio',
    //             'crimage'
    //         ]);

    //     // Checa se a solicitação de mudança de endereço não foi registrado no banco de dados
    //     $this->assertEquals(RepresentanteEndereco::count(), 0);
    // }

    // /** @test 
    //  * 
    //  * Representante Comercial não pode logar na área restrita do Portal com a senha errada.
    // */
    // public function cannot_login_on_restrict_area_with_wrong_password()
    // {
    //     $representante = factory('App\Representante')->create();

    //     $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => '1234']);

    //     // Checa se mensagem do erro foi retornada
    //     $this->assertEquals(session('message'), 'Login inválido.');
    // }

    // /** @test 
    //  * 
    //  * Abas da área restrita não são acessíveis sem o login.
    // */
    // public function cannot_access_tabs_on_restrict_area_without_login()
    // {
    //     // Checa acesso a página principal é bloqueado e redirecionado para tela de login
    //     $this->get(route('representante.dashboard'))->assertRedirect(route('representante.login'));

    //     // Checa acesso a aba "Dados Gerais" é bloqueado e redirecionado para tela de login
    //     $this->get(route('representante.dados-gerais'))->assertRedirect(route('representante.login'));

    //     // Checa acesso a aba "Contatos" é bloqueado e redirecionado para tela de login
    //     $this->get(route('representante.contatos.view'))->assertRedirect(route('representante.login'));

    //     // Checa acesso a aba "End. de Correspondência" é bloqueado e redirecionado para tela de login
    //     $this->get(route('representante.enderecos.view'))->assertRedirect(route('representante.login'));

    //     // Checa acesso a aba "Situação Financeira" é bloqueado e redirecionado para tela de login
    //     $this->get(route('representante.lista-cobrancas'))->assertRedirect(route('representante.login'));
    // }

    // /** @test 
    //  * 
    //  * Senha não pode ser reconfigurada quando CPF/CNPJ não está registrado no Portal.
    // */
    // public function cannot_reset_password_for_representante_with_nonexistent_cpf_cnpj()
    // {
    //     $this->post(route('representante.password.email'), ['cpf_cnpj' => '04377629042']);

    //     // Checa se mensagem do erro foi retornada
    //     $this->assertEquals(session('message'), 'CPF ou CNPJ não cadastrado.');
    // }

    // /** @test 
    //  * 
    //  *  E-mail não pode ser modificado quando dados obrigatórios não são informados.
    // */
    // public function cannot_modify_email_for_representante_with_missing_mandatory_input()
    // {
    //     // Checa se erros nos campos foram retornados
    //     $this->post(route('representante.email.reset'), ['cpf_cnpj' => null, 'registro_core' => null, 'email_antigo' => null, 'email_novo' => null])
    //         ->assertSessionHasErrors([
    //             'cpf_cnpj',
    //             'registro_core',
    //             'email_antigo',
    //             'email_novo'
    //         ]);
    // }
}