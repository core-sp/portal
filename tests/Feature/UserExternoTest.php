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
            'tipo_conta' => null,
            'cpf_cnpj' => null, 
            'nome' => null,
            'email' => '', 
            'password' => '', 
            'password_confirmation' => null, 
            'aceite' => ''
        ])
        ->assertSessionHasErrors([
            'tipo_conta',
            'cpf_cnpj',
            'nome',
            'email',
            'password',
            'password_confirmation', 
            'aceite'
        ]);
    }

    /** @test 
    */
    public function cannot_register_without_tipo_conta_input()
    {
        $dados = factory('App\UserExterno')->states('cadastro')->raw([
            'tipo_conta' => null,
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'tipo_conta'
        ]);

        $this->assertDatabaseMissing('users_externo', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test 
    */
    public function cannot_register_with_tipo_conta_input_invalid()
    {
        $dados = factory('App\UserExterno')->states('cadastro')->raw([
            'tipo_conta' => 'user',
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'tipo_conta'
        ]);

        $this->assertDatabaseMissing('users_externo', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test 
     * 
     * Não pode criar um registro faltando cpf/cnpj.
    */
    public function cannot_register_without_cpfcnpj_input()
    {
        $dados = factory('App\UserExterno')->states('cadastro')->raw([
            'cpf_cnpj' => null,
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
        $dados = factory('App\UserExterno')->states('cadastro')->raw([
            'nome' => '', 
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
        $dados = factory('App\UserExterno')->states('cadastro')->raw([
            'email' => '', 
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
        $dados = factory('App\UserExterno')->states('cadastro')->raw([
            'aceite' => '', 
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
        $dados = factory('App\UserExterno')->states('cadastro')->raw([
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
        $dados = factory('App\UserExterno')->states('cadastro')->raw([
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
        $dados = factory('App\UserExterno')->states('cadastro')->raw([
            'password_confirmation' => null
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
        $dados = factory('App\UserExterno')->states('cadastro')->raw([
            'email' => 'gfgfgf.com', 
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
    public function cannot_register_if_exist_cpfcnpj_in_users_externo_table()
    {
        $pre = factory('App\UserExterno')->create();
        $dados = factory('App\UserExterno')->states('cadastro')->raw([
            'cpf_cnpj' => $pre->cpf_cnpj,
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
    public function cannot_register_with_cpfcnpj_wrong()
    {
        $pre = factory('App\UserExterno')->create();
        $dados = factory('App\UserExterno')->states('cadastro')->raw([
            'cpf_cnpj' => '12345678900',
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
        $dados = factory('App\UserExterno')->states('cadastro')->raw([
            'cpf_cnpj' => '09361260000167',
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
        $dados = factory('App\UserExterno')->states('cadastro')->raw();
        $this->post(route('externo.cadastro.submit'), $dados);

        Mail::assertQueued(CadastroUserExternoMail::class);

        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $dados['cpf_cnpj'], 
            'ativo' => 0
        ]);

        // Checa se após acessar o link de confirmação, o campo "ativo" é atualizado para 1
        $this->get(route('externo.verifica-email', ['tipo' => 'user-externo', 'token'=> UserExterno::first()->verify_token]));
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $dados['cpf_cnpj'], 
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

        $dados = factory('App\UserExterno')->states('cadastro')->raw([
            'nome' => $user_externo['nome'],
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'email' => $user_externo['email'],
        ]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados);

        Mail::assertQueued(CadastroUserExternoMail::class);
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $user_externo['cpf_cnpj'], 
            'ativo' => 0,
            'deleted_at' => null
        ]);

        // Checa se após acessar o link de confirmação, o campo "ativo" é atualizado para 1
        $this->get(route('externo.verifica-email', ['tipo' => 'user-externo', 'token'=> UserExterno::first()->verify_token]));
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $user_externo['cpf_cnpj'], 
            'ativo' => 1
        ]);
    }

    /** @test 
     * 
     * Pode se registrar se ativo 0.
    */
    public function register_new_user_externo_when_ativo_0_after_24h()
    {
        Mail::fake();

        $user_externo = factory('App\UserExterno')->create([
            'ativo' => 0,
        ]);
        UserExterno::first()->update(['updated_at' => Carbon::today()->subDay()]);

        $dados = factory('App\UserExterno')->states('cadastro')->raw([
            'nome' => $user_externo['nome'],
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'email' => $user_externo['email'],
        ]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados);

        Mail::assertQueued(CadastroUserExternoMail::class);
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $user_externo['cpf_cnpj'], 
            'ativo' => 0,
            'deleted_at' => null
        ]);

        // Checa se após acessar o link de confirmação, o campo "ativo" é atualizado para 1
        $this->get(route('externo.verifica-email', ['tipo' => 'user-externo', 'token'=> UserExterno::first()->verify_token]));
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

        $dados = factory('App\UserExterno')->states('cadastro')->raw([
            'nome' => $user_externo['nome'],
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'email' => $user_externo['email'],
        ]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados);

        $this->get(route('externo.cadastro'))
        ->assertSeeText('Esta conta já solicitou o cadastro. Verifique seu email para ativar. Caso não tenha mais acesso ao e-mail, aguarde 24h para se recadastrar');

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

        $dados = factory('App\UserExterno')->states('cadastro')->raw([
            'cpf_cnpj' => '36982299007',
        ]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados);

        Mail::assertQueued(CadastroUserExternoMail::class);

        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $dados['cpf_cnpj'], 
            'ativo' => 0
        ]);

        UserExterno::first()->update(['updated_at' => Carbon::today()->subDays(2)]);
        
        $this->get(route('externo.verifica-email', ['tipo' => 'user-externo', 'token'=> UserExterno::first()->verify_token]))
        ->assertRedirect(route('externo.login'));

        $this->get(route('externo.login'))
        ->assertSeeText('Falha na verificação. Caso e-mail já tenha sido verificado, basta logar na área restrita do Login Externo, caso contrário, por favor refazer cadastro no Login Externo.');
        
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $dados['cpf_cnpj'], 
            'ativo' => 0
        ]);
    }

    /** @test 
    */
    public function cannot_verify_mail_with_wrong_token()
    {
        Mail::fake();

        $dados = factory('App\UserExterno')->states('cadastro')->raw([
            'cpf_cnpj' => '36982299007',
        ]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados);

        Mail::assertQueued(CadastroUserExternoMail::class);

        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => '36982299007', 
            'ativo' => 0
        ]);
        
        $this->get(route('externo.verifica-email', ['tipo' => 'user-externo', 'token'=> UserExterno::first()->verify_token . '5']))
        ->assertStatus(302);

        $this->get(route('externo.login'))
        ->assertSeeText('Falha na verificação. Caso e-mail já tenha sido verificado, basta logar na área restrita do Login Externo, caso contrário, por favor refazer cadastro no Login Externo.');
        
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => '36982299007', 
            'ativo' => 0
        ]);
    }

    /** @test 
    */
    public function cannot_verify_mail_with_wrong_tipo()
    {
        Mail::fake();

        $dados = factory('App\UserExterno')->states('cadastro')->raw([
            'cpf_cnpj' => '36982299007',
        ]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados);

        Mail::assertQueued(CadastroUserExternoMail::class);

        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => '36982299007', 
            'ativo' => 0
        ]);
        
        $this->get(route('externo.verifica-email', ['tipo' => 'user-externos', 'token'=> UserExterno::first()->verify_token]))
        ->assertNotFound();
        
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

        $dados = factory('App\UserExterno')->states('cadastro')->raw([
            'cpf_cnpj' => '36982299007',
        ]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados);

        Mail::assertQueued(CadastroUserExternoMail::class);

        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => '36982299007', 
            'ativo' => 0
        ]);

        UserExterno::first()->update(['updated_at' => Carbon::today()->subDays(2)]);
        $this->get(route('externo.verifica-email', ['tipo' => 'user-externo', 'token'=> UserExterno::first()->verify_token]))
        ->assertRedirect(route('externo.login'));

        $this->get(route('externo.login'))
        ->assertSeeText('Falha na verificação. Caso e-mail já tenha sido verificado, basta logar na área restrita do Login Externo, caso contrário, por favor refazer cadastro no Login Externo.');
        
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => '36982299007', 
            'ativo' => 0
        ]);

        $dados = factory('App\UserExterno')->states('cadastro')->raw([
            'cpf_cnpj' => '36982299007',
        ]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados);

        Mail::assertQueued(CadastroUserExternoMail::class);

        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => '36982299007', 
            'ativo' => 0,
            'deleted_at' => null
        ]);

        // Checa se após acessar o link de confirmação, o campo "ativo" é atualizado para 1
        $this->get(route('externo.verifica-email', ['tipo' => 'user-externo', 'token'=> UserExterno::first()->verify_token]));
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

        $dados = factory('App\UserExterno')->states('cadastro')->raw([
            'nome' => $user_externo['nome'],
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'email' => $user_externo['email'],
        ]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados);

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
        $dados = factory('App\UserExterno')->states('cadastro')->raw([
            'cpf_cnpj' => '36982299007',
        ]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . '"'.formataCpfCnpj($dados['cpf_cnpj']).'" ("'.$dados['email'].'") cadastrou-se na Área do Login Externo como Usuário Externo.';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test 
     * 
     * Log externo ao verificar email.
    */
    public function log_is_generated_when_verifica_email()
    {
        $dados = factory('App\UserExterno')->states('cadastro')->raw([
            'cpf_cnpj' => '36982299007',
        ]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados);

        $this->get(route('externo.verifica-email', ['tipo' => 'user-externo', 'token'=> UserExterno::first()->verify_token]));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário Externo 1 ("'.formataCpfCnpj($dados['cpf_cnpj']).'") verificou o email após o cadastro.';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test 
     * 
     * Usuário Externo  pode logar na área restrita do Portal.
    */
    public function login_on_externo()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'tipo_conta' => 'user_externo',
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
            'tipo_conta' => 'user_externo',
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->post(route('externo.login.submit'), $dados);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário '.$user_externo['nome'] . ' ("'.formataCpfCnpj($user_externo['cpf_cnpj']). '") conectou-se à Área do Usuário Externo.';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test 
     * 
     * Log externo ao logar.
    */
    public function log_is_generated_when_logout()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'tipo_conta' => 'user_externo',
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->post(route('externo.login.submit'), $dados);
        $this->post(route('externo.logout'));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário '.$user_externo['nome'] . ' ("'.formataCpfCnpj($user_externo['cpf_cnpj']). '") desconectou-se da Área do Usuário Externo.';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test 
     * 
     * Log externo ao logar.
    */
    public function log_is_generated_when_logout_without_session()
    {
        $this->post(route('externo.logout'));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Sessão expirou / não há sessão ativa ao realizar o logout da Área do Usuário Externo / Contabilidade.';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test 
     * 
     * Log externo ao não conseguir logar com cpf/cnpj válido, mas não existe no banco.
    */
    public function log_is_generated_when_failed_logon()
    {
        factory('App\UserExterno')->create();
        $dados = [
            'tipo_conta' => 'user_externo',
            'cpf_cnpj' => '72027756000135',
            'password' => 'Teste102030'
        ];
        $this->post(route('externo.login.submit'), $dados);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário não encontrado com o cpf/cnpj "' .$dados['cpf_cnpj']. '" não conseguiu logar na Área do Usuário Externo / Contabilidade.';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test 
     * 
     * Log externo ao não conseguir logar com senha errada.
    */
    public function log_is_generated_when_failed_logon_with_password_wrong()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'tipo_conta' => 'user_externo',
            'cpf_cnpj' => $user_externo->cpf_cnpj,
            'password' => 'Teste10203040'
        ];
        $this->post(route('externo.login.submit'), $dados);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário com o cpf/cnpj '.$user_externo->cpf_cnpj . ' não conseguiu logar na Área do Usuário Externo.';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test 
     * 
     * Usuário Externo  não pode logar se não está cadastrado.
    */
    public function cannot_login_on_externo_without_registration()
    {
        $user_externo = factory('App\UserExterno')->raw();
        $dados = [
            'tipo_conta' => 'user_externo',
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
            'tipo_conta' => 'user_externo',
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
            'tipo_conta' => 'user_externo',
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030',
        ])
        ->assertRedirect(route('externo.login'));
        $this->get(route('externo.login'))->assertSeeText('CPF/CNPJ não encontrado.');
    }

    /** @test 
    */
    public function cannot_login_on_externo_with_tipo_wrong()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'tipo_conta' => 'users_externo',
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados)
        ->assertSessionHasErrors([
            'tipo_conta'
        ]);
    }

    /** @test 
     * 
     * Usuário Externo  não pode logar se senha está errada.
    */
    public function cannot_login_on_externo_with_password_wrong()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'tipo_conta' => 'user_externo',
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
    public function actived_user_await_login_after_3x()
    {
        $user_externo = factory('App\UserExterno')->create();

        for($i = 0; $i < 3; $i++)
        {
            $this->get(route('externo.login'))->assertOk();
            $this->post(route('externo.login.submit'), [
                'tipo_conta' => 'user_externo',
                'cpf_cnpj' => $user_externo['cpf_cnpj'],
                'password' => 'Teste10203',
            ])
            ->assertRedirect(route('externo.login'));

            $this->get(route('externo.login'))
            ->assertDontSeeText('Login inválido devido à quantidade de tentativas. Tente novamente em');
        }

        $this->post(route('externo.login.submit'), [
            'tipo_conta' => 'user_externo',
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
            'tipo_conta' => 'user_externo',
            'cpf_cnpj' => '72027756000135',
            'password' => 'Teste102030'
        ];
        for($i = 0; $i < 4; $i++)
            $this->post(route('externo.login.submit'), $dados);
            
        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário com cpf/cnpj "'.$dados['cpf_cnpj'].'" foi bloqueado temporariamente por alguns segundos devido a alcançar ';
        $txt .= 'o limite de tentativas de login na Área do Usuário Externo.';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test 
    */
    public function log_is_generated_when_lockout_logon_with_cpf_cnpj_not_actived()
    {
        $externo = factory('App\UserExterno')->create([
            'ativo' => 0
        ]);
        $dados = [
            'tipo_conta' => 'user_externo',
            'cpf_cnpj' => $externo->cpf_cnpj,
            'password' => 'Teste102030'
        ];
        for($i = 0; $i < 4; $i++)
            $this->post(route('externo.login.submit'), $dados);
            
        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário com cpf/cnpj "'.$dados['cpf_cnpj'].'" foi bloqueado temporariamente por alguns segundos devido a alcançar ';
        $txt .= 'o limite de tentativas de login na Área do Usuário Externo.';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test 
    */
    public function log_is_generated_when_lockout_logon()
    {
        $externo = factory('App\UserExterno')->create();
        $dados = [
            'tipo_conta' => 'user_externo',
            'cpf_cnpj' => $externo->cpf_cnpj,
            'password' => 'Teste1020'
        ];
        for($i = 0; $i < 4; $i++)
            $this->post(route('externo.login.submit'), $dados);
            
        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário com cpf/cnpj "'.$dados['cpf_cnpj'].'" foi bloqueado temporariamente por alguns segundos devido a alcançar ';
        $txt .= 'o limite de tentativas de login na Área do Usuário Externo.';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test 
    */
    public function not_actived_user_await_login_after_3x()
    {
        $user_externo = factory('App\UserExterno')->create([
            'ativo' => 0
        ]);

        for($i = 0; $i < 3; $i++)
        {
            $this->get(route('externo.login'))->assertOk();
            $this->post(route('externo.login.submit'), [
                'tipo_conta' => 'user_externo',
                'cpf_cnpj' => $user_externo['cpf_cnpj'],
                'password' => 'Teste10203',
            ])
            ->assertRedirect(route('externo.login'));

            $this->get(route('externo.login'))
            ->assertDontSeeText('Login inválido devido à quantidade de tentativas. Tente novamente em');
        }

        $this->post(route('externo.login.submit'), [
            'tipo_conta' => 'user_externo',
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
                'tipo_conta' => 'user_externo',
                'cpf_cnpj' => $user_externo['cpf_cnpj'],
                'password' => 'Teste10203',
            ])
            ->assertRedirect(route('externo.login'));

            $this->get(route('externo.login'))
            ->assertDontSeeText('Login inválido devido à quantidade de tentativas. Tente novamente em');
        }

        $this->post(route('externo.login.submit'), [
            'tipo_conta' => 'user_externo',
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
            'tipo_conta' => 'user_externo',
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
    public function cannot_send_mail_reset_password_for_user_externo_not_actived()
    {
        Notification::fake();

        $user_externo = factory('App\UserExterno')->create([
            'ativo' => 0
        ]);
        $this->get(route('externo.password.request'))->assertOk();
        $this->post(route('externo.password.email'), [
            'tipo_conta' => 'user_externo',
            'cpf_cnpj' => $user_externo['cpf_cnpj']
        ])->assertSessionHasErrors([
            'cpf_cnpj'
        ]);

        Notification::assertNothingSent();
    }

    /** @test 
    */
    public function cannot_send_mail_reset_password_for_user_externo_tipo_invalid()
    {
        Notification::fake();

        $user_externo = factory('App\UserExterno')->create();
        $this->get(route('externo.password.request'))->assertOk();
        $this->post(route('externo.password.email'), [
            'tipo_conta' => 'user_externos',
            'cpf_cnpj' => $user_externo['cpf_cnpj']
        ])->assertSessionHasErrors([
            'tipo_conta'
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
            'tipo_conta' => 'user_externo',
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
            'tipo_conta' => 'user_externo',
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
            'tipo_conta' => 'user_externo',
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
            'tipo_conta' => 'user_externo',
            'cpf_cnpj' => $user_externo['cpf_cnpj']
        ]);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário com o cpf/cnpj '.$user_externo['cpf_cnpj'].' do tipo de conta "Usuário Externo" solicitou o envio de link para alterar a senha no Login Externo.';
        $this->assertStringContainsString($txt, $log);
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
            'tipo_conta' => 'user_externo',
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
            'tipo_conta' => 'user_externo',
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
            'tipo_conta' => 'user_externo',
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
            'tipo_conta' => 'user_externo',
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
            'tipo_conta' => '',
            'cpf_cnpj' => '',
            'password' => '', 
            'password_confirmation' => '', 
            'token' => $token
        ])->assertSessionHasErrors([
            'tipo_conta',
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
            'tipo_conta' => 'user_externo',
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
    */
    public function cannot_reset_password_with_wrong_tipo()
    {
        $user_externo = factory('App\UserExterno')->create();
        $token = Password::broker('users_externo')->createToken($user_externo);
        $this->get(route('externo.password.reset', $token.'abc'))->assertSuccessful();
        $this->post(route('externo.password.update'), [
            'tipo_conta' => 'userexterno',
            'cpf_cnpj' => $user_externo->cpf_cnpj,
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
            'token' => $token
        ])->assertSessionHasErrors([
            'tipo_conta',
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
            'tipo_conta' => 'user_externo',
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

        $this->get(route('externo.password.reset', $token))
        ->assertSee('<label for="password-text" class="m-0 p-0">Força da senha</label>')
        ->assertSee('<div class="progress" id="password-text"></div>')
        ->assertSee('<small><em>Em caso de senha fraca ou média, considere alterá-la para sua segurança.</em></small>')
        ->assertSuccessful();

        $this->post(route('externo.password.update'), [
            'tipo_conta' => 'user_externo',
            'token' => $token,
            'cpf_cnpj' => $user_externo->cpf_cnpj,
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
        ]);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário com o cpf/cnpj '.$user_externo['cpf_cnpj'];
        $txt .= ' alterou a senha com sucesso na Área do Usuário Externo através do "Esqueci a senha".';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function log_is_generated_when_bot_try_login()
    {
        $user_externo = factory('App\UserExterno')->create();

        $this->get(route('externo.login'))->assertOk();

        $this->post(route('externo.login.submit'), ['cpf_cnpj' => $user_externo['cpf_cnpj'], 'password' => 'teste1020', 'email_system' => '1'])
        ->assertRedirect(route('externo.login'));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $texto = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $texto .= 'Possível bot tentou login com cpf/cnpj "' .apenasNumeros($user_externo->cpf_cnpj). '" como Usuário Externo, mas impedido de verificar o usuário no banco de dados.';
        $this->assertStringContainsString($texto, $log);
    }

    /** @test */
    public function same_ip_when_lockout_user_externo_by_csrf_token_can_login_on_portal()
    {
        $user = factory('App\User')->create([
            'password' => bcrypt('TestePorta1@')
        ]);
        $user_externo = factory('App\UserExterno')->create();

        $this->get('/')->assertOk();
        $csrf = csrf_token();

        for($i = 0; $i < 4; $i++)
        {
            $this->get(route('externo.login'));
            $this->assertEquals($csrf, request()->session()->get('_token'));
            $this->post(route('externo.login.submit'), [
                'tipo_conta' => 'user_externo',
                'cpf_cnpj' => $user_externo['cpf_cnpj'], 
                'password' => 'teste1020'
            ]);
            $this->assertEquals($csrf, request()->session()->get('_token'));
        }

        $this->post('admin/login', [
            'login' => $user->username, 
            'password' => 'TestePorta1'
        ]);
        $this->assertEquals($csrf, request()->session()->get('_token'));
        $this->get('admin/login')
        ->assertSee('Login inválido devido à quantidade de tentativas.');
        $this->assertEquals($csrf, request()->session()->get('_token'));

        request()->session()->regenerate();

        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), [
            'tipo_conta' => 'user_externo',
            'cpf_cnpj' => $user_externo['cpf_cnpj'], 
            'password' => 'Teste102030'
        ])
        ->assertRedirect(route('externo.dashboard'));
    }

    /** @test */
    public function cannot_view_form_when_bot_try_login_on_restrict_area()
    {
        $user_externo = factory('App\UserExterno')->create();

        $this->get(route('externo.login'))->assertOk();

        $this->post(route('externo.login.submit'), [
            'tipo_conta' => 'user_externo',
            'cpf_cnpj' => $user_externo['cpf_cnpj'], 
            'password' => 'teste1020', 'email_system' => '1'
        ])
        ->assertRedirect(route('externo.login'));

        $this->get(route('externo.login'))
        ->assertDontSee('<label for="login">CPF ou CNPJ</label>')
        ->assertDontSee('<label for="password">Senha</label>')
        ->assertDontSee('<button type="submit" class="btn btn-primary">Entrar</button>');
    }

    /** @test */
    public function can_view_strength_bar_password_login_on_restrict_area()
    {
        $user_externo = factory('App\UserExterno')->create();

        $this->get(route('externo.login'))
        ->assertSee('<label for="password-text" class="m-0 p-0">Força da senha</label>')
        ->assertSee('<div class="progress" id="password-text"></div>')
        ->assertSee('<small><em>Em caso de senha fraca ou média, considere alterá-la para sua segurança.</em></small>')
        ->assertOk();
    }

    /** @test 
     * 
     * Pode editar os dados cadastrais.
    */
    public function can_after_login_update_nome_and_email()
    {
        $user_externo = $this->signInAsUserExterno();
        
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

        $user_externo = $this->signInAsUserExterno();

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
        $user_externo = $this->signInAsUserExterno();

        $this->get(route('externo.editar.view'));
        $this->put(route('externo.editar', [
            'nome' => 'Novo nome do Usuário Externo',
            'email' => 'teste@email.com.br'
        ]));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário Externo ' . $user_externo->id . ' ("'. formataCpfCnpj($user_externo->cpf_cnpj) .'")';
        $txt .= ' alterou os dados com sucesso na Área Restrita após logon.';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test 
     * 
     * Pode editar o nome.
    */
    public function can_after_login_update_nome()
    {
        $user_externo = $this->signInAsUserExterno();

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
        $user_externo = $this->signInAsUserExterno();

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
        $user_externo = $this->signInAsUserExterno();

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
        $user_externo = $this->signInAsUserExterno();

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
        $user_externo = $this->signInAsUserExterno();

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
        $user_externo = $this->signInAsUserExterno();

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
        $user_externo = $this->signInAsUserExterno();

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
        Mail::fake();

        $user_externo = $this->signInAsUserExterno();

        $this->get(route('externo.editar.view'))->assertOk();
        $this->get(route('externo.editar.senha.view'))
        ->assertSee('<label for="password-text" class="m-0 p-0">Força da senha</label>')
        ->assertSee('<div class="progress" id="password-text"></div>')
        ->assertSee('<small><em>Em caso de senha fraca ou média, considere alterá-la para sua segurança.</em></small>')
        ->assertOk();
        $this->put(route('externo.editar', [
            'password_atual' => 'Teste102030',
            'password' => 'Teste10203040',
            'password_confirmation' => 'Teste10203040'
        ]));
        $this->get(route('externo.editar.view'))
        ->assertSee('Dados alterados com sucesso.');

        Mail::assertQueued(CadastroUserExternoMail::class);
    }

    /** @test */
    public function log_is_generated_when_change_password_on_restrict_area()
    {
        $user_externo = $this->signInAsUserExterno();

        $this->put(route('externo.editar', [
            'password_atual' => 'Teste102030',
            'password' => 'TestePortal123@#$%&',
            'password_confirmation' => 'TestePortal123@#$%&', 
        ]));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $texto = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $texto .= 'Usuário Externo ' . $user_externo->id . ' ("'. formataCpfCnpj($user_externo->cpf_cnpj) .'") alterou a senha com sucesso na Área Restrita após logon.';
        $this->assertStringContainsString($texto, $log);
    }

    /** @test 
     * 
     * Não pode editar a senha se a atual foi digitada errada.
    */
    public function cannot_after_login_update_password_with_password_atual_wrong()
    {
        $user_externo = $this->signInAsUserExterno();

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
        $user_externo = $this->signInAsUserExterno();

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
        $user_externo = $this->signInAsUserExterno();

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
        $user_externo = $this->signInAsUserExterno();

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
        $user_externo = $this->signInAsUserExterno();

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
        $user_externo = $this->signInAsUserExterno();

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
        $user_externo = $this->signInAsUserExterno();

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

    /** 
     * =======================================================================================================
     * TESTES PRÉ-REGISTRO USUÁRIO EXTERNO
     * =======================================================================================================
     */

    /** @test */
    public function cannot_access_links_pre_registro_contabilidade()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        
        $this->get(route('externo.relacao.preregistros'))->assertRedirect(route('externo.login'));

        $this->post(route('externo.contabil.inserir.preregistro'), [
            'cpf_cnpj' => '49931920000112', 
            'email' => 'teste@teste.com', 
            'nome' => 'Teste Nome'
        ])->assertRedirect(route('externo.login'));

        $this->get(route('externo.relacao.preregistros', ['preRegistro' => 1]))->assertRedirect(route('externo.login'));
    }

    /** @test */
    public function cannot_access_links_pre_registro_with_parameter_pre_registro()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on', 'preRegistro' => 1]))
        ->assertRedirect(route('externo.dashboard'));

        $pr = factory('App\PreRegistroCpf')->states('request')->make();
        $dados = $pr->final;
        $pr = $pr->makeHidden(['final'])->attributesToArray();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertRedirect(route('externo.dashboard'));

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertRedirect(route('externo.dashboard'));

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'contabil',
            'campo' => 'cnpj_contabil',
            'valor' => '78087976000130'
        ])->assertRedirect(route('externo.dashboard'));

        $this->get(route('externo.preregistro.anexo.download', ['id' => 1, 'preRegistro' => 1]))
        ->assertRedirect(route('externo.dashboard'));

        $this->delete(route('externo.preregistro.anexo.excluir', ['id' => 1, 'preRegistro' => 1]))
        ->assertRedirect(route('externo.dashboard'));
    }

    /** @test */
    public function can_register_when_created_by_contabilidade()
    {
        Mail::fake();

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $dados['cpf_cnpj'], 
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'ativo' => 0,
            'aceite' => 0
        ]);

        $dados = factory('App\UserExterno')->states('cadastro')->raw();
        $this->post(route('externo.cadastro.submit'), $dados);

        Mail::assertQueued(CadastroUserExternoMail::class);
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $dados['cpf_cnpj'], 
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'ativo' => 0,
            'aceite' => 1
        ]);

        $this->get(route('externo.verifica-email', ['tipo' => 'user-externo', 'token'=> UserExterno::first()->verify_token]));
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $dados['cpf_cnpj'], 
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'ativo' => 1,
            'aceite' => 1
        ]);
    }

    /** @test */
    public function can_register_when_ativo_0_after_24h_when_created_by_contabilidade()
    {
        Mail::fake();

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $dados['cpf_cnpj'], 
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'ativo' => 0,
            'aceite' => 0
        ]);

        UserExterno::first()->update(['updated_at' => Carbon::today()->subDays(2)]);

        $dados = factory('App\UserExterno')->states('cadastro')->raw();
        $this->post(route('externo.cadastro.submit'), $dados);

        Mail::assertQueued(CadastroUserExternoMail::class);
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $dados['cpf_cnpj'], 
            'aceite' => 1,
            'ativo' => 0,
            'deleted_at' => null
        ]);

        $this->get(route('externo.verifica-email', ['tipo' => 'user-externo', 'token'=> UserExterno::first()->verify_token]));
        $this->assertDatabaseHas('users_externo', [
            'cpf_cnpj' => $dados['cpf_cnpj'], 
            'ativo' => 1,
            'aceite' => 1
        ]);
    }
}