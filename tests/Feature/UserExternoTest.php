<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\UserExterno;
use Illuminate\Support\Facades\Mail;
use App\Mail\CadastroUserExternoMail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Notification;
use Illuminate\Support\Facades\Password;
use Carbon\Carbon;

class UserExternoTest extends TestCase
{
    use RefreshDatabase;

    /** @test 
     * 
     * Não pode criar um registro com formulário em branco.
    */
    public function cannot_register_without_mandatory_inputs()
    {
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), [
            'cpf_cnpj' => null, 
            'nome' => null,
            'email' => '', 
            'password' => '', 
            'password_confirmation' => null, 
            'aceite' => ''
        ])
        ->assertSessionHasErrors([
            'cpf_cnpj',
            'nome',
            'email',
            'password',
            'password_confirmation', 
            'aceite'
        ]);
    }

    /** @test 
     * 
     * Não pode criar um registro faltando cpf/cnpj.
    */
    public function cannot_register_without_cpfcnpj_input()
    {
        $dados = factory('App\UserExterno')->raw([
            'cpf_cnpj' => null, 
            'aceite' => 'on',
            'password' => 'Teste102030',
            'password_confirmation' => 'Teste102030'
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'cpf_cnpj'
        ]);

        $this->assertDatabaseMissing('users_externo', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test 
     * 
     * Não pode criar um registro faltando nome.
    */
    public function cannot_register_without_nome_input()
    {
        $dados = factory('App\UserExterno')->raw([
            'nome' => '', 
            'aceite' => 'on',
            'password' => 'Teste102030',
            'password_confirmation' => 'Teste102030'
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'nome'
        ]);

        $this->assertDatabaseMissing('users_externo', [
            'email' => $dados['email']
        ]);
    }

    /** @test 
     * 
     * Não pode criar um registro faltando email.
    */
    public function cannot_register_without_email_input()
    {
        $dados = factory('App\UserExterno')->raw([
            'email' => '', 
            'aceite' => 'on',
            'password' => 'Teste102030',
            'password_confirmation' => 'Teste102030'
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'email'
        ]);

        $this->assertDatabaseMissing('users_externo', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test 
     * 
     * Não pode criar um registro faltando concordar com os termos.
    */
    public function cannot_register_without_checkbox_input()
    {
        $dados = factory('App\UserExterno')->raw([
            'aceite' => '', 
            'password' => 'Teste102030',
            'password_confirmation' => 'Teste102030'
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'aceite'
        ]);

        $this->assertDatabaseMissing('users_externo', [
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
        $dados = factory('App\UserExterno')->raw([
            'aceite' => 'on',
            'password' => 'teste10',
            'password_confirmation' => 'teste10'
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'password'
        ]);

        $this->assertDatabaseMissing('users_externo', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test 
     * 
     * Não pode criar um registro faltando com a senha e confirmação de senha diferentes.
    */
    public function cannot_register_with_password_and_confirmation_differents()
    {
        $dados = factory('App\UserExterno')->raw([
            'aceite' => 'on',
            'password' => 'Teste102030',
            'password_confirmation' => 'teste102030'
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'password_confirmation'
        ]);

        $this->assertDatabaseMissing('users_externo', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test 
     * 
     * Não pode criar um registro faltando confirmação de senha.
    */
    public function cannot_register_without_password_confirmation()
    {
        $dados = factory('App\UserExterno')->raw([
            'aceite' => 'on',
            'password' => 'Teste102030'
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'password'
        ]);

        $this->assertDatabaseMissing('users_externo', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test 
     * 
     * Não pode criar um registro com padrão de email errado.
    */
    public function cannot_register_with_email_wrong()
    {
        $dados = factory('App\UserExterno')->raw([
            'email' => 'gfgfgf.com', 
            'aceite' => 'on',
            'password' => 'Teste102030',
            'password_confirmation' => 'Teste102030'
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'email'
        ]);

        $this->assertDatabaseMissing('users_externo', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test 
     * 
     * Não pode criar um registro se o cpf/cnpj já existirem no banco dos Usuário Externo.
    */
    public function cannot_register_if_exist_cpfcnpj()
    {
        $pre = factory('App\UserExterno')->create();
        $dados = factory('App\UserExterno')->raw([
            'cpf_cnpj' => $pre->cpf_cnpj,
            'aceite' => 'on',
            'password' => 'Teste102030',
            'password_confirmation' => 'Teste102030'
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'cpf_cnpj'
        ]);
        $this->assertDatabaseMissing('users_externo', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test 
     * 
    */
    public function cannot_register_if_exist_more_than_two_email_equals()
    {
        $pre = factory('App\UserExterno')->create();
        $pre2 = factory('App\UserExterno')->create([
            'cpf_cnpj' => '03961439893',
            'email' => $pre->email
        ]);
        $dados = factory('App\UserExterno')->raw([
            'cpf_cnpj' => '09361260000167',
            'aceite' => 'on',
            'password' => 'Teste102030',
            'password_confirmation' => 'Teste102030',
            'email' => $pre->email
        ]);
        $this->get(route('externo.cadastro'))->assertOk();

        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertRedirect(route('externo.cadastro'));

        $this->get(route('externo.cadastro'))
        ->assertSeeText('Este email já alcançou o limite de cadastro, por favor insira outro.');

        $this->assertDatabaseMissing('users_externo', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test 
     * 
     * Pode se registrar com todas as informações certas.
    */
    public function register_new_user_externo()
    {
        Mail::fake();

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), [
            'cpf_cnpj' => '36982299007',
            'email' => 'teste@teste.com',
            'nome' => 'Teste do Registro',
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
            'aceite' => 'on'
        ]);

        Mail::assertQueued(CadastroUserExternoMail::class);

        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => '36982299007', 
            'ativo' => 0
        ]);

        // Checa se após acessar o link de confirmação, o campo "ativo" é atualizado para 1
        $this->get(route('externo.verifica-email', UserExterno::first()->verify_token));
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => '36982299007', 
            'ativo' => 1
        ]);
    }

    /** @test 
     * 
     * Pode se registrar se deletado e ativo 0.
    */
    public function register_new_user_externo_when_deleted_and_ativo_0_after_24h()
    {
        Mail::fake();

        $user_externo = factory('App\UserExterno')->create([
            'ativo' => 0,
        ]);
        $user_externo->delete();
        UserExterno::withTrashed()->first()->update(['updated_at' => Carbon::today()->subDay()]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), [
            'nome' => $user_externo['nome'],
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'email' => $user_externo['email'],
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
            'aceite' => 'on',
        ]);

        Mail::assertQueued(CadastroUserExternoMail::class);
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $user_externo['cpf_cnpj'], 
            'ativo' => 0,
            'deleted_at' => null
        ]);

        // Checa se após acessar o link de confirmação, o campo "ativo" é atualizado para 1
        $this->get(route('externo.verifica-email', UserExterno::first()->verify_token));
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $user_externo['cpf_cnpj'], 
            'ativo' => 1
        ]);
    }

    /** @test 
     * 
    */
    public function cannot_register_new_user_externo_when_ativo_0_in_24h()
    {
        Mail::fake();

        $user_externo = factory('App\UserExterno')->create([
            'ativo' => 0,
        ]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), [
            'nome' => $user_externo['nome'],
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'email' => $user_externo['email'],
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
            'aceite' => 'on',
        ]);

        $this->get(route('externo.cadastro'))->assertSeeText('Esta conta já solicitou o cadastro. Verifique seu email para ativar. Caso não tenha mais acesso ao e-mail, aguarde 24h para se recadastrar');

        Mail::assertNotQueued(CadastroUserExternoMail::class);
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $user_externo['cpf_cnpj'], 
            'ativo' => 0,
            'deleted_at' => null
        ]);
    }

    /** @test 
    */
    public function cannot_to_active_register_after_24h()
    {
        Mail::fake();

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), [
            'cpf_cnpj' => '36982299007',
            'email' => 'teste@teste.com',
            'nome' => 'Teste do Registro',
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
            'aceite' => 'on'
        ]);

        Mail::assertQueued(CadastroUserExternoMail::class);

        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => '36982299007', 
            'ativo' => 0
        ]);

        UserExterno::first()->update(['updated_at' => Carbon::today()->subDays(2)]);
        
        $this->get(route('externo.verifica-email', UserExterno::first()->verify_token))->assertRedirect(route('externo.login'));

        $this->get(route('externo.login'))
        ->assertSeeText('Falha na verificação. Caso e-mail já tenha sido verificado, basta logar na área restrita do Login Externo, caso contrário, por favor refazer cadastro no Login Externo.');
        
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => '36982299007', 
            'ativo' => 0
        ]);
    }

    /** @test 
    */
    public function cannot_verify_mail_with_wrong_token()
    {
        Mail::fake();

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), [
            'cpf_cnpj' => '36982299007',
            'email' => 'teste@teste.com',
            'nome' => 'Teste do Registro',
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
            'aceite' => 'on'
        ]);

        Mail::assertQueued(CadastroUserExternoMail::class);

        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => '36982299007', 
            'ativo' => 0
        ]);
        
        $this->get(route('externo.verifica-email', UserExterno::first()->verify_token . '5'))->assertStatus(302);

        $this->get(route('externo.login'))
        ->assertSeeText('Falha na verificação. Caso e-mail já tenha sido verificado, basta logar na área restrita do Login Externo, caso contrário, por favor refazer cadastro no Login Externo.');
        
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => '36982299007', 
            'ativo' => 0
        ]);
    }

    /** @test 
     * 
     * Pode se registrar se deletado e ativo 0.
    */
    public function register_after_24h_and_verify_mail()
    {
        Mail::fake();

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), [
            'cpf_cnpj' => '36982299007',
            'email' => 'teste@teste.com',
            'nome' => 'Teste do Registro',
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
            'aceite' => 'on'
        ]);

        Mail::assertQueued(CadastroUserExternoMail::class);

        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => '36982299007', 
            'ativo' => 0
        ]);

        UserExterno::first()->update(['updated_at' => Carbon::today()->subDays(2)]);
        $this->get(route('externo.verifica-email', UserExterno::first()->verify_token))->assertRedirect(route('externo.login'));
        $this->get(route('externo.login'))
        ->assertSeeText('Falha na verificação. Caso e-mail já tenha sido verificado, basta logar na área restrita do Login Externo, caso contrário, por favor refazer cadastro no Login Externo.');
        
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => '36982299007', 
            'ativo' => 0
        ]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), [
            'cpf_cnpj' => '36982299007',
            'email' => 'teste@teste.com',
            'nome' => 'Teste do Registro',
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
            'aceite' => 'on'
        ]);

        Mail::assertQueued(CadastroUserExternoMail::class);

        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => '36982299007', 
            'ativo' => 0,
            'deleted_at' => null
        ]);

        // Checa se após acessar o link de confirmação, o campo "ativo" é atualizado para 1
        $this->get(route('externo.verifica-email', UserExterno::first()->verify_token));
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => '36982299007', 
            'ativo' => 1
        ]);
    }

    /** @test 
    */
    public function cannot_register_new_user_externo_when_ativo_0_and_deleted_in_24h()
    {
        Mail::fake();

        $user_externo = factory('App\UserExterno')->create([
            'ativo' => 0,
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), [
            'nome' => $user_externo['nome'],
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'email' => $user_externo['email'],
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
            'aceite' => 'on',
        ]);

        $this->get(route('externo.cadastro'))
        ->assertSeeText('Esta conta já solicitou o cadastro. Verifique seu email para ativar. Caso não tenha mais acesso ao e-mail, aguarde 24h para se recadastrar');

        Mail::assertNotQueued(CadastroUserExternoMail::class);

        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $user_externo->cpf_cnpj, 
            'ativo' => 0
        ]);
    }

    /** @test 
     * 
     * Log externo ao se cadastrar.
    */
    public function log_is_generated_when_new_user_externo()
    {
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), [
            'cpf_cnpj' => '36982299007',
            'email' => 'teste@teste.com',
            'nome' => 'Teste do Registro',
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
            'aceite' => 'on'
        ]);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $this->assertStringContainsString('36982299007', $log);
        $this->assertStringContainsString('cadastrou-se na Área do Login Externo.', $log);
    }

    /** @test 
     * 
     * Log externo ao verificar email.
    */
    public function log_is_generated_when_verifica_email()
    {
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), [
            'cpf_cnpj' => '36982299007',
            'email' => 'teste@teste.com',
            'nome' => 'Teste do Registro',
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
            'aceite' => 'on'
        ]);
        $this->get(route('externo.verifica-email', UserExterno::first()->verify_token));
        $log = tailCustom(storage_path($this->pathLogExterno()));
        $this->assertStringContainsString('36982299007', $log);
        $this->assertStringContainsString('verificou o email após o cadastro.', $log);
    }

    /** @test 
     * 
     * Usuário Externo  pode logar na área restrita do Portal.
    */
    public function login_on_externo()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados)
        ->assertRedirect(route('externo.dashboard'));
    }

    /** @test 
     * 
     * Log externo ao logar.
    */
    public function log_is_generated_when_logon()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->post(route('externo.login.submit'), $dados);
        $log = tailCustom(storage_path($this->pathLogExterno()));

        $texto = 'Usuário '.$user_externo['nome'] . ' ("'.formataCpfCnpj($user_externo['cpf_cnpj']). '") conectou-se à Área do Usuário Externo.';
        $this->assertStringContainsString($texto, $log);
    }

    /** @test 
     * 
     * Log externo ao logar.
    */
    public function log_is_generated_when_logout()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->post(route('externo.login.submit'), $dados);
        $this->post(route('externo.logout'));
        $log = tailCustom(storage_path($this->pathLogExterno()));

        $texto = 'Usuário '.$user_externo['nome'] . ' ("'.formataCpfCnpj($user_externo['cpf_cnpj']). '") desconectou-se da Área do Usuário Externo.';
        $this->assertStringContainsString($texto, $log);
    }

    /** @test 
     * 
     * Log externo ao logar.
    */
    public function log_is_generated_when_logout_without_session()
    {
        $this->post(route('externo.logout'));
        $log = tailCustom(storage_path($this->pathLogExterno()));

        $texto = 'Sessão expirou / não há sessão ativa ao realizar o logout da Área do Usuário Externo.';
        $this->assertStringContainsString($texto, $log);
    }

    /** @test 
     * 
     * Log externo ao não conseguir logar com cpf/cnpj válido, mas não existe no banco.
    */
    public function log_is_generated_when_failed_logon()
    {
        factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => '72027756000135',
            'password' => 'Teste102030'
        ];
        $this->post(route('externo.login.submit'), $dados);
        $log = tailCustom(storage_path($this->pathLogExterno()));

        $texto = 'Usuário não encontrado com o cpf/cnpj "' .$dados['cpf_cnpj']. '" não conseguiu logar na Área do Usuário Externo.';
        $this->assertStringContainsString($texto, $log);
    }

    /** @test 
     * 
     * Log externo ao não conseguir logar com senha errada.
    */
    public function log_is_generated_when_failed_logon_with_password_wrong()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $user_externo->cpf_cnpj,
            'password' => 'Teste10203040'
        ];
        $this->post(route('externo.login.submit'), $dados);
        $log = tailCustom(storage_path($this->pathLogExterno()));

        $texto = 'Usuário com o cpf/cnpj '.$user_externo->cpf_cnpj . ' não conseguiu logar na Área do Usuário Externo.';
        $this->assertStringContainsString($texto, $log);
    }

    /** @test 
     * 
     * Usuário Externo  não pode logar se não está cadastrado.
    */
    public function cannot_login_on_externo_without_registration()
    {
        $user_externo = factory('App\UserExterno')->raw();
        $dados = [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados)
        ->assertRedirect(route('externo.login'));

        $this->get(route('externo.login'))->assertSeeText('CPF/CNPJ não encontrado.');
    }

    /** @test 
     * 
     * Usuário Externo  não pode logar se não está ativo.
    */
    public function cannot_login_on_externo_with_ativo_0()
    {
        $user_externo = factory('App\UserExterno')->create([
            'ativo' => 0
        ]);
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030',
        ])
        ->assertRedirect(route('externo.login'));
        $this->get(route('externo.login'))->assertSeeText('Por favor, acesse o email informado no momento do cadastro para verificar sua conta.');
    }

    /** @test 
     * 
     * Usuário Externo  não pode logar se não está ativo.
    */
    public function cannot_login_on_externo_when_deleted()
    {
        $user_externo = factory('App\UserExterno')->create();
        $user_externo->delete();
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030',
        ])
        ->assertRedirect(route('externo.login'));
        $this->get(route('externo.login'))->assertSeeText('CPF/CNPJ não encontrado.');
    }

    /** @test 
     * 
     * Usuário Externo  não pode logar se senha está errada.
    */
    public function cannot_login_on_externo_whit_password_wrong()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'teste102030'
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados)
        ->assertRedirect(route('externo.login'));
        $this->get(route('externo.login'))->assertSeeText('Login inválido');
    }

    /** @test 
    */
    public function active_user_await_login_after_3x()
    {
        $user_externo = factory('App\UserExterno')->create();

        for($i = 0; $i < 3; $i++)
        {
            $this->get(route('externo.login'))->assertOk();
            $this->post(route('externo.login.submit'), [
                'cpf_cnpj' => $user_externo['cpf_cnpj'],
                'password' => 'Teste10203',
            ])
            ->assertRedirect(route('externo.login'));

            $this->get(route('externo.login'))
            ->assertDontSeeText('Login inválido devido à quantidade de tentativas. Tente novamente em');
        }

        $this->post(route('externo.login.submit'), [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste10203',
        ])->assertSessionHasErrors([
            'cpf_cnpj'
        ])->assertRedirect(route('externo.login'));
        
        $this->get(route('externo.login'))
        ->assertSeeText('Login inválido devido à quantidade de tentativas. Tente novamente em');
    }

    /** @test 
    */
    public function log_is_generated_when_lockout_logon_with_cpf_cnpj_not_created()
    {
        $externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => '72027756000135',
            'password' => 'Teste102030'
        ];
        for($i = 0; $i < 4; $i++)
            $this->post(route('externo.login.submit'), $dados);
            
        $log = tailCustom(storage_path($this->pathLogExterno()));

        $texto = 'Usuário com cpf/cnpj "'.$dados['cpf_cnpj'].'" foi bloqueado temporariamente por alguns segundos devido a alcançar ';
        $texto .= 'o limite de tentativas de login na Área do Usuário Externo.';
        $this->assertStringContainsString($texto, $log);
    }

    /** @test 
    */
    public function log_is_generated_when_lockout_logon_with_cpf_cnpj_not_actived()
    {
        $externo = factory('App\UserExterno')->create([
            'ativo' => 0
        ]);
        $dados = [
            'cpf_cnpj' => $externo->cpf_cnpj,
            'password' => 'Teste102030'
        ];
        for($i = 0; $i < 4; $i++)
            $this->post(route('externo.login.submit'), $dados);
            
        $log = tailCustom(storage_path($this->pathLogExterno()));

        $texto = 'Usuário com cpf/cnpj "'.$dados['cpf_cnpj'].'" foi bloqueado temporariamente por alguns segundos devido a alcançar ';
        $texto .= 'o limite de tentativas de login na Área do Usuário Externo.';
        $this->assertStringContainsString($texto, $log);
    }

    /** @test 
    */
    public function log_is_generated_when_lockout_logon()
    {
        $externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $externo->cpf_cnpj,
            'password' => 'Teste1020'
        ];
        for($i = 0; $i < 4; $i++)
            $this->post(route('externo.login.submit'), $dados);
            
        $log = tailCustom(storage_path($this->pathLogExterno()));

        $texto = 'Usuário com cpf/cnpj "'.$dados['cpf_cnpj'].'" foi bloqueado temporariamente por alguns segundos devido a alcançar ';
        $texto .= 'o limite de tentativas de login na Área do Usuário Externo.';
        $this->assertStringContainsString($texto, $log);
    }

    /** @test 
    */
    public function not_active_user_await_login_after_3x()
    {
        $user_externo = factory('App\UserExterno')->create([
            'ativo' => 0
        ]);

        for($i = 0; $i < 3; $i++)
        {
            $this->get(route('externo.login'))->assertOk();
            $this->post(route('externo.login.submit'), [
                'cpf_cnpj' => $user_externo['cpf_cnpj'],
                'password' => 'Teste10203',
            ])
            ->assertRedirect(route('externo.login'));

            $this->get(route('externo.login'))
            ->assertDontSeeText('Login inválido devido à quantidade de tentativas. Tente novamente em');
        }

        $this->post(route('externo.login.submit'), [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste10203',
        ])->assertSessionHasErrors([
            'cpf_cnpj'
        ])->assertRedirect(route('externo.login'));
        
        $this->get(route('externo.login'))
        ->assertSeeText('Login inválido devido à quantidade de tentativas. Tente novamente em');
    }

    /** @test 
    */
    public function not_registered_user_await_login_after_3x()
    {
        $user_externo = factory('App\UserExterno')->make();

        for($i = 0; $i < 3; $i++)
        {
            $this->get(route('externo.login'))->assertOk();
            $this->post(route('externo.login.submit'), [
                'cpf_cnpj' => $user_externo['cpf_cnpj'],
                'password' => 'Teste10203',
            ])
            ->assertRedirect(route('externo.login'));

            $this->get(route('externo.login'))
            ->assertDontSeeText('Login inválido devido à quantidade de tentativas. Tente novamente em');
        }

        $this->post(route('externo.login.submit'), [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste10203',
        ])->assertSessionHasErrors([
            'cpf_cnpj'
        ])->assertRedirect(route('externo.login'));
        
        $this->get(route('externo.login'))
        ->assertSeeText('Login inválido devido à quantidade de tentativas. Tente novamente em');
    }

    /** @test 
     * 
     * Usuário Externo  não pode resetar senha sem cadastro.
    */
    public function cannot_send_mail_reset_password_for_user_externo_not_created()
    {
        Notification::fake();

        $user_externo = factory('App\UserExterno')->raw();
        $this->get(route('externo.password.request'))->assertOk();
        $this->post(route('externo.password.email'), [
            'cpf_cnpj' => $user_externo['cpf_cnpj']
        ])->assertSessionHasErrors([
            'cpf_cnpj'
        ]);

        Notification::assertNothingSent();
    }

    /** @test 
     * 
     * Usuário Externo  não pode resetar senha com cadastro sem ativar.
    */
    public function cannot_send_mail_reset_password_for_user_externo_not_active()
    {
        Notification::fake();

        $user_externo = factory('App\UserExterno')->create([
            'ativo' => 0
        ]);
        $this->get(route('externo.password.request'))->assertOk();
        $this->post(route('externo.password.email'), [
            'cpf_cnpj' => $user_externo['cpf_cnpj']
        ])->assertSessionHasErrors([
            'cpf_cnpj'
        ]);

        Notification::assertNothingSent();
    }

    /** @test 
     * 
     * Usuário Externo  pode resetar senha.
    */
    public function send_mail_reset_password_for_user_externo()
    {
        Notification::fake();

        $user_externo = factory('App\UserExterno')->create();
        $this->get(route('externo.password.request'))->assertOk();
        $this->post(route('externo.password.email'), [
            'cpf_cnpj' => $user_externo['cpf_cnpj']
        ])->assertStatus(302);
        $this->get(route('externo.password.request'))
        ->assertSee('O link de reconfiguração de senha foi enviado ao email ' .$user_externo['email']);
        
        Notification::hasSent($user_externo, ResetPassword::class);
    }

    /** @test 
     * 
     * Não pode enviar email para resetar senha se o cpf / cnpj não foi encontrado.
    */
    public function cannot_send_mail_reset_password_when_not_find_cpfcnpj()
    {
        Notification::fake();

        factory('App\UserExterno')->create([
            'cpf_cnpj' => '43795442818'
        ]);
        $user_externo = factory('App\UserExterno')->raw();
        $this->get(route('externo.password.request'))->assertOk();
        $this->post(route('externo.password.email'), [
            'cpf_cnpj' => $user_externo['cpf_cnpj']
        ])->assertSessionHasErrors([
            'cpf_cnpj'
        ]);

        Notification::assertNothingSent();
    }

    /** @test 
     * 
     * Não pode enviar email para resetar senha se o cpf / cnpj está errado.
    */
    public function cannot_send_mail_reset_password_with_cpfcnpj_wrong()
    {
        $user_externo = factory('App\UserExterno')->create([
            'cpf_cnpj' => '123456789'
        ]);
        $this->get(route('externo.password.request'))->assertOk();
        $this->post(route('externo.password.email'), [
            'cpf_cnpj' => $user_externo['cpf_cnpj']
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
        $user_externo = factory('App\UserExterno')->create();
        $this->get(route('externo.password.request'));
        $this->post(route('externo.password.email'), [
            'cpf_cnpj' => $user_externo['cpf_cnpj']
        ]);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $this->assertStringContainsString($user_externo['cpf_cnpj'], $log);
        $this->assertStringContainsString('solicitou o envio de link para alterar a senha no Login Externo.', $log);
    }

    /** @test 
     * 
     * Não pode resetar senha se o cpf / cnpj está errado.
    */
    public function cannot_reset_password_with_cpfcnpj_wrong_after_verificar_email()
    {
        $user_externo = factory('App\UserExterno')->create([
            'cpf_cnpj' => '123456789'
        ]);
        $token = Password::broker('users_externo')->createToken($user_externo);
        $this->get(route('externo.password.reset', $token))->assertSuccessful();
        $this->post(route('externo.password.update'), [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
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
        $user_externo = factory('App\UserExterno')->create();
        $token = Password::broker('users_externo')->createToken($user_externo);
        $this->get(route('externo.password.reset', $token))->assertSuccessful();
        $this->post(route('externo.password.update'), [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
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
        $user_externo = factory('App\UserExterno')->create();
        $token = Password::broker('users_externo')->createToken($user_externo);
        $this->get(route('externo.password.reset', $token))->assertSuccessful();
        $this->post(route('externo.password.update'), [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
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
        $user_externo = factory('App\UserExterno')->create();
        $token = Password::broker('users_externo')->createToken($user_externo);
        $this->get(route('externo.password.reset', $token))->assertSuccessful();
        $this->post(route('externo.password.update'), [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
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
        $user_externo = factory('App\UserExterno')->create();
        $token = Password::broker('users_externo')->createToken($user_externo);
        $this->get(route('externo.password.reset', $token))->assertSuccessful();
        $this->post(route('externo.password.update'), [
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
    */
    public function cannot_reset_password_with_wrong_token()
    {
        $user_externo = factory('App\UserExterno')->create();
        $token = Password::broker('users_externo')->createToken($user_externo);
        $this->get(route('externo.password.reset', $token.'abc'))->assertSuccessful();
        $this->post(route('externo.password.update'), [
            'cpf_cnpj' => $user_externo->cpf_cnpj,
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
            'token' => $token.'abc'
        ])->assertSessionHasErrors([
            'cpf_cnpj',
        ]);
    }

    /** @test 
     * 
     * Pode resetar senha com tudo certo.
    */
    public function reset_password_after_verificar_email()
    {
        $user_externo = factory('App\UserExterno')->create();
        $token = Password::broker('users_externo')->createToken($user_externo);

        $this->get(route('externo.password.reset', $token))->assertSuccessful();
        $this->post(route('externo.password.update'), [
            'token' => $token,
            'cpf_cnpj' => $user_externo->cpf_cnpj,
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
        ])->assertRedirect(route('externo.login'));

        $this->get(route('externo.login'))
        ->assertSee('Senha alterada com sucesso. Favor realizar o login novamente com as novas informações.');
    }

    /** @test 
     * 
     * Log externo ao alterar a senha em 'Esqueci a senha'.
    */
    public function log_is_generated_when_reset_password()
    {
        $user_externo = factory('App\UserExterno')->create();
        $token = Password::broker('users_externo')->createToken($user_externo);
        $this->get(route('externo.password.reset', $token))->assertSuccessful();
        $this->post(route('externo.password.update'), [
            'token' => $token,
            'cpf_cnpj' => $user_externo->cpf_cnpj,
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
        ]);
        $log = tailCustom(storage_path($this->pathLogExterno()));
        $texto = 'Usuário com o cpf/cnpj '.$user_externo['cpf_cnpj'];
        $texto .= ' alterou a senha com sucesso na Área do Usuário Externo através do "Esqueci a senha".';
        $this->assertStringContainsString($texto, $log);
    }

    /** @test 
     * 
     * Pode editar os dados cadastrais.
    */
    public function can_after_login_update_nome_and_email()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados);
        $this->get(route('externo.editar.view'))->assertOk();
        $this->put(route('externo.editar', [
            'nome' => 'Novo nome do Usuário Externo',
            'email' => 'teste@email.com.br'
        ]));
        $this->get(route('externo.editar.view'))
        ->assertSee('Dados alterados com sucesso.');
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'nome' => mb_strtoupper('Novo nome do Usuário Externo', 'UTF-8'),
            'email' => 'teste@email.com.br'
        ]);
    }

    /** @test 
    */
    public function cannot_after_login_update_email_with_more_than_2_mails_equal()
    {
        factory('App\UserExterno')->create([
            'cpf_cnpj' => '89878398000177',
            'email' => 'teste@email.com.br'
        ]);
        factory('App\UserExterno')->create([
            'cpf_cnpj' => '98040120063',
            'email' => 'teste@email.com.br'
        ]);
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030',
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados);

        $this->get(route('externo.editar.view'))->assertOk();
        $this->put(route('externo.editar', [
            'email' => 'teste@email.com.br'
        ]));
        $this->get(route('externo.editar.view'))
        ->assertSee('Este email já alcançou o limite de cadastro, por favor insira outro.');

        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'email' => $user_externo['email']
        ]);
    }

    /** @test 
     * 
     * Log externo ao alterar os dados cadastrais.
    */
    public function log_is_generated_when_update_data()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->get(route('externo.login'));
        $this->post(route('externo.login.submit'), $dados);
        $this->get(route('externo.editar.view'));
        $this->put(route('externo.editar', [
            'nome' => 'Novo nome do Usuário Externo',
            'email' => 'teste@email.com.br'
        ]));
        $log = tailCustom(storage_path($this->pathLogExterno()));
        $this->assertStringContainsString($user_externo->id, $log);
        $this->assertStringContainsString($user_externo->cpf_cnpj, $log);
        $this->assertStringContainsString('alterou os dados com sucesso na Área Restrita após logon.', $log);
    }

    /** @test 
     * 
     * Pode editar o nome.
    */
    public function can_after_login_update_nome()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados);
        $this->get(route('externo.editar.view'))->assertOk();
        $this->put(route('externo.editar', [
            'nome' => 'Novo nome do Usuário Externo',
            'email' => $user_externo['email']
        ]));
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'nome' => mb_strtoupper('Novo nome do Usuário Externo', 'UTF-8'),
            'email' => $user_externo['email']
        ]);
    }

    /** @test 
     * 
     * Pode editar o email.
    */
    public function can_after_login_update_email()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados);
        $this->get(route('externo.editar.view'))->assertOk();
        $this->put(route('externo.editar', [
            'nome' => $user_externo['nome'],
            'email' => 'teste@teste.com.br'
        ]));
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'nome' => $user_externo['nome'],
            'email' => 'teste@teste.com.br'
        ]);
    }

    /** @test 
     * 
     * Carrega os dados cadastrais.
    */
    public function fill_data_with_nome_cpfcnpj_email()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados);
        $this->get(route('externo.editar.view'))
        ->assertSee($user_externo['nome'])
        ->assertSee($user_externo['cpf_cnpj'])
        ->assertSee($user_externo['email']);
    }

    /** @test 
     * 
     * Não pode editar os dados cadastrais sem os inputs obrigatórios.
    */
    public function cannot_after_login_update_nome_and_email_without_mandatory_inputs()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados);
        $this->get(route('externo.editar.view'))->assertOk();
        $this->put(route('externo.editar', [
            'nome' => '',
            'email' => ''
        ]))->assertSessionHasErrors([
            'nome',
            'email'
        ]);
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'nome' => $user_externo['nome'],
            'email' => $user_externo['email']
        ]);
    }

    /** @test 
     * 
     * Não pode editar o nome vazio.
    */
    public function cannot_after_login_update_nome_empty()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados);
        $this->get(route('externo.editar.view'))->assertOk();
        $this->put(route('externo.editar', [
            'nome' => ''
        ]))->assertSessionHasErrors([
            'nome'
        ]);
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'nome' => $user_externo['nome'],
            'email' => $user_externo['email']
        ]);
    }

    /** @test 
     * 
     * Não pode editar o email vazio.
    */
    public function cannot_after_login_update_email_empty()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados);
        $this->get(route('externo.editar.view'))->assertOk();
        $this->put(route('externo.editar', [
            'email' => ''
        ]))->assertSessionHasErrors([
            'email'
        ]);
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'nome' => $user_externo['nome'],
            'email' => $user_externo['email']
        ]);
    }

    /** @test 
     * 
     * Não pode editar o email errado.
    */
    public function cannot_after_login_update_email_wrong()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados);
        $this->get(route('externo.editar.view'))->assertOk();
        $this->put(route('externo.editar', [
            'email' => 'teste.com.br'
        ]))->assertSessionHasErrors([
            'email'
        ]);
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'nome' => $user_externo['nome'],
            'email' => $user_externo['email']
        ]);
    }
    
    /** @test 
     * 
     * Pode editar a senha depois de logar.
    */
    public function can_after_login_update_password()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados);
        $this->get(route('externo.editar.view'))->assertOk();
        $this->get(route('externo.editar.senha.view'))->assertOk();
        $this->put(route('externo.editar', [
            'password_atual' => 'Teste102030',
            'password' => 'Teste10203040',
            'password_confirmation' => 'Teste10203040'
        ]));
        $this->get(route('externo.editar.view'))
        ->assertSee('Dados alterados com sucesso.');
    }

    /** @test 
     * 
     * Não pode editar a senha se a atual foi digitada errada.
    */
    public function cannot_after_login_update_password_with_password_atual_wrong()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados);
        $this->get(route('externo.editar.view'))->assertOk();
        $this->get(route('externo.editar.senha.view'))->assertOk();
        $this->put(route('externo.editar', [
            'password_atual' => 'Teste10203040',
            'password' => 'Teste10203040',
            'password_confirmation' => 'Teste10203040'
        ]));
        $this->get(route('externo.editar.senha.view'))
        ->assertSee('A senha atual digitada está incorreta!');
    }

    /** @test 
     * 
     * Não pode editar a senha se está errada.
    */
    public function cannot_after_login_update_password_wrong()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados);
        $this->get(route('externo.editar.view'))->assertOk();
        $this->get(route('externo.editar.senha.view'))->assertOk();
        $this->put(route('externo.editar', [
            'password_atual' => 'Teste102030',
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
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados);
        $this->get(route('externo.editar.view'))->assertOk();
        $this->get(route('externo.editar.senha.view'))->assertOk();
        $this->put(route('externo.editar', [
            'password_atual' => 'Teste102030',
            'password' => 'Teste10203040',
            'password_confirmation' => 'teste10203040'
        ]))->assertSessionHasErrors([
            'password',
            'password_confirmation'
        ]);
    }

    /** @test 
     * 
     * Não pode editar a senha se a senha e confirmação de senha estão diferentes.
    */
    public function cannot_after_login_update_password_and_confirmation_differents()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados);
        $this->get(route('externo.editar.view'))->assertOk();
        $this->get(route('externo.editar.senha.view'))->assertOk();
        $this->put(route('externo.editar', [
            'password_atual' => 'Teste102030',
            'password' => 'Teste10203040',
            'password_confirmation' => 'Teste1020304050'
        ]))->assertSessionHasErrors([
            'password',
            'password_confirmation'
        ]);
    }

    /** @test 
     * 
     * Não pode editar a senha se a senha está vazia.
    */
    public function cannot_after_login_update_password_empty()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados);
        $this->get(route('externo.editar.view'))->assertOk();
        $this->get(route('externo.editar.senha.view'))->assertOk();
        $this->put(route('externo.editar', [
            'password_atual' => 'Teste102030',
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
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados);
        $this->get(route('externo.editar.view'))->assertOk();
        $this->get(route('externo.editar.senha.view'))->assertOk();
        $this->put(route('externo.editar', [
            'password_atual' => 'Teste102030',
            'password' => 'Teste1020304050',
            'password_confirmation' => ''
        ]))->assertSessionHasErrors([
            'password'
        ]);
    }

    /** 
     * =======================================================================================================
     * TESTES ABAS DE SERVIÇOS
     * =======================================================================================================
     */

    /** @test 
     * 
     * Pode acessar todas as abas na área restrita do Portal.
    */
    public function after_login_can_access_tabs_on_restrict()
    {
        $user_externo = factory('App\UserExterno')->create();
        $this->post(route('externo.login.submit'), [
            'cpf_cnpj' => $user_externo['cpf_cnpj'], 
            'password' => 'Teste102030'
        ]);
        $this->get(route('externo.dashboard'))->assertOk();
        $this->get(route('externo.editar.view'))->assertOk();
        $this->get(route('externo.editar.senha.view'))->assertOk();
        $this->get(route('externo.preregistro.view'))->assertOk();
    }

    /** @test 
     * 
     * Abas da área restrita não são acessíveis sem o login.
    */
    public function cannot_access_tabs_on_restrict_area_without_login()
    {
        $this->get(route('externo.dashboard'))->assertRedirect(route('externo.login'));
        $this->get(route('externo.editar.view'))->assertRedirect(route('externo.login'));
        $this->get(route('externo.editar.senha.view'))->assertRedirect(route('externo.login'));
        $this->get(route('externo.preregistro.view'))->assertRedirect(route('externo.login'));
    }
}