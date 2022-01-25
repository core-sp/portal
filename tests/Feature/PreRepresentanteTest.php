<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\PreRepresentante;
use Illuminate\Support\Facades\Mail;
use App\Mail\CadastroPreRepresentanteMail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Notification;
use Illuminate\Support\Facades\Password;

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
            'cpf_cnpj_cad' => null, 
            'nome' => null,
            'email' => '', 
            'password' => '', 
            'password_confirmation' => null, 
            'checkbox-tdu' => ''
        ])
        ->assertSessionHasErrors([
            'cpf_cnpj_cad',
            'nome',
            'email',
            'password',
            'password_confirmation', 
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
            'cpf_cnpj_cad' => null, 
            'checkbox-tdu' => 'on',
            'password' => 'Teste102030',
            'password_confirmation' => 'Teste102030'
        ]);
        $this->get(route('prerepresentante.cadastro'))->assertOk();
        $this->post(route('prerepresentante.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'cpf_cnpj_cad'
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
     * Não pode criar um registro faltando confirmação de senha.
    */
    public function cannot_register_without_password_confirmation()
    {
        $dados = factory('App\PreRepresentante')->raw([
            'checkbox-tdu' => 'on',
            'password' => 'Teste102030'
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
            'cpf_cnpj_cad' => '36982299007',
            'checkbox-tdu' => 'on',
            'password' => 'Teste102030',
            'password_confirmation' => 'Teste102030'
        ]);
        $this->get(route('prerepresentante.cadastro'))->assertOk();
        $this->post(route('prerepresentante.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'cpf_cnpj_cad'
        ]);
        $this->assertDatabaseMissing('pre_representantes', [
            'cpf_cnpj' => $dados['cpf_cnpj_cad']
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
            'cpf_cnpj_cad' => $pre->cpf_cnpj,
            'checkbox-tdu' => 'on',
            'password' => 'Teste102030',
            'password_confirmation' => 'Teste102030'
        ]);
        $this->get(route('prerepresentante.cadastro'))->assertOk();
        $this->post(route('prerepresentante.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'cpf_cnpj_cad'
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

        $this->get(route('prerepresentante.cadastro'))->assertOk();
        $this->post(route('prerepresentante.cadastro.submit'), [
            'cpf_cnpj_cad' => '36982299007',
            'email' => 'teste@teste.com',
            'nome' => 'Teste do Registro',
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
            'checkbox-tdu' => 'on'
        ]);

        // Checa se o e-mail para confirmação do e-mail foi enviado
        Mail::assertQueued(CadastroPreRepresentanteMail::class);

        $this->assertDatabaseHas('pre_representantes', [
            'cpf_cnpj' => '36982299007', 
            'ativo' => 0
        ]);

        // Checa se após acessar o link de confirmação, o campo "ativo" é atualizado para 1
        $this->get(route('prerepresentante.verifica-email', PreRepresentante::first()->verify_token));
        $this->assertDatabaseHas('pre_representantes', [
            'cpf_cnpj' => '36982299007', 
            'ativo' => 1
        ]);
    }

    /** @test 
     * 
     * Pode se registrar se deletado e ativo 0.
    */
    public function register_new_prerepresentante_when_deleted_and_ativo_0()
    {
        Mail::fake();

        $prerep = factory('App\PreRepresentante')->create([
            'ativo' => 0,
            'deleted_at' => '2021-11-11 10:35:05'
        ]);
        $this->get(route('prerepresentante.cadastro'))->assertOk();
        $this->post(route('prerepresentante.cadastro.submit'), [
            'nome' => $prerep['nome'],
            'cpf_cnpj_cad' => $prerep['cpf_cnpj'],
            'email' => $prerep['email'],
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
            'checkbox-tdu' => 'on',
        ]);

        // Checa se o e-mail para confirmação do e-mail foi enviado
        Mail::assertQueued(CadastroPreRepresentanteMail::class);
        $this->assertDatabaseHas('pre_representantes', [
            'cpf_cnpj' => $prerep['cpf_cnpj'], 
            'ativo' => 0,
            'deleted_at' => null
        ]);

        // Checa se após acessar o link de confirmação, o campo "ativo" é atualizado para 1
        $this->get(route('prerepresentante.verifica-email', PreRepresentante::first()->verify_token));
        $this->assertDatabaseHas('pre_representantes', [
            'cpf_cnpj' => $prerep['cpf_cnpj'], 
            'ativo' => 1
        ]);
    }

    /** @test 
     * 
     * Log externo ao se cadastrar.
    */
    public function log_is_generated_when_new_prerepresentante()
    {
        $this->get(route('prerepresentante.cadastro'))->assertOk();
        $this->post(route('prerepresentante.cadastro.submit'), [
            'cpf_cnpj_cad' => '36982299007',
            'email' => 'teste@teste.com',
            'nome' => 'Teste do Registro',
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
            'checkbox-tdu' => 'on'
        ]);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $this->assertStringContainsString('36982299007', $log);
        $this->assertStringContainsString('cadastrou-se na Área do Pré-registro.', $log);
    }

    /** @test 
     * 
     * Log externo ao verificar email.
    */
    public function log_is_generated_when_verifica_email()
    {
        $this->get(route('prerepresentante.cadastro'))->assertOk();
        $this->post(route('prerepresentante.cadastro.submit'), [
            'cpf_cnpj_cad' => '36982299007',
            'email' => 'teste@teste.com',
            'nome' => 'Teste do Registro',
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
            'checkbox-tdu' => 'on'
        ]);
        $this->get(route('prerepresentante.verifica-email', PreRepresentante::first()->verify_token));
        $log = tailCustom(storage_path($this->pathLogExterno()));
        $this->assertStringContainsString('36982299007', $log);
        $this->assertStringContainsString('verificou o email após o cadastro.', $log);
    }

    /** @test 
     * 
     * Pre Representante Comercial pode logar na área restrita do Portal.
    */
    public function login_on_pre_registro()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $dados = [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password_login' => 'Teste102030'
        ];
        $this->get(route('prerepresentante.login'))->assertOk();
        $this->post(route('prerepresentante.login.submit'), $dados)
        ->assertRedirect(route('prerepresentante.dashboard'));
    }

    /** @test 
     * 
     * Log externo ao logar.
    */
    public function log_is_generated_when_logon()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $dados = [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password_login' => 'Teste102030'
        ];
        $this->post(route('prerepresentante.login.submit'), $dados);
        $log = tailCustom(storage_path($this->pathLogExterno()));
        $this->assertStringContainsString($prerep->id, $log);
        $this->assertStringContainsString('conectou-se à Área do Pré-registro.', $log);
    }

    /** @test 
     * 
     * Log externo ao não conseguir logar com cpf/cnpj válido, mas não existe no banco.
    */
    public function log_is_generated_when_error_logon()
    {
        factory('App\PreRepresentante')->create();
        $dados = [
            'cpf_cnpj' => '72027756000135',
            'password_login' => 'Teste102030'
        ];
        $this->post(route('prerepresentante.login.submit'), $dados);
        $log = tailCustom(storage_path($this->pathLogExterno()));
        $this->assertStringContainsString('72027756000135', $log);
        $this->assertStringContainsString('não conseguiu logar. Erro: CPF/CNPJ não encontrado.', $log);
    }

    /** @test 
     * 
     * Log externo ao não conseguir logar com senha errada.
    */
    public function log_is_generated_when_error_logon_with_password_wrong()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $dados = [
            'cpf_cnpj' => $prerep->cpf_cnpj,
            'password_login' => 'Teste10203040'
        ];
        $this->post(route('prerepresentante.login.submit'), $dados);
        $log = tailCustom(storage_path($this->pathLogExterno()));
        $this->assertStringContainsString($prerep->cpf_cnpj, $log);
        $this->assertStringContainsString('não conseguiu logar.', $log);
    }

    /** @test 
     * 
     * Pre Representante Comercial não pode logar se não está cadastrado.
    */
    public function cannot_login_on_pre_registro_without_registration()
    {
        $prerep = factory('App\PreRepresentante')->raw();
        $dados = [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password_login' => 'Teste102030'
        ];
        $this->get(route('prerepresentante.login'))->assertOk();
        $this->post(route('prerepresentante.login.submit'), $dados)
        ->assertRedirect(route('prerepresentante.login'));
    }

    /** @test 
     * 
     * Pre Representante Comercial não pode logar se não está ativo.
    */
    public function cannot_login_on_pre_registro_with_ativo_0()
    {
        $prerep = factory('App\PreRepresentante')->create([
            'ativo' => 0
        ]);
        $this->get(route('prerepresentante.login'))->assertOk();
        $this->post(route('prerepresentante.login.submit'), [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password_login' => 'Teste102030',
        ])
        ->assertRedirect(route('prerepresentante.login'));
        $this->get(route('prerepresentante.login'))->assertSeeText('Por favor, acesse o email informado no momento do cadastro para verificar sua conta.');
    }

    /** @test 
     * 
     * Pre Representante Comercial não pode logar se senha está errada.
    */
    public function cannot_login_on_pre_registro_whit_password_wrong()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $dados = [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password_login' => 'teste102030'
        ];
        $this->get(route('prerepresentante.login'))->assertOk();
        $this->post(route('prerepresentante.login.submit'), $dados)
        ->assertRedirect(route('prerepresentante.login'));
        $this->get(route('prerepresentante.login'))->assertSeeText('Login inválido');
    }

    /** @test 
     * 
     * Pre Representante Comercial não pode resetar senha sem cadastro.
    */
    public function cannot_send_mail_reset_password_for_prerepresentante_not_created()
    {
        $prerep = factory('App\PreRepresentante')->raw();
        $this->get(route('prerepresentante.password.request'))->assertOk();
        $this->post(route('prerepresentante.password.email'), [
            'cpf_cnpj' => $prerep['cpf_cnpj']
        ])->assertStatus(302);
        $this->get(route('prerepresentante.password.request'))
        ->assertSee('CPF ou CNPJ não cadastrado.');
    }

    /** @test 
     * 
     * Pre Representante Comercial pode resetar senha.
    */
    public function send_mail_reset_password_for_prerepresentante()
    {
        Notification::fake();

        $prerep = factory('App\PreRepresentante')->create();
        $this->get(route('prerepresentante.password.request'))->assertOk();
        $this->post(route('prerepresentante.password.email'), [
            'cpf_cnpj' => $prerep['cpf_cnpj']
        ])->assertStatus(302);
        $this->get(route('prerepresentante.password.request'))
        ->assertSee('O link de reconfiguração de senha foi enviado ao email ' .$prerep['email']);
        
        Notification::hasSent($prerep, ResetPassword::class);
    }

    /** @test 
     * 
     * Não pode enviar email para resetar senha se o cpf / cnpj não foi encontrado.
    */
    public function cannot_send_mail_reset_password_when_not_find_cpfcnpj()
    {
        factory('App\PreRepresentante')->create([
            'cpf_cnpj' => '43795442818'
        ]);
        $prerep = factory('App\PreRepresentante')->raw();
        $this->get(route('prerepresentante.password.request'))->assertOk();
        $this->post(route('prerepresentante.password.email'), [
            'cpf_cnpj' => $prerep['cpf_cnpj']
        ])->assertRedirect(route('prerepresentante.password.request'));
        $this->get(route('prerepresentante.password.request'))->assertSee('CPF ou CNPJ não cadastrado.');
    }

    /** @test 
     * 
     * Não pode enviar email para resetar senha se o cpf / cnpj está errado.
    */
    public function cannot_send_mail_reset_password_with_cpfcnpj_wrong()
    {
        $prerep = factory('App\PreRepresentante')->create([
            'cpf_cnpj' => '123456789'
        ]);
        $this->get(route('prerepresentante.password.request'))->assertOk();
        $this->post(route('prerepresentante.password.email'), [
            'cpf_cnpj' => $prerep['cpf_cnpj']
        ])->assertSessionHasErrors([
            'cpf_cnpj'
        ]);
    }

    /** @test 
     * 
     * Log externo ao alterar a senha em 'Esqueci a senha'.
    */
    public function log_is_generated_when_send_mail_reset_password()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $this->get(route('prerepresentante.password.request'));
        $this->post(route('prerepresentante.password.email'), [
            'cpf_cnpj' => $prerep['cpf_cnpj']
        ]);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $this->assertStringContainsString($prerep['cpf_cnpj'], $log);
        $this->assertStringContainsString('solicitou o envio de link para alterar a senha.', $log);
    }

    /** @test 
     * 
     * Não pode resetar senha se o cpf / cnpj está errado.
    */
    public function cannot_reset_password_with_cpfcnpj_wrong_after_verificar_email()
    {
        $prerep = factory('App\PreRepresentante')->create([
            'cpf_cnpj' => '123456789'
        ]);
        $token = Password::broker()->createToken($prerep);
        $this->get(route('prerepresentante.password.reset', $token))->assertSuccessful();
        $this->post(route('prerepresentante.password.update'), [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
            'token' => $token
        ])->assertSessionHasErrors([
            'cpf_cnpj'
        ]);
    }

    /** @test 
     * 
     * Não pode resetar senha se a senha está errada.
    */
    public function cannot_reset_password_with_password_wrong_after_verificar_email()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $token = Password::broker()->createToken($prerep);
        $this->get(route('prerepresentante.password.reset', $token))->assertSuccessful();
        $this->post(route('prerepresentante.password.update'), [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password' => 'teste102030', 
            'password_confirmation' => 'teste102030', 
            'token' => $token
        ])->assertSessionHasErrors([
            'password'
        ]);
    }

    /** @test 
     * 
     * Não pode resetar senha se a confirmação de senha está errada.
    */
    public function cannot_reset_password_with_password_confirmation_wrong_after_verificar_email()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $token = Password::broker()->createToken($prerep);
        $this->get(route('prerepresentante.password.reset', $token))->assertSuccessful();
        $this->post(route('prerepresentante.password.update'), [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password' => 'Teste102030', 
            'password_confirmation' => 'teste102030', 
            'token' => $token
        ])->assertSessionHasErrors([
            'password_confirmation'
        ]);
    }

    /** @test 
     * 
     * Não pode resetar senha se a senha e confirmação de senha estão diferentes.
    */
    public function cannot_reset_password_with_password_and_confirmation_differents_after_verificar_email()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $token = Password::broker()->createToken($prerep);
        $this->get(route('prerepresentante.password.reset', $token))->assertSuccessful();
        $this->post(route('prerepresentante.password.update'), [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste10203040', 
            'token' => $token
        ])->assertSessionHasErrors([
            'password_confirmation'
        ]);
    }

    /** @test 
     * 
     * Não pode resetar senha se os campos obrigatórios estão faltando.
    */
    public function cannot_reset_password_without_mandatory_inputs_after_verificar_email()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $token = Password::broker()->createToken($prerep);
        $this->get(route('prerepresentante.password.reset', $token))->assertSuccessful();
        $this->post(route('prerepresentante.password.update'), [
            'cpf_cnpj' => '',
            'password' => '', 
            'password_confirmation' => '', 
            'token' => $token
        ])->assertSessionHasErrors([
            'cpf_cnpj',
            'password',
            'password_confirmation'
        ]);
    }

    /** @test 
     * 
     * Pode resetar senha com tudo certo.
    */
    public function reset_password_after_verificar_email()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $token = Password::broker()->createToken($prerep);
        $this->get(route('prerepresentante.password.reset', $token))->assertSuccessful();
        $this->post(route('prerepresentante.password.update'), [
            'token' => $token,
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
        ])->assertRedirect(route('prerepresentante.login'));
        $this->get(route('prerepresentante.login'))
        ->assertSee('Senha alterada com sucesso. Favor realizar o login novamente com as novas informações.');
    }

    /** @test 
     * 
     * Log externo ao alterar a senha em 'Esqueci a senha'.
    */
    public function log_is_generated_when_reset_password()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $token = Password::broker()->createToken($prerep);
        $this->get(route('prerepresentante.password.reset', $token));
        $this->post(route('prerepresentante.password.update'), [
            'token' => $token,
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
        ]);
        $log = tailCustom(storage_path($this->pathLogExterno()));
        $this->assertStringContainsString($prerep['cpf_cnpj'], $log);
        $this->assertStringContainsString('alterou a senha com sucesso.', $log);
    }

    /** @test 
     * 
     * Pode editar os dados cadastrais.
    */
    public function can_after_login_update_nome_and_email()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $dados = [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password_login' => 'Teste102030'
        ];
        $this->get(route('prerepresentante.login'))->assertOk();
        $this->post(route('prerepresentante.login.submit'), $dados);
        $this->get(route('prerepresentante.editar.view'))->assertOk();
        $this->put(route('prerepresentante.editar', [
            'nome' => 'Novo nome do Pre Representante',
            'email' => 'teste@email.com.br'
        ]));
        $this->get(route('prerepresentante.editar.view'))
        ->assertSee('Dados alterados com sucesso.');
        $this->assertDatabaseHas('pre_representantes', [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'nome' => strtoupper('Novo nome do Pre Representante'),
            'email' => 'teste@email.com.br'
        ]);
    }

    /** @test 
     * 
     * Log externo ao alterar os dados cadastrais.
    */
    public function log_is_generated_when_update_data()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $dados = [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password_login' => 'Teste102030'
        ];
        $this->get(route('prerepresentante.login'));
        $this->post(route('prerepresentante.login.submit'), $dados);
        $this->get(route('prerepresentante.editar.view'));
        $this->put(route('prerepresentante.editar', [
            'nome' => 'Novo nome do Pre Representante',
            'email' => 'teste@email.com.br'
        ]));
        $log = tailCustom(storage_path($this->pathLogExterno()));
        $this->assertStringContainsString($prerep->id, $log);
        $this->assertStringContainsString($prerep->cpf_cnpj, $log);
        $this->assertStringContainsString('alterou os dados com sucesso.', $log);
    }

    /** @test 
     * 
     * Pode editar o nome.
    */
    public function can_after_login_update_nome()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $dados = [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password_login' => 'Teste102030'
        ];
        $this->get(route('prerepresentante.login'))->assertOk();
        $this->post(route('prerepresentante.login.submit'), $dados);
        $this->get(route('prerepresentante.editar.view'))->assertOk();
        $this->put(route('prerepresentante.editar', [
            'nome' => 'Novo nome do Pre Representante',
            'email' => $prerep['email']
        ]));
        $this->assertDatabaseHas('pre_representantes', [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'nome' => strtoupper('Novo nome do Pre Representante'),
            'email' => $prerep['email']
        ]);
    }

    /** @test 
     * 
     * Pode editar o email.
    */
    public function can_after_login_update_email()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $dados = [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password_login' => 'Teste102030'
        ];
        $this->get(route('prerepresentante.login'))->assertOk();
        $this->post(route('prerepresentante.login.submit'), $dados);
        $this->get(route('prerepresentante.editar.view'))->assertOk();
        $this->put(route('prerepresentante.editar', [
            'nome' => $prerep['nome'],
            'email' => 'teste@teste.com.br'
        ]));
        $this->assertDatabaseHas('pre_representantes', [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'nome' => $prerep['nome'],
            'email' => 'teste@teste.com.br'
        ]);
    }

    /** @test 
     * 
     * Carrega os dados cadastrais.
    */
    public function fill_data_with_nome_cpfcnpj_email()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $dados = [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password_login' => 'Teste102030'
        ];
        $this->get(route('prerepresentante.login'))->assertOk();
        $this->post(route('prerepresentante.login.submit'), $dados);
        $this->get(route('prerepresentante.editar.view'))
        ->assertSee($prerep['nome'])
        ->assertSee($prerep['cpf_cnpj'])
        ->assertSee($prerep['email']);
    }

    /** @test 
     * 
     * Não pode editar os dados cadastrais sem os inputs obrigatórios.
    */
    public function cannot_after_login_update_nome_and_email_without_mandatory_inputs()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $dados = [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password_login' => 'Teste102030'
        ];
        $this->get(route('prerepresentante.login'))->assertOk();
        $this->post(route('prerepresentante.login.submit'), $dados);
        $this->get(route('prerepresentante.editar.view'))->assertOk();
        $this->put(route('prerepresentante.editar', [
            'nome' => '',
            'email' => ''
        ]))->assertSessionHasErrors([
            'nome',
            'email'
        ]);
        $this->assertDatabaseHas('pre_representantes', [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'nome' => $prerep['nome'],
            'email' => $prerep['email']
        ]);
    }

    /** @test 
     * 
     * Não pode editar o nome vazio.
    */
    public function cannot_after_login_update_nome_empty()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $dados = [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password_login' => 'Teste102030'
        ];
        $this->get(route('prerepresentante.login'))->assertOk();
        $this->post(route('prerepresentante.login.submit'), $dados);
        $this->get(route('prerepresentante.editar.view'))->assertOk();
        $this->put(route('prerepresentante.editar', [
            'nome' => ''
        ]))->assertSessionHasErrors([
            'nome'
        ]);
        $this->assertDatabaseHas('pre_representantes', [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'nome' => $prerep['nome'],
            'email' => $prerep['email']
        ]);
    }

    /** @test 
     * 
     * Não pode editar o email vazio.
    */
    public function cannot_after_login_update_email_empty()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $dados = [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password_login' => 'Teste102030'
        ];
        $this->get(route('prerepresentante.login'))->assertOk();
        $this->post(route('prerepresentante.login.submit'), $dados);
        $this->get(route('prerepresentante.editar.view'))->assertOk();
        $this->put(route('prerepresentante.editar', [
            'email' => ''
        ]))->assertSessionHasErrors([
            'email'
        ]);
        $this->assertDatabaseHas('pre_representantes', [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'nome' => $prerep['nome'],
            'email' => $prerep['email']
        ]);
    }

    /** @test 
     * 
     * Não pode editar o email errado.
    */
    public function cannot_after_login_update_email_wrong()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $dados = [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password_login' => 'Teste102030'
        ];
        $this->get(route('prerepresentante.login'))->assertOk();
        $this->post(route('prerepresentante.login.submit'), $dados);
        $this->get(route('prerepresentante.editar.view'))->assertOk();
        $this->put(route('prerepresentante.editar', [
            'email' => 'teste.com.br'
        ]))->assertSessionHasErrors([
            'email'
        ]);
        $this->assertDatabaseHas('pre_representantes', [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'nome' => $prerep['nome'],
            'email' => $prerep['email']
        ]);
    }
    
    /** @test 
     * 
     * Pode editar a senha depois de logar.
    */
    public function can_after_login_update_password()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $dados = [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password_login' => 'Teste102030'
        ];
        $this->get(route('prerepresentante.login'))->assertOk();
        $this->post(route('prerepresentante.login.submit'), $dados);
        $this->get(route('prerepresentante.editar.view'))->assertOk();
        $this->get(route('prerepresentante.editar.senha.view'))->assertOk();
        $this->put(route('prerepresentante.editar', [
            'password_login' => 'Teste102030',
            'password' => 'Teste10203040',
            'password_confirmation' => 'Teste10203040'
        ]));
        $this->get(route('prerepresentante.editar.view'))
        ->assertSee('Dados alterados com sucesso.');
    }

    /** @test 
     * 
     * Não pode editar a senha se a atual foi digitada errada.
    */
    public function cannot_after_login_update_password_with_password_atual_wrong()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $dados = [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password_login' => 'Teste102030'
        ];
        $this->get(route('prerepresentante.login'))->assertOk();
        $this->post(route('prerepresentante.login.submit'), $dados);
        $this->get(route('prerepresentante.editar.view'))->assertOk();
        $this->get(route('prerepresentante.editar.senha.view'))->assertOk();
        $this->put(route('prerepresentante.editar', [
            'password_login' => 'Teste10203040',
            'password' => 'Teste10203040',
            'password_confirmation' => 'Teste10203040'
        ]));
        $this->get(route('prerepresentante.editar.senha.view'))
        ->assertSee('A senha atual digitada está incorreta!');
    }

    /** @test 
     * 
     * Não pode editar a senha se está errada.
    */
    public function cannot_after_login_update_password_wrong()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $dados = [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password_login' => 'Teste102030'
        ];
        $this->get(route('prerepresentante.login'))->assertOk();
        $this->post(route('prerepresentante.login.submit'), $dados);
        $this->get(route('prerepresentante.editar.view'))->assertOk();
        $this->get(route('prerepresentante.editar.senha.view'))->assertOk();
        $this->put(route('prerepresentante.editar', [
            'password_login' => 'Teste102030',
            'password' => 'teste10203040',
            'password_confirmation' => 'teste10203040'
        ]))->assertSessionHasErrors([
            'password'
        ]);
    }

    /** @test 
     * 
     * Não pode editar a senha se a confirmação de senha está errada.
    */
    public function cannot_after_login_update_password_confirmation_wrong()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $dados = [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password_login' => 'Teste102030'
        ];
        $this->get(route('prerepresentante.login'))->assertOk();
        $this->post(route('prerepresentante.login.submit'), $dados);
        $this->get(route('prerepresentante.editar.view'))->assertOk();
        $this->get(route('prerepresentante.editar.senha.view'))->assertOk();
        $this->put(route('prerepresentante.editar', [
            'password_login' => 'Teste102030',
            'password' => 'Teste10203040',
            'password_confirmation' => 'teste10203040'
        ]))->assertSessionHasErrors([
            'password'
        ]);
    }

    /** @test 
     * 
     * Não pode editar a senha se a senha e confirmação de senha estão diferentes.
    */
    public function cannot_after_login_update_password_and_confirmation_differents()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $dados = [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password_login' => 'Teste102030'
        ];
        $this->get(route('prerepresentante.login'))->assertOk();
        $this->post(route('prerepresentante.login.submit'), $dados);
        $this->get(route('prerepresentante.editar.view'))->assertOk();
        $this->get(route('prerepresentante.editar.senha.view'))->assertOk();
        $this->put(route('prerepresentante.editar', [
            'password_login' => 'Teste102030',
            'password' => 'Teste10203040',
            'password_confirmation' => 'Teste1020304050'
        ]))->assertSessionHasErrors([
            'password'
        ]);
    }

    /** @test 
     * 
     * Não pode editar a senha se a senha está vazia.
    */
    public function cannot_after_login_update_password_empty()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $dados = [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password_login' => 'Teste102030'
        ];
        $this->get(route('prerepresentante.login'))->assertOk();
        $this->post(route('prerepresentante.login.submit'), $dados);
        $this->get(route('prerepresentante.editar.view'))->assertOk();
        $this->get(route('prerepresentante.editar.senha.view'))->assertOk();
        $this->put(route('prerepresentante.editar', [
            'password_login' => 'Teste102030',
            'password' => '',
            'password_confirmation' => 'Teste1020304050'
        ]))->assertSessionHasErrors([
            'password'
        ]);
    }

    /** @test 
     * 
     * Não pode editar a senha se a confirmação está vazia.
    */
    public function cannot_after_login_update_confirmation_empty()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $dados = [
            'cpf_cnpj' => $prerep['cpf_cnpj'],
            'password_login' => 'Teste102030'
        ];
        $this->get(route('prerepresentante.login'))->assertOk();
        $this->post(route('prerepresentante.login.submit'), $dados);
        $this->get(route('prerepresentante.editar.view'))->assertOk();
        $this->get(route('prerepresentante.editar.senha.view'))->assertOk();
        $this->put(route('prerepresentante.editar', [
            'password_login' => 'Teste102030',
            'password' => 'Teste1020304050',
            'password_confirmation' => ''
        ]))->assertSessionHasErrors([
            'password'
        ]);
    }

    // *************************************************************************************************************

    /** @test 
     * 
     * Pode acessar todas as abas na área restrita do Portal.
    */
    public function after_login_can_access_tabs_on_restrict_area_pre_registro()
    {
        $prerep = factory('App\PreRepresentante')->create();
        $this->post(route('prerepresentante.login.submit'), [
            'cpf_cnpj' => $prerep['cpf_cnpj'], 
            'password_login' => 'Teste102030'
        ]);
        $this->get(route('prerepresentante.dashboard'))->assertOk();
        $this->get(route('prerepresentante.editar.view'))->assertOk();
        $this->get(route('prerepresentante.editar.senha.view'))->assertOk();
    }

    /** @test 
     * 
     * Abas da área restrita não são acessíveis sem o login.
    */
    public function cannot_access_tabs_on_restrict_area_without_login()
    {
        $this->get(route('prerepresentante.dashboard'))->assertRedirect(route('prerepresentante.login'));
        $this->get(route('prerepresentante.editar.view'))->assertRedirect(route('prerepresentante.login'));
        $this->get(route('prerepresentante.editar.senha.view'))->assertRedirect(route('prerepresentante.login'));
    }
}