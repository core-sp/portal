<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreRegistroMail;
use App\PreRegistro;
use App\Contabil;
use Illuminate\Support\Arr;
use Notification;
use Illuminate\Support\Facades\Password;
use Carbon\Carbon;
use App\Mail\CadastroUserExternoMail;

class ContabilTest extends TestCase
{
    use RefreshDatabase;

    /** 
     * =======================================================================================================
     * TESTES LOGIN CONTABIL
     * =======================================================================================================
     */

    /** @test */
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

    /** @test */
    public function cannot_register_without_tipo_conta_input()
    {
        $dados = factory('App\Contabil')->states('cadastro')->raw([
            'tipo_conta' => null,
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'tipo_conta'
        ]);

        $this->assertDatabaseMissing('contabeis', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test */
    public function cannot_register_with_tipo_conta_input_invalid()
    {
        $dados = factory('App\Contabil')->states('cadastro')->raw([
            'tipo_conta' => 'contabill',
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'tipo_conta'
        ]);

        $this->assertDatabaseMissing('contabeis', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test */
    public function cannot_register_without_cpfcnpj_input()
    {
        $dados = factory('App\Contabil')->states('cadastro')->raw([
            'cpf_cnpj' => null,
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'cpf_cnpj'
        ]);

        $this->assertDatabaseMissing('contabeis', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test */
    public function cannot_register_without_nome_input()
    {
        $dados = factory('App\Contabil')->states('cadastro')->raw([
            'nome' => '', 
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'nome'
        ]);

        $this->assertDatabaseMissing('contabeis', [
            'email' => $dados['email']
        ]);
    }

    /** @test */
    public function cannot_register_without_email_input()
    {
        $dados = factory('App\Contabil')->states('cadastro')->raw([
            'email' => '', 
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'email'
        ]);

        $this->assertDatabaseMissing('contabeis', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test */
    public function cannot_register_without_checkbox_input()
    {
        $dados = factory('App\Contabil')->states('cadastro')->raw([
            'aceite' => '', 
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'aceite'
        ]);

        $this->assertDatabaseMissing('contabeis', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test */
    public function cannot_register_with_password_wrong()
    {
        // Faltando letra maiuscula e mais um caracter
        $dados = factory('App\Contabil')->states('cadastro')->raw([
            'password' => 'teste10',
            'password_confirmation' => 'teste10'
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'password'
        ]);

        $this->assertDatabaseMissing('contabeis', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test */
    public function cannot_register_with_password_and_confirmation_differents()
    {
        $dados = factory('App\Contabil')->states('cadastro')->raw([
            'password' => 'Teste102030',
            'password_confirmation' => 'teste102030'
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'password_confirmation'
        ]);

        $this->assertDatabaseMissing('contabeis', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test */
    public function cannot_register_without_password_confirmation()
    {
        $dados = factory('App\Contabil')->states('cadastro')->raw([
            'password_confirmation' => null
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'password'
        ]);

        $this->assertDatabaseMissing('contabeis', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test */
    public function cannot_register_with_email_wrong()
    {
        $dados = factory('App\Contabil')->states('cadastro')->raw([
            'email' => 'gfgfgf.com', 
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'email'
        ]);

        $this->assertDatabaseMissing('contabeis', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test */
    public function cannot_register_if_exist_cpfcnpj_in_contabil_table()
    {
        $pre = factory('App\Contabil')->create();
        $dados = factory('App\Contabil')->states('cadastro')->raw([
            'cpf_cnpj' => $pre->cpf_cnpj,
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'cpf_cnpj'
        ]);
        $this->assertDatabaseMissing('contabeis', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test */
    public function cannot_register_with_cpfcnpj_wrong()
    {
        $pre = factory('App\Contabil')->create();
        $dados = factory('App\Contabil')->states('cadastro')->raw([
            'cpf_cnpj' => '12345678900',
        ]);
        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertSessionHasErrors([
            'cpf_cnpj'
        ]);
        $this->assertDatabaseMissing('contabeis', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test */
    public function cannot_register_if_exist_more_than_two_email_equals()
    {
        $pre = factory('App\Contabil')->create();
        $pre2 = factory('App\Contabil')->create([
            'cnpj' => '49931920000112',
            'email' => $pre->email
        ]);
        $dados = factory('App\Contabil')->states('cadastro')->raw([
            'cpf_cnpj' => '09361260000167',
            'email' => $pre->email
        ]);
        $this->get(route('externo.cadastro'))->assertOk();

        $this->post(route('externo.cadastro.submit'), $dados)
        ->assertRedirect(route('externo.cadastro'));

        $this->get(route('externo.cadastro'))
        ->assertSeeText('Este email já alcançou o limite de cadastro, por favor insira outro.');

        $this->assertDatabaseMissing('contabeis', [
            'nome' => $dados['nome']
        ]);
    }

    /** @test */
    public function register_new_contabil()
    {
        Mail::fake();

        $this->get(route('externo.cadastro'))->assertOk();
        $dados = factory('App\Contabil')->states('cadastro')->raw();
        $dados['cpf_cnpj'] = $dados['cnpj'];

        $this->post(route('externo.cadastro.submit'), $dados);

        Mail::assertQueued(CadastroUserExternoMail::class);

        $this->assertDatabaseHas('contabeis', [
            'cnpj' => $dados['cpf_cnpj'], 
            'ativo' => 0
        ]);

        // Checa se após acessar o link de confirmação, o campo "ativo" é atualizado para 1
        $this->get(route('externo.verifica-email', ['tipo' => 'contabil', 'token'=> Contabil::first()->verify_token]));
        $this->assertDatabaseHas('contabeis', [
            'cnpj' => $dados['cpf_cnpj'], 
            'ativo' => 1
        ]);
    }

    /** @test */
    public function register_new_contabil_when_deleted_and_ativo_0_after_24h()
    {
        Mail::fake();

        $user_externo = factory('App\Contabil')->create([
            'ativo' => 0,
        ]);
        $user_externo->delete();
        Contabil::withTrashed()->first()->update(['updated_at' => Carbon::today()->subDay()]);

        $dados = factory('App\Contabil')->states('cadastro')->raw([
            'nome' => $user_externo['nome'],
            'cpf_cnpj' => $user_externo['cnpj'],
            'email' => $user_externo['email'],
        ]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados);

        Mail::assertQueued(CadastroUserExternoMail::class);
        $this->assertDatabaseHas('contabeis', [
            'cnpj' => $user_externo['cnpj'], 
            'ativo' => 0,
            'deleted_at' => null
        ]);

        // Checa se após acessar o link de confirmação, o campo "ativo" é atualizado para 1
        $this->get(route('externo.verifica-email', ['tipo' => 'contabil', 'token'=> Contabil::first()->verify_token]));
        $this->assertDatabaseHas('contabeis', [
            'cnpj' => $user_externo['cnpj'], 
            'ativo' => 1
        ]);
    }

    /** @test */
    public function cannot_register_new_contabil_when_ativo_0_in_24h()
    {
        Mail::fake();

        $user_externo = factory('App\Contabil')->create([
            'ativo' => 0,
        ]);

        $dados = factory('App\Contabil')->states('cadastro')->raw([
            'nome' => $user_externo['nome'],
            'cpf_cnpj' => $user_externo['cnpj'],
            'email' => $user_externo['email'],
        ]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados);

        $this->get(route('externo.cadastro'))
        ->assertSeeText('Esta conta já solicitou o cadastro. Verifique seu email para ativar. Caso não tenha mais acesso ao e-mail, aguarde 24h para se recadastrar');

        Mail::assertNotQueued(CadastroUserExternoMail::class);
        $this->assertDatabaseHas('contabeis', [
            'cnpj' => $user_externo['cnpj'], 
            'ativo' => 0,
            'deleted_at' => null
        ]);
    }

    /** @test */
    public function cannot_to_active_register_after_24h()
    {
        Mail::fake();

        $dados = factory('App\Contabil')->states('cadastro')->raw([
            'cpf_cnpj' => '49931920000112',
        ]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados);

        Mail::assertQueued(CadastroUserExternoMail::class);

        $this->assertDatabaseHas('contabeis', [
            'cnpj' => $dados['cpf_cnpj'], 
            'ativo' => 0
        ]);

        Contabil::first()->update(['updated_at' => Carbon::today()->subDays(2)]);
        
        $this->get(route('externo.verifica-email', ['tipo' => 'contabil', 'token'=> Contabil::first()->verify_token]))
        ->assertRedirect(route('externo.login'));

        $this->get(route('externo.login'))
        ->assertSeeText('Falha na verificação. Caso e-mail já tenha sido verificado, basta logar na área restrita do Login Externo, caso contrário, por favor refazer cadastro no Login Externo.');
        
        $this->assertDatabaseHas('contabeis', [
            'cnpj' => $dados['cpf_cnpj'], 
            'ativo' => 0
        ]);
    }

    /** @test */
    public function cannot_verify_mail_with_wrong_token()
    {
        Mail::fake();

        $dados = factory('App\Contabil')->states('cadastro')->raw([
            'cpf_cnpj' => '49931920000112',
        ]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados);

        Mail::assertQueued(CadastroUserExternoMail::class);

        $this->assertDatabaseHas('contabeis', [
            'cnpj' => '49931920000112', 
            'ativo' => 0
        ]);
        
        $this->get(route('externo.verifica-email', ['tipo' => 'contabil', 'token'=> Contabil::first()->verify_token . '5']))
        ->assertStatus(302);

        $this->get(route('externo.login'))
        ->assertSeeText('Falha na verificação. Caso e-mail já tenha sido verificado, basta logar na área restrita do Login Externo, caso contrário, por favor refazer cadastro no Login Externo.');
        
        $this->assertDatabaseHas('contabeis', [
            'cnpj' => '49931920000112', 
            'ativo' => 0
        ]);
    }

    /** @test */
    public function cannot_verify_mail_with_wrong_tipo()
    {
        Mail::fake();

        $dados = factory('App\Contabil')->states('cadastro')->raw([
            'cpf_cnpj' => '49931920000112',
        ]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados);

        Mail::assertQueued(CadastroUserExternoMail::class);

        $this->assertDatabaseHas('contabeis', [
            'cnpj' => '49931920000112', 
            'ativo' => 0
        ]);
        
        $this->get(route('externo.verifica-email', ['tipo' => 'user_externos', 'token'=> Contabil::first()->verify_token]))
        ->assertNotFound();
        
        $this->assertDatabaseHas('contabeis', [
            'cnpj' => '49931920000112', 
            'ativo' => 0
        ]);
    }

    /** @test */
    public function register_after_24h_and_verify_mail()
    {
        Mail::fake();

        $dados = factory('App\Contabil')->states('cadastro')->raw([
            'cpf_cnpj' => '49931920000112',
        ]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados);

        Mail::assertQueued(CadastroUserExternoMail::class);

        $this->assertDatabaseHas('contabeis', [
            'cnpj' => '49931920000112', 
            'ativo' => 0
        ]);

        Contabil::first()->update(['updated_at' => Carbon::today()->subDays(2)]);
        $this->get(route('externo.verifica-email', ['tipo' => 'contabil', 'token'=> Contabil::first()->verify_token]))
        ->assertRedirect(route('externo.login'));

        $this->get(route('externo.login'))
        ->assertSeeText('Falha na verificação. Caso e-mail já tenha sido verificado, basta logar na área restrita do Login Externo, caso contrário, por favor refazer cadastro no Login Externo.');
        
        $this->assertDatabaseHas('contabeis', [
            'cnpj' => '49931920000112', 
            'ativo' => 0
        ]);

        $dados = factory('App\Contabil')->states('cadastro')->raw([
            'cpf_cnpj' => '49931920000112',
        ]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados);

        Mail::assertQueued(CadastroUserExternoMail::class);

        $this->assertDatabaseHas('contabeis', [
            'cnpj' => '49931920000112', 
            'ativo' => 0,
            'deleted_at' => null
        ]);

        // Checa se após acessar o link de confirmação, o campo "ativo" é atualizado para 1
        $this->get(route('externo.verifica-email', ['tipo' => 'contabil', 'token'=> Contabil::first()->verify_token]));
        $this->assertDatabaseHas('contabeis', [
            'cnpj' => '49931920000112', 
            'ativo' => 1
        ]);
    }

    /** @test */
    public function cannot_register_new_contabil_when_ativo_0_and_deleted_in_24h()
    {
        Mail::fake();

        $user_externo = factory('App\Contabil')->create([
            'ativo' => 0,
        ]);

        $dados = factory('App\Contabil')->states('cadastro')->raw([
            'nome' => $user_externo['nome'],
            'cpf_cnpj' => $user_externo['cnpj'],
            'email' => $user_externo['email'],
        ]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados);

        $this->get(route('externo.cadastro'))
        ->assertSeeText('Esta conta já solicitou o cadastro. Verifique seu email para ativar. Caso não tenha mais acesso ao e-mail, aguarde 24h para se recadastrar');

        Mail::assertNotQueued(CadastroUserExternoMail::class);

        $this->assertDatabaseHas('contabeis', [
            'cnpj' => $user_externo->cnpj, 
            'ativo' => 0
        ]);
    }

    /** @test */
    public function log_is_generated_when_new_contabil()
    {
        $dados = factory('App\Contabil')->states('cadastro')->raw([
            'cpf_cnpj' => '49931920000112',
        ]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . '"'.formataCpfCnpj($dados['cpf_cnpj']).'" ("'.$dados['email'].'") cadastrou-se na Área do Login Externo como Contabilidade.';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function log_is_generated_when_verifica_email()
    {
        $dados = factory('App\Contabil')->states('cadastro')->raw([
            'cpf_cnpj' => '49931920000112',
        ]);

        $this->get(route('externo.cadastro'))->assertOk();
        $this->post(route('externo.cadastro.submit'), $dados);

        $this->get(route('externo.verifica-email', ['tipo' => 'contabil', 'token'=> Contabil::first()->verify_token]));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Contabilidade 1 ("'.formataCpfCnpj($dados['cpf_cnpj']).'") verificou o email após o cadastro.';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function login_on_externo()
    {
        $user_externo = factory('App\Contabil')->create();
        $dados = [
            'tipo_conta' => 'contabil',
            'cpf_cnpj' => $user_externo['cnpj'],
            'password' => 'Teste102030'
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados)
        ->assertRedirect(route('externo.dashboard'));
    }

    /** @test */
    public function log_is_generated_when_logon()
    {
        $user_externo = factory('App\Contabil')->create();
        $dados = [
            'tipo_conta' => 'contabil',
            'cpf_cnpj' => $user_externo['cnpj'],
            'password' => 'Teste102030'
        ];
        $this->post(route('externo.login.submit'), $dados);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário '.$user_externo['nome'] . ' ("'.formataCpfCnpj($user_externo['cnpj']). '") conectou-se à Área da Contabilidade.';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function log_is_generated_when_logout()
    {
        $user_externo = factory('App\Contabil')->create();
        $dados = [
            'tipo_conta' => 'contabil',
            'cpf_cnpj' => $user_externo['cnpj'],
            'password' => 'Teste102030'
        ];
        $this->post(route('externo.login.submit'), $dados);
        $this->post(route('externo.logout'));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário '.$user_externo['nome'] . ' ("'.formataCpfCnpj($user_externo['cnpj']). '") desconectou-se da Área da Contabilidade.';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function log_is_generated_when_logout_without_session()
    {
        $this->post(route('externo.logout'));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Sessão expirou / não há sessão ativa ao realizar o logout da Área do Usuário Externo / Contabilidade.';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function log_is_generated_when_failed_logon()
    {
        factory('App\Contabil')->create();
        $dados = [
            'tipo_conta' => 'contabil',
            'cpf_cnpj' => '72027756000135',
            'password' => 'Teste102030'
        ];
        $this->post(route('externo.login.submit'), $dados);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário não encontrado com o cpf/cnpj "' .$dados['cpf_cnpj']. '" não conseguiu logar na Área do Usuário Externo / Contabilidade.';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function log_is_generated_when_failed_logon_with_password_wrong()
    {
        $user_externo = factory('App\Contabil')->create();
        $dados = [
            'tipo_conta' => 'contabil',
            'cpf_cnpj' => $user_externo->cnpj,
            'password' => 'Teste10203040'
        ];
        $this->post(route('externo.login.submit'), $dados);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário com o cpf/cnpj '.$user_externo->cnpj . ' não conseguiu logar na Área da Contabilidade.';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function cannot_login_on_externo_without_registration()
    {
        $user_externo = factory('App\Contabil')->raw();
        $dados = [
            'tipo_conta' => 'contabil',
            'cpf_cnpj' => $user_externo['cnpj'],
            'password' => 'Teste102030'
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados)
        ->assertRedirect(route('externo.login'));

        $this->get(route('externo.login'))->assertSeeText('CPF/CNPJ não encontrado.');
    }

    /** @test */
    public function cannot_login_on_externo_with_ativo_0()
    {
        $user_externo = factory('App\Contabil')->create([
            'ativo' => 0
        ]);
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), [
            'tipo_conta' => 'contabil',
            'cpf_cnpj' => $user_externo['cnpj'],
            'password' => 'Teste102030',
        ])
        ->assertRedirect(route('externo.login'));
        $this->get(route('externo.login'))->assertSeeText('Por favor, acesse o email informado no momento do cadastro para verificar sua conta.');
    }

    /** @test */
    public function cannot_login_on_externo_when_deleted()
    {
        $user_externo = factory('App\Contabil')->create();
        $user_externo->delete();

        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), [
            'tipo_conta' => 'contabil',
            'cpf_cnpj' => $user_externo['cnpj'],
            'password' => 'Teste102030',
        ])
        ->assertRedirect(route('externo.login'));
        $this->get(route('externo.login'))->assertSeeText('CPF/CNPJ não encontrado.');
    }

    /** @test */
    public function cannot_login_on_externo_with_tipo_wrong()
    {
        $user_externo = factory('App\Contabil')->create();
        $dados = [
            'tipo_conta' => 'users_externo',
            'cpf_cnpj' => $user_externo['cnpj'],
            'password' => 'Teste102030'
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados)
        ->assertSessionHasErrors([
            'tipo_conta'
        ]);
    }

    /** @test */
    public function cannot_login_on_externo_with_password_wrong()
    {
        $user_externo = factory('App\Contabil')->create();
        $dados = [
            'tipo_conta' => 'contabil',
            'cpf_cnpj' => $user_externo['cnpj'],
            'password' => 'teste102030'
        ];
        $this->get(route('externo.login'))->assertOk();
        $this->post(route('externo.login.submit'), $dados)
        ->assertRedirect(route('externo.login'));
        $this->get(route('externo.login'))->assertSeeText('Login inválido');
    }

    /** @test */
    public function actived_user_await_login_after_3x()
    {
        $user_externo = factory('App\Contabil')->create();

        for($i = 0; $i < 3; $i++)
        {
            $this->get(route('externo.login'))->assertOk();
            $this->post(route('externo.login.submit'), [
                'tipo_conta' => 'contabil',
                'cpf_cnpj' => $user_externo['cnpj'],
                'password' => 'Teste10203',
            ])
            ->assertRedirect(route('externo.login'));

            $this->get(route('externo.login'))
            ->assertDontSeeText('Login inválido devido à quantidade de tentativas. Tente novamente em');
        }

        $this->post(route('externo.login.submit'), [
            'tipo_conta' => 'contabil',
            'cpf_cnpj' => $user_externo['cnpj'],
            'password' => 'Teste10203',
        ])->assertSessionHasErrors([
            'cnpj'
        ])->assertRedirect(route('externo.login'));
        
        $this->get(route('externo.login'))
        ->assertSeeText('Login inválido devido à quantidade de tentativas. Tente novamente em');
    }

    /** @test */
    public function log_is_generated_when_lockout_logon_with_cpf_cnpj_not_created()
    {
        $externo = factory('App\Contabil')->create();
        $dados = [
            'tipo_conta' => 'contabil',
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

    /** @test */
    public function log_is_generated_when_lockout_logon_with_cpf_cnpj_not_actived()
    {
        $externo = factory('App\Contabil')->create([
            'ativo' => 0
        ]);
        $dados = [
            'tipo_conta' => 'contabil',
            'cpf_cnpj' => $externo->cnpj,
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

    /** @test */
    public function log_is_generated_when_lockout_logon()
    {
        $externo = factory('App\Contabil')->create();
        $dados = [
            'tipo_conta' => 'contabil',
            'cpf_cnpj' => $externo->cnpj,
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

    /** @test */
    public function not_actived_user_await_login_after_3x()
    {
        $user_externo = factory('App\Contabil')->create([
            'ativo' => 0
        ]);

        for($i = 0; $i < 3; $i++)
        {
            $this->get(route('externo.login'))->assertOk();
            $this->post(route('externo.login.submit'), [
                'tipo_conta' => 'contabil',
                'cpf_cnpj' => $user_externo['cnpj'],
                'password' => 'Teste10203',
            ])
            ->assertRedirect(route('externo.login'));

            $this->get(route('externo.login'))
            ->assertDontSeeText('Login inválido devido à quantidade de tentativas. Tente novamente em');
        }

        $this->post(route('externo.login.submit'), [
            'tipo_conta' => 'contabil',
            'cpf_cnpj' => $user_externo['cnpj'],
            'password' => 'Teste10203',
        ])->assertSessionHasErrors([
            'cnpj'
        ])->assertRedirect(route('externo.login'));
        
        $this->get(route('externo.login'))
        ->assertSeeText('Login inválido devido à quantidade de tentativas. Tente novamente em');
    }

    /** @test */
    public function not_registered_user_await_login_after_3x()
    {
        $user_externo = factory('App\Contabil')->make();

        for($i = 0; $i < 3; $i++)
        {
            $this->get(route('externo.login'))->assertOk();
            $this->post(route('externo.login.submit'), [
                'tipo_conta' => 'contabil',
                'cpf_cnpj' => $user_externo['cnpj'],
                'password' => 'Teste10203',
            ])
            ->assertRedirect(route('externo.login'));

            $this->get(route('externo.login'))
            ->assertDontSeeText('Login inválido devido à quantidade de tentativas. Tente novamente em');
        }

        $this->post(route('externo.login.submit'), [
            'tipo_conta' => 'contabil',
            'cpf_cnpj' => $user_externo['cnpj'],
            'password' => 'Teste10203',
        ])->assertSessionHasErrors([
            'cnpj'
        ])->assertRedirect(route('externo.login'));
        
        $this->get(route('externo.login'))
        ->assertSeeText('Login inválido devido à quantidade de tentativas. Tente novamente em');
    }

    /** @test */
    public function cannot_send_mail_reset_password_for_contabil_not_created()
    {
        Notification::fake();

        $user_externo = factory('App\Contabil')->raw();
        $this->get(route('externo.password.request'))->assertOk();
        $this->post(route('externo.password.email'), [
            'tipo_conta' => 'contabil',
            'cpf_cnpj' => $user_externo['cnpj']
        ])->assertSessionHasErrors([
            'cpf_cnpj'
        ]);

        Notification::assertNothingSent();
    }

    /** @test */
    public function cannot_send_mail_reset_password_for_contabil_not_actived()
    {
        Notification::fake();

        $user_externo = factory('App\Contabil')->create([
            'ativo' => 0
        ]);
        $this->get(route('externo.password.request'))->assertOk();
        $this->post(route('externo.password.email'), [
            'tipo_conta' => 'contabil',
            'cpf_cnpj' => $user_externo['cnpj']
        ])->assertSessionHasErrors([
            'cpf_cnpj'
        ]);

        Notification::assertNothingSent();
    }

    /** @test */
    public function cannot_send_mail_reset_password_for_contabil_tipo_invalid()
    {
        Notification::fake();

        $user_externo = factory('App\Contabil')->create();
        $this->get(route('externo.password.request'))->assertOk();
        $this->post(route('externo.password.email'), [
            'tipo_conta' => 'user_externos',
            'cpf_cnpj' => $user_externo['cnpj']
        ])->assertSessionHasErrors([
            'tipo_conta'
        ]);

        Notification::assertNothingSent();
    }

    /** @test */
    public function send_mail_reset_password_for_contabil()
    {
        Notification::fake();

        $user_externo = factory('App\Contabil')->create();
        $this->get(route('externo.password.request'))->assertOk();
        $this->post(route('externo.password.email'), [
            'tipo_conta' => 'contabil',
            'cpf_cnpj' => $user_externo['cnpj']
        ])->assertStatus(302);
        $this->get(route('externo.password.request'))
        ->assertSee('O link de reconfiguração de senha foi enviado ao email ' .$user_externo['email']);
        
        Notification::hasSent($user_externo, ResetPassword::class);
    }

    // /** @test 
    //  * 
    //  * Não pode enviar email para resetar senha se o cpf / cnpj não foi encontrado.
    // */
    // public function cannot_send_mail_reset_password_when_not_find_cpfcnpj()
    // {
    //     Notification::fake();

    //     factory('App\Contabil')->create([
    //         'cpf_cnpj' => '43795442818'
    //     ]);
    //     $user_externo = factory('App\Contabil')->raw();
    //     $this->get(route('externo.password.request'))->assertOk();
    //     $this->post(route('externo.password.email'), [
    //         'tipo_conta' => 'contabil',
    //         'cpf_cnpj' => $user_externo['cpf_cnpj']
    //     ])->assertSessionHasErrors([
    //         'cpf_cnpj'
    //     ]);

    //     Notification::assertNothingSent();
    // }

    // /** @test 
    //  * 
    //  * Não pode enviar email para resetar senha se o cpf / cnpj está errado.
    // */
    // public function cannot_send_mail_reset_password_with_cpfcnpj_wrong()
    // {
    //     $user_externo = factory('App\Contabil')->create([
    //         'cpf_cnpj' => '123456789'
    //     ]);
    //     $this->get(route('externo.password.request'))->assertOk();
    //     $this->post(route('externo.password.email'), [
    //         'tipo_conta' => 'contabil',
    //         'cpf_cnpj' => $user_externo['cpf_cnpj']
    //     ])->assertSessionHasErrors([
    //         'cpf_cnpj'
    //     ]);
    // }

    // /** @test 
    //  * 
    //  * Log externo ao alterar a senha em 'Esqueci a senha'.
    // */
    // public function log_is_generated_when_send_mail_reset_password()
    // {
    //     $user_externo = factory('App\Contabil')->create();
    //     $this->get(route('externo.password.request'));
    //     $this->post(route('externo.password.email'), [
    //         'tipo_conta' => 'contabil',
    //         'cpf_cnpj' => $user_externo['cpf_cnpj']
    //     ]);

    //     $log = tailCustom(storage_path($this->pathLogExterno()));
    //     $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
    //     $txt = $inicio . 'Usuário com o cpf/cnpj '.$user_externo['cpf_cnpj'].' do tipo de conta "Usuário Externo" solicitou o envio de link para alterar a senha no Login Externo.';
    //     $this->assertStringContainsString($txt, $log);
    // }

    // /** @test 
    //  * 
    //  * Não pode resetar senha se o cpf / cnpj está errado.
    // */
    // public function cannot_reset_password_with_cpfcnpj_wrong_after_verificar_email()
    // {
    //     $user_externo = factory('App\Contabil')->create([
    //         'cpf_cnpj' => '123456789'
    //     ]);
    //     $token = Password::broker('users_externo')->createToken($user_externo);
    //     $this->get(route('externo.password.reset', $token))->assertSuccessful();
    //     $this->post(route('externo.password.update'), [
    //         'tipo_conta' => 'contabil',
    //         'cpf_cnpj' => $user_externo['cpf_cnpj'],
    //         'password' => 'Teste102030', 
    //         'password_confirmation' => 'Teste102030', 
    //         'token' => $token
    //     ])->assertSessionHasErrors([
    //         'cpf_cnpj'
    //     ]);
    // }

    // /** @test 
    //  * 
    //  * Não pode resetar senha se a senha está errada.
    // */
    // public function cannot_reset_password_with_password_wrong_after_verificar_email()
    // {
    //     $user_externo = factory('App\Contabil')->create();
    //     $token = Password::broker('users_externo')->createToken($user_externo);
    //     $this->get(route('externo.password.reset', $token))->assertSuccessful();
    //     $this->post(route('externo.password.update'), [
    //         'tipo_conta' => 'contabil',
    //         'cpf_cnpj' => $user_externo['cpf_cnpj'],
    //         'password' => 'teste102030', 
    //         'password_confirmation' => 'teste102030', 
    //         'token' => $token
    //     ])->assertSessionHasErrors([
    //         'password'
    //     ]);
    // }

    // /** @test 
    //  * 
    //  * Não pode resetar senha se a confirmação de senha está errada.
    // */
    // public function cannot_reset_password_with_password_confirmation_wrong_after_verificar_email()
    // {
    //     $user_externo = factory('App\Contabil')->create();
    //     $token = Password::broker('users_externo')->createToken($user_externo);
    //     $this->get(route('externo.password.reset', $token))->assertSuccessful();
    //     $this->post(route('externo.password.update'), [
    //         'tipo_conta' => 'contabil',
    //         'cpf_cnpj' => $user_externo['cpf_cnpj'],
    //         'password' => 'Teste102030', 
    //         'password_confirmation' => 'teste102030', 
    //         'token' => $token
    //     ])->assertSessionHasErrors([
    //         'password_confirmation'
    //     ]);
    // }

    // /** @test 
    //  * 
    //  * Não pode resetar senha se a senha e confirmação de senha estão diferentes.
    // */
    // public function cannot_reset_password_with_password_and_confirmation_differents_after_verificar_email()
    // {
    //     $user_externo = factory('App\Contabil')->create();
    //     $token = Password::broker('users_externo')->createToken($user_externo);
    //     $this->get(route('externo.password.reset', $token))->assertSuccessful();
    //     $this->post(route('externo.password.update'), [
    //         'tipo_conta' => 'contabil',
    //         'cpf_cnpj' => $user_externo['cpf_cnpj'],
    //         'password' => 'Teste102030', 
    //         'password_confirmation' => 'Teste10203040', 
    //         'token' => $token
    //     ])->assertSessionHasErrors([
    //         'password_confirmation'
    //     ]);
    // }

    // /** @test 
    //  * 
    //  * Não pode resetar senha se os campos obrigatórios estão faltando.
    // */
    // public function cannot_reset_password_without_mandatory_inputs_after_verificar_email()
    // {
    //     $user_externo = factory('App\Contabil')->create();
    //     $token = Password::broker('users_externo')->createToken($user_externo);
    //     $this->get(route('externo.password.reset', $token))->assertSuccessful();
    //     $this->post(route('externo.password.update'), [
    //         'tipo_conta' => '',
    //         'cpf_cnpj' => '',
    //         'password' => '', 
    //         'password_confirmation' => '', 
    //         'token' => $token
    //     ])->assertSessionHasErrors([
    //         'tipo_conta',
    //         'cpf_cnpj',
    //         'password',
    //         'password_confirmation'
    //     ]);
    // }

    // /** @test 
    //  * 
    // */
    // public function cannot_reset_password_with_wrong_token()
    // {
    //     $user_externo = factory('App\Contabil')->create();
    //     $token = Password::broker('users_externo')->createToken($user_externo);
    //     $this->get(route('externo.password.reset', $token.'abc'))->assertSuccessful();
    //     $this->post(route('externo.password.update'), [
    //         'tipo_conta' => 'contabil',
    //         'cpf_cnpj' => $user_externo->cpf_cnpj,
    //         'password' => 'Teste102030', 
    //         'password_confirmation' => 'Teste102030', 
    //         'token' => $token.'abc'
    //     ])->assertSessionHasErrors([
    //         'cpf_cnpj',
    //     ]);
    // }

    // /** @test 
    //  * 
    // */
    // public function cannot_reset_password_with_wrong_tipo()
    // {
    //     $user_externo = factory('App\Contabil')->create();
    //     $token = Password::broker('users_externo')->createToken($user_externo);
    //     $this->get(route('externo.password.reset', $token.'abc'))->assertSuccessful();
    //     $this->post(route('externo.password.update'), [
    //         'tipo_conta' => 'userexterno',
    //         'cpf_cnpj' => $user_externo->cpf_cnpj,
    //         'password' => 'Teste102030', 
    //         'password_confirmation' => 'Teste102030', 
    //         'token' => $token
    //     ])->assertSessionHasErrors([
    //         'tipo_conta',
    //     ]);
    // }

    // /** @test 
    //  * 
    //  * Pode resetar senha com tudo certo.
    // */
    // public function reset_password_after_verificar_email()
    // {
    //     $user_externo = factory('App\Contabil')->create();
    //     $token = Password::broker('users_externo')->createToken($user_externo);

    //     $this->get(route('externo.password.reset', $token))->assertSuccessful();
    //     $this->post(route('externo.password.update'), [
    //         'tipo_conta' => 'contabil',
    //         'token' => $token,
    //         'cpf_cnpj' => $user_externo->cpf_cnpj,
    //         'password' => 'Teste102030', 
    //         'password_confirmation' => 'Teste102030', 
    //     ])->assertRedirect(route('externo.login'));

    //     $this->get(route('externo.login'))
    //     ->assertSee('Senha alterada com sucesso. Favor realizar o login novamente com as novas informações.');
    // }

    // /** @test 
    //  * 
    //  * Log externo ao alterar a senha em 'Esqueci a senha'.
    // */
    // public function log_is_generated_when_reset_password()
    // {
    //     $user_externo = factory('App\Contabil')->create();
    //     $token = Password::broker('users_externo')->createToken($user_externo);

    //     $this->get(route('externo.password.reset', $token))
    //     ->assertSee('<label for="password-text" class="m-0 p-0">Força da senha</label>')
    //     ->assertSee('<div class="progress" id="password-text"></div>')
    //     ->assertSee('<small><em>Em caso de senha fraca ou média, considere alterá-la para sua segurança.</em></small>')
    //     ->assertSuccessful();

    //     $this->post(route('externo.password.update'), [
    //         'tipo_conta' => 'contabil',
    //         'token' => $token,
    //         'cpf_cnpj' => $user_externo->cpf_cnpj,
    //         'password' => 'Teste102030', 
    //         'password_confirmation' => 'Teste102030', 
    //     ]);

    //     $log = tailCustom(storage_path($this->pathLogExterno()));
    //     $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
    //     $txt = $inicio . 'Usuário com o cpf/cnpj '.$user_externo['cpf_cnpj'];
    //     $txt .= ' alterou a senha com sucesso na Área do Usuário Externo através do "Esqueci a senha".';
    //     $this->assertStringContainsString($txt, $log);
    // }

    // /** @test */
    // public function log_is_generated_when_bot_try_login()
    // {
    //     $user_externo = factory('App\Contabil')->create();

    //     $this->get(route('externo.login'))->assertOk();

    //     $this->post(route('externo.login.submit'), ['cpf_cnpj' => $user_externo['cpf_cnpj'], 'password' => 'teste1020', 'email_system' => '1'])
    //     ->assertRedirect(route('externo.login'));

    //     $log = tailCustom(storage_path($this->pathLogExterno()));
    //     $texto = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
    //     $texto .= 'Possível bot tentou login com cpf/cnpj "' .apenasNumeros($user_externo->cpf_cnpj). '" como Usuário Externo, mas impedido de verificar o usuário no banco de dados.';
    //     $this->assertStringContainsString($texto, $log);
    // }

    // /** @test */
    // public function same_ip_when_lockout_contabil_by_csrf_token_can_login_on_portal()
    // {
    //     $user = factory('App\User')->create([
    //         'password' => bcrypt('TestePorta1@')
    //     ]);
    //     $user_externo = factory('App\Contabil')->create();

    //     $this->get('/')->assertOk();
    //     $csrf = csrf_token();

    //     for($i = 0; $i < 4; $i++)
    //     {
    //         $this->get(route('externo.login'));
    //         $this->assertEquals($csrf, request()->session()->get('_token'));
    //         $this->post(route('externo.login.submit'), [
    //             'tipo_conta' => 'contabil',
    //             'cpf_cnpj' => $user_externo['cpf_cnpj'], 
    //             'password' => 'teste1020'
    //         ]);
    //         $this->assertEquals($csrf, request()->session()->get('_token'));
    //     }

    //     $this->post('admin/login', [
    //         'login' => $user->username, 
    //         'password' => 'TestePorta1'
    //     ]);
    //     $this->assertEquals($csrf, request()->session()->get('_token'));
    //     $this->get('admin/login')
    //     ->assertSee('Login inválido devido à quantidade de tentativas.');
    //     $this->assertEquals($csrf, request()->session()->get('_token'));

    //     request()->session()->regenerate();

    //     $this->get(route('externo.login'))->assertOk();
    //     $this->post(route('externo.login.submit'), [
    //         'tipo_conta' => 'contabil',
    //         'cpf_cnpj' => $user_externo['cpf_cnpj'], 
    //         'password' => 'Teste102030'
    //     ])
    //     ->assertRedirect(route('externo.dashboard'));
    // }

    // /** @test */
    // public function cannot_view_form_when_bot_try_login_on_restrict_area()
    // {
    //     $user_externo = factory('App\Contabil')->create();

    //     $this->get(route('externo.login'))->assertOk();

    //     $this->post(route('externo.login.submit'), [
    //         'tipo_conta' => 'contabil',
    //         'cpf_cnpj' => $user_externo['cpf_cnpj'], 
    //         'password' => 'teste1020', 'email_system' => '1'
    //     ])
    //     ->assertRedirect(route('externo.login'));

    //     $this->get(route('externo.login'))
    //     ->assertDontSee('<label for="login">CPF ou CNPJ</label>')
    //     ->assertDontSee('<label for="password">Senha</label>')
    //     ->assertDontSee('<button type="submit" class="btn btn-primary">Entrar</button>');
    // }

    // /** @test */
    // public function can_view_strength_bar_password_login_on_restrict_area()
    // {
    //     $user_externo = factory('App\Contabil')->create();

    //     $this->get(route('externo.login'))
    //     ->assertSee('<label for="password-text" class="m-0 p-0">Força da senha</label>')
    //     ->assertSee('<div class="progress" id="password-text"></div>')
    //     ->assertSee('<small><em>Em caso de senha fraca ou média, considere alterá-la para sua segurança.</em></small>')
    //     ->assertOk();
    // }

    // /** @test 
    //  * 
    //  * Pode editar os dados cadastrais.
    // */
    // public function can_after_login_update_nome_and_email()
    // {
    //     $user_externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.editar.view'))->assertOk();
    //     $this->put(route('externo.editar', [
    //         'nome' => 'Novo nome do Usuário Externo',
    //         'email' => 'teste@email.com.br'
    //     ]));
    //     $this->get(route('externo.editar.view'))
    //     ->assertSee('Dados alterados com sucesso.');
    //     $this->assertDatabaseHas('contabeis', [
    //         'cpf_cnpj' => $user_externo['cpf_cnpj'],
    //         'nome' => mb_strtoupper('Novo nome do Usuário Externo', 'UTF-8'),
    //         'email' => 'teste@email.com.br'
    //     ]);
    // }

    // /** @test 
    // */
    // public function cannot_after_login_update_email_with_more_than_2_mails_equal()
    // {
    //     factory('App\Contabil')->create([
    //         'cpf_cnpj' => '89878398000177',
    //         'email' => 'teste@email.com.br'
    //     ]);
    //     factory('App\Contabil')->create([
    //         'cpf_cnpj' => '98040120063',
    //         'email' => 'teste@email.com.br'
    //     ]);

    //     $user_externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.editar.view'))->assertOk();
    //     $this->put(route('externo.editar', [
    //         'email' => 'teste@email.com.br'
    //     ]));
    //     $this->get(route('externo.editar.view'))
    //     ->assertSee('Este email já alcançou o limite de cadastro, por favor insira outro.');

    //     $this->assertDatabaseHas('contabeis', [
    //         'cpf_cnpj' => $user_externo['cpf_cnpj'],
    //         'email' => $user_externo['email']
    //     ]);
    // }

    // /** @test 
    //  * 
    //  * Log externo ao alterar os dados cadastrais.
    // */
    // public function log_is_generated_when_update_data()
    // {
    //     $user_externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.editar.view'));
    //     $this->put(route('externo.editar', [
    //         'nome' => 'Novo nome do Usuário Externo',
    //         'email' => 'teste@email.com.br'
    //     ]));

    //     $log = tailCustom(storage_path($this->pathLogExterno()));
    //     $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
    //     $txt = $inicio . 'Usuário Externo ' . $user_externo->id . ' ("'. formataCpfCnpj($user_externo->cpf_cnpj) .'")';
    //     $txt .= ' alterou os dados com sucesso na Área Restrita após logon.';
    //     $this->assertStringContainsString($txt, $log);
    // }

    // /** @test 
    //  * 
    //  * Pode editar o nome.
    // */
    // public function can_after_login_update_nome()
    // {
    //     $user_externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.editar.view'))->assertOk();
    //     $this->put(route('externo.editar', [
    //         'nome' => 'Novo nome do Usuário Externo',
    //         'email' => $user_externo['email']
    //     ]));
    //     $this->assertDatabaseHas('contabeis', [
    //         'cpf_cnpj' => $user_externo['cpf_cnpj'],
    //         'nome' => mb_strtoupper('Novo nome do Usuário Externo', 'UTF-8'),
    //         'email' => $user_externo['email']
    //     ]);
    // }

    // /** @test 
    //  * 
    //  * Pode editar o email.
    // */
    // public function can_after_login_update_email()
    // {
    //     $user_externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.editar.view'))->assertOk();
    //     $this->put(route('externo.editar', [
    //         'nome' => $user_externo['nome'],
    //         'email' => 'teste@teste.com.br'
    //     ]));
    //     $this->assertDatabaseHas('contabeis', [
    //         'cpf_cnpj' => $user_externo['cpf_cnpj'],
    //         'nome' => $user_externo['nome'],
    //         'email' => 'teste@teste.com.br'
    //     ]);
    // }

    // /** @test 
    //  * 
    //  * Carrega os dados cadastrais.
    // */
    // public function fill_data_with_nome_cpfcnpj_email()
    // {
    //     $user_externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.editar.view'))
    //     ->assertSee($user_externo['nome'])
    //     ->assertSee($user_externo['cpf_cnpj'])
    //     ->assertSee($user_externo['email']);
    // }

    // /** @test 
    //  * 
    //  * Não pode editar os dados cadastrais sem os inputs obrigatórios.
    // */
    // public function cannot_after_login_update_nome_and_email_without_mandatory_inputs()
    // {
    //     $user_externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.editar.view'))->assertOk();
    //     $this->put(route('externo.editar', [
    //         'nome' => '',
    //         'email' => ''
    //     ]))->assertSessionHasErrors([
    //         'nome',
    //         'email'
    //     ]);
    //     $this->assertDatabaseHas('contabeis', [
    //         'cpf_cnpj' => $user_externo['cpf_cnpj'],
    //         'nome' => $user_externo['nome'],
    //         'email' => $user_externo['email']
    //     ]);
    // }

    // /** @test 
    //  * 
    //  * Não pode editar o nome vazio.
    // */
    // public function cannot_after_login_update_nome_empty()
    // {
    //     $user_externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.editar.view'))->assertOk();
    //     $this->put(route('externo.editar', [
    //         'nome' => ''
    //     ]))->assertSessionHasErrors([
    //         'nome'
    //     ]);
    //     $this->assertDatabaseHas('contabeis', [
    //         'cpf_cnpj' => $user_externo['cpf_cnpj'],
    //         'nome' => $user_externo['nome'],
    //         'email' => $user_externo['email']
    //     ]);
    // }

    // /** @test 
    //  * 
    //  * Não pode editar o email vazio.
    // */
    // public function cannot_after_login_update_email_empty()
    // {
    //     $user_externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.editar.view'))->assertOk();
    //     $this->put(route('externo.editar', [
    //         'email' => ''
    //     ]))->assertSessionHasErrors([
    //         'email'
    //     ]);
    //     $this->assertDatabaseHas('contabeis', [
    //         'cpf_cnpj' => $user_externo['cpf_cnpj'],
    //         'nome' => $user_externo['nome'],
    //         'email' => $user_externo['email']
    //     ]);
    // }

    // /** @test 
    //  * 
    //  * Não pode editar o email errado.
    // */
    // public function cannot_after_login_update_email_wrong()
    // {
    //     $user_externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.editar.view'))->assertOk();
    //     $this->put(route('externo.editar', [
    //         'email' => 'teste.com.br'
    //     ]))->assertSessionHasErrors([
    //         'email'
    //     ]);
    //     $this->assertDatabaseHas('contabeis', [
    //         'cpf_cnpj' => $user_externo['cpf_cnpj'],
    //         'nome' => $user_externo['nome'],
    //         'email' => $user_externo['email']
    //     ]);
    // }
    
    // /** @test 
    //  * 
    //  * Pode editar a senha depois de logar.
    // */
    // public function can_after_login_update_password()
    // {
    //     Mail::fake();

    //     $user_externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.editar.view'))->assertOk();
    //     $this->get(route('externo.editar.senha.view'))
    //     ->assertSee('<label for="password-text" class="m-0 p-0">Força da senha</label>')
    //     ->assertSee('<div class="progress" id="password-text"></div>')
    //     ->assertSee('<small><em>Em caso de senha fraca ou média, considere alterá-la para sua segurança.</em></small>')
    //     ->assertOk();
    //     $this->put(route('externo.editar', [
    //         'password_atual' => 'Teste102030',
    //         'password' => 'Teste10203040',
    //         'password_confirmation' => 'Teste10203040'
    //     ]));
    //     $this->get(route('externo.editar.view'))
    //     ->assertSee('Dados alterados com sucesso.');

    //     Mail::assertQueued(CadastroUserExternoMail::class);
    // }

    // /** @test */
    // public function log_is_generated_when_change_password_on_restrict_area()
    // {
    //     $user_externo = $this->signInAsUserExterno();

    //     $this->put(route('externo.editar', [
    //         'password_atual' => 'Teste102030',
    //         'password' => 'TestePortal123@#$%&',
    //         'password_confirmation' => 'TestePortal123@#$%&', 
    //     ]));

    //     $log = tailCustom(storage_path($this->pathLogExterno()));
    //     $texto = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
    //     $texto .= 'Usuário Externo ' . $user_externo->id . ' ("'. formataCpfCnpj($user_externo->cpf_cnpj) .'") alterou a senha com sucesso na Área Restrita após logon.';
    //     $this->assertStringContainsString($texto, $log);
    // }

    // /** @test 
    //  * 
    //  * Não pode editar a senha se a atual foi digitada errada.
    // */
    // public function cannot_after_login_update_password_with_password_atual_wrong()
    // {
    //     $user_externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.editar.view'))->assertOk();
    //     $this->get(route('externo.editar.senha.view'))->assertOk();
    //     $this->put(route('externo.editar', [
    //         'password_atual' => 'Teste10203040',
    //         'password' => 'Teste10203040',
    //         'password_confirmation' => 'Teste10203040'
    //     ]));
    //     $this->get(route('externo.editar.senha.view'))
    //     ->assertSee('A senha atual digitada está incorreta!');
    // }

    // /** @test 
    //  * 
    //  * Não pode editar a senha se está errada.
    // */
    // public function cannot_after_login_update_password_wrong()
    // {
    //     $user_externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.editar.view'))->assertOk();
    //     $this->get(route('externo.editar.senha.view'))->assertOk();
    //     $this->put(route('externo.editar', [
    //         'password_atual' => 'Teste102030',
    //         'password' => 'teste10203040',
    //         'password_confirmation' => 'teste10203040'
    //     ]))->assertSessionHasErrors([
    //         'password'
    //     ]);
    // }

    // /** @test 
    //  * 
    //  * Não pode editar a senha se a confirmação de senha está errada.
    // */
    // public function cannot_after_login_update_password_confirmation_wrong()
    // {
    //     $user_externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.editar.view'))->assertOk();
    //     $this->get(route('externo.editar.senha.view'))->assertOk();
    //     $this->put(route('externo.editar', [
    //         'password_atual' => 'Teste102030',
    //         'password' => 'Teste10203040',
    //         'password_confirmation' => 'teste10203040'
    //     ]))->assertSessionHasErrors([
    //         'password',
    //         'password_confirmation'
    //     ]);
    // }

    // /** @test 
    //  * 
    //  * Não pode editar a senha se a senha e confirmação de senha estão diferentes.
    // */
    // public function cannot_after_login_update_password_and_confirmation_differents()
    // {
    //     $user_externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.editar.view'))->assertOk();
    //     $this->get(route('externo.editar.senha.view'))->assertOk();
    //     $this->put(route('externo.editar', [
    //         'password_atual' => 'Teste102030',
    //         'password' => 'Teste10203040',
    //         'password_confirmation' => 'Teste1020304050'
    //     ]))->assertSessionHasErrors([
    //         'password',
    //         'password_confirmation'
    //     ]);
    // }

    // /** @test 
    //  * 
    //  * Não pode editar a senha se a senha está vazia.
    // */
    // public function cannot_after_login_update_password_empty()
    // {
    //     $user_externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.editar.view'))->assertOk();
    //     $this->get(route('externo.editar.senha.view'))->assertOk();
    //     $this->put(route('externo.editar', [
    //         'password_atual' => 'Teste102030',
    //         'password' => '',
    //         'password_confirmation' => 'Teste1020304050'
    //     ]))->assertSessionHasErrors([
    //         'password'
    //     ]);
    // }

    // /** @test 
    //  * 
    //  * Não pode editar a senha se a confirmação está vazia.
    // */
    // public function cannot_after_login_update_confirmation_empty()
    // {
    //     $user_externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.editar.view'))->assertOk();
    //     $this->get(route('externo.editar.senha.view'))->assertOk();
    //     $this->put(route('externo.editar', [
    //         'password_atual' => 'Teste102030',
    //         'password' => 'Teste1020304050',
    //         'password_confirmation' => ''
    //     ]))->assertSessionHasErrors([
    //         'password'
    //     ]);
    // }

    // /** 
    //  * =======================================================================================================
    //  * TESTES ABAS DE SERVIÇOS
    //  * =======================================================================================================
    //  */

    // /** @test 
    //  * 
    //  * Pode acessar todas as abas na área restrita do Portal.
    // */
    // public function after_login_can_access_tabs_on_restrict()
    // {
    //     $user_externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.dashboard'))->assertOk();
    //     $this->get(route('externo.editar.view'))->assertOk();
    //     $this->get(route('externo.editar.senha.view'))->assertOk();
    //     $this->get(route('externo.preregistro.view'))->assertOk();
    // }

    // /** @test 
    //  * 
    //  * Abas da área restrita não são acessíveis sem o login.
    // */
    // public function cannot_access_tabs_on_restrict_area_without_login()
    // {
    //     $this->get(route('externo.dashboard'))->assertRedirect(route('externo.login'));
    //     $this->get(route('externo.editar.view'))->assertRedirect(route('externo.login'));
    //     $this->get(route('externo.editar.senha.view'))->assertRedirect(route('externo.login'));
    //     $this->get(route('externo.preregistro.view'))->assertRedirect(route('externo.login'));
    // }

    // /** 
    //  * =======================================================================================================
    //  * TESTES PRE-REGISTRO CONTABIL
    //  * =======================================================================================================
    //  */

    // /** @test */
    // public function view_msg_update()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
    //     ->assertSeeText('Atualizado em: ')
    //     ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));

    //     PreRegistro::first()->update(['updated_at' => PreRegistro::first()->updated_at->subHour()]);
    //     $atual = PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s');

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'contabil',
    //         'campo' => 'cnpj_contabil',
    //         'valor' => '78087976000130'
    //     ])->assertStatus(200);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
    //     ->assertSeeText('Atualizado em: ')
    //     ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
    //     $this->assertNotEquals($atual, PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
    // }

    // /** 
    //  * =======================================================================================================
    //  * TESTES PRE-REGISTRO CONTABIL VIA AJAX - CLIENT
    //  * =======================================================================================================
    //  */

    // /** @test */
    // public function can_update_table_contabeis_by_ajax()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
    //     $contabil = factory('App\Contabil')->raw();
        
    //     foreach($contabil as $key => $value)
    //         $this->post(route('externo.inserir.preregistro.ajax'), [
    //             'classe' => 'contabil',
    //             'campo' => $key.'_contabil',
    //             'valor' => $value
    //         ])->assertStatus(200);
        
    //     $this->assertDatabaseHas('contabeis', $contabil);

    //     $this->assertDatabaseHas('pre_registros', [
    //         'contabil_id' => PreRegistro::first()->contabil_id
    //     ]);
    // }

    // /** @test */
    // public function can_update_table_contabeis_by_ajax_with_upperCase()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
    //     $contabil = factory('App\Contabil')->states('low')->raw();
        
    //     foreach($contabil as $key => $value)
    //         $this->post(route('externo.inserir.preregistro.ajax'), [
    //             'classe' => 'contabil',
    //             'campo' => $key.'_contabil',
    //             'valor' => $value
    //         ])->assertStatus(200);
        
    //     foreach($contabil as $key => $value)
    //         if(isset($value) && ($key != 'email'))
    //             $contabil[$key] = mb_strtoupper($value, 'UTF-8');

    //     $this->assertDatabaseHas('contabeis', $contabil);
    //     $this->assertDatabaseHas('pre_registros', [
    //         'contabil_id' => PreRegistro::first()->contabil_id
    //     ]);
    // }

    // /** @test */
    // public function can_update_table_contabeis_by_ajax_when_exists_others_pre_registros()
    // {
    //     $preRegistroCpf_1 = factory('App\PreRegistroCpf')->create([
    //         'dt_nascimento' => '1970-03-10',
    //         'pre_registro_id' => factory('App\PreRegistro')->create([
    //             'user_externo_id' => factory('App\UserExterno')->create([
    //                 'cpf_cnpj' => '69214841063'
    //             ])
    //         ])
    //     ]);

    //     $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
    //         'dt_nascimento' => '1975-10-15',
    //         'pre_registro_id' => factory('App\PreRegistro')->create([
    //             'contabil_id' => $preRegistroCpf_1->preRegistro->contabil_id,
    //             'user_externo_id' => factory('App\UserExterno')->create([
    //                 'cpf_cnpj' => '60923317058'
    //             ])
    //         ])
    //     ]);

    //     $externo = $this->signInAsUserExterno();
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
    //     $contabil = factory('App\Contabil')->raw([
    //         'cnpj' => '46217816000172'
    //     ]);
        
    //     foreach($contabil as $key => $value)
    //         $this->post(route('externo.inserir.preregistro.ajax'), [
    //             'classe' => 'contabil',
    //             'campo' => $key.'_contabil',
    //             'valor' => $value
    //         ])->assertStatus(200);

    //     $this->assertDatabaseHas('contabeis', $contabil);
    //     $this->assertDatabaseHas('pre_registros', $preRegistroCpf_1->preRegistro->toArray());
    //     $this->assertDatabaseHas('pre_registros', $preRegistroCpf_2->preRegistro->toArray());
    //     $this->assertDatabaseHas('pre_registros', [
    //         'contabil_id' => $externo->load('preRegistro')->preRegistro->contabil_id
    //     ]);
    // }

    // /** @test */
    // public function can_update_table_contabeis_by_ajax_when_exists_others_pre_registros_with_same_contabil()
    // {
    //     $preRegistroCpf_1 = factory('App\PreRegistroCpf')->create([
    //         'dt_nascimento' => '1970-03-10',
    //         'pre_registro_id' => factory('App\PreRegistro')->create([
    //             'user_externo_id' => factory('App\UserExterno')->create([
    //                 'cpf_cnpj' => '69214841063'
    //             ])
    //         ])
    //     ]);

    //     $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
    //         'dt_nascimento' => '1975-10-15',
    //         'pre_registro_id' => factory('App\PreRegistro')->create([
    //             'contabil_id' => $preRegistroCpf_1->preRegistro->contabil_id,
    //             'user_externo_id' => factory('App\UserExterno')->create([
    //                 'cpf_cnpj' => '60923317058'
    //             ])
    //         ])
    //     ]);

    //     $externo = $this->signInAsUserExterno();
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
    //     $contabil = factory('App\Contabil')->raw();
        
    //     foreach($contabil as $key => $value)
    //         $this->post(route('externo.inserir.preregistro.ajax'), [
    //             'classe' => 'contabil',
    //             'campo' => $key.'_contabil',
    //             'valor' => $value
    //         ])->assertStatus(200);

    //     $this->assertDatabaseHas('contabeis', $contabil);
    //     $this->assertDatabaseHas('pre_registros', $preRegistroCpf_1->preRegistro->attributesToArray());
    //     $this->assertDatabaseHas('pre_registros', $preRegistroCpf_2->preRegistro->attributesToArray());
    //     $this->assertDatabaseHas('pre_registros', [
    //         'contabil_id' => $externo->load('preRegistro')->preRegistro->contabil_id
    //     ]);
    // }

    // /** @test */
    // public function cannot_update_table_contabeis_by_ajax_wrong_input_name()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
    //     $contabil = factory('App\Contabil')->raw();
        
    //     foreach($contabil as $key => $value)
    //         $this->post(route('externo.inserir.preregistro.ajax'), [
    //             'classe' => 'contabil',
    //             'campo' => $key.'_erro',
    //             'valor' => $value
    //         ])->assertSessionHasErrors('campo');
        
    //     $this->assertDatabaseMissing('contabeis', $contabil);
    //     $this->assertDatabaseHas('pre_registros', [
    //         'contabil_id' => PreRegistro::first()->contabil_id
    //     ]);
    // }

    // /** @test */
    // public function cannot_update_table_contabeis_by_ajax_without_classe()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
    //     $contabil = factory('App\Contabil')->raw();
        
    //     foreach($contabil as $key => $value)
    //         $this->post(route('externo.inserir.preregistro.ajax'), [
    //             'classe' => '',
    //             'campo' => $key.'_contabil',
    //             'valor' => $value
    //         ])->assertSessionHasErrors('classe');
        
    //     $this->assertDatabaseMissing('contabeis', $contabil);
    //     $this->assertDatabaseHas('pre_registros', [
    //         'contabil_id' => PreRegistro::first()->contabil_id
    //     ]);
    // }

    // /** @test */
    // public function cannot_update_table_contabeis_by_ajax_wrong_classe()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
    //     $contabil = factory('App\Contabil')->raw();
        
    //     foreach($contabil as $key => $value)
    //         $this->post(route('externo.inserir.preregistro.ajax'), [
    //             'classe' => 'contabil_erro',
    //             'campo' => $key.'_contabil',
    //             'valor' => $value
    //         ])->assertSessionHasErrors('classe');
        
    //     $this->assertDatabaseMissing('contabeis', $contabil);
    //     $this->assertDatabaseHas('pre_registros', [
    //         'contabil_id' => PreRegistro::first()->contabil_id
    //     ]);
    // }

    // /** @test */
    // public function cannot_update_table_contabeis_by_ajax_without_campo()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
    //     $contabil = factory('App\Contabil')->raw();
        
    //     foreach($contabil as $key => $value)
    //         $this->post(route('externo.inserir.preregistro.ajax'), [
    //             'classe' => 'contabil',
    //             'campo' => '',
    //             'valor' => $value
    //         ])->assertSessionHasErrors('campo');
        
    //     $this->assertDatabaseMissing('contabeis', $contabil);
    //     $this->assertDatabaseHas('pre_registros', [
    //         'contabil_id' => PreRegistro::first()->contabil_id
    //     ]);
    // }

    // /** @test */
    // public function cannot_update_table_contabeis_by_ajax_with_input_type_text_more_191_chars()
    // {
    //     $faker = \Faker\Factory::create();
    //     $externo = $this->signInAsUserExterno();
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'contabil',
    //         'campo' => 'cnpj_contabil',
    //         'valor' => factory('App\Contabil')->raw()['cnpj']
    //     ]);

    //     $contabil = [
    //         'nome' => $faker->sentence(400),
    //         'email' => $faker->sentence(400),
    //         'nome_contato' => $faker->sentence(400),
    //         'telefone' => $faker->sentence(400),
    //     ];

    //     foreach($contabil as $key => $value)
    //         $this->post(route('externo.inserir.preregistro.ajax'), [
    //             'classe' => 'contabil',
    //             'campo' => $key . '_contabil',
    //             'valor' => $value
    //         ])->assertSessionHasErrors('valor');

    //     $this->assertDatabaseMissing('contabeis', $contabil);
    //     $this->assertDatabaseHas('pre_registros', [
    //         'contabil_id' => PreRegistro::first()->contabil_id
    //     ]);
    // }

    // /** @test */
    // public function cannot_update_table_contabeis_by_ajax_with_cnpj_wrong()
    // {
    //     $externo = $this->signInAsUserExterno();
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'contabil',
    //         'campo' => 'cnpj_contabil',
    //         'valor' => factory('App\Contabil')->raw()['cnpj'] . '4'
    //     ])->assertSessionHasErrors('valor');

    //     $this->assertDatabaseMissing('contabeis', [
    //         'cnpj' => factory('App\Contabil')->raw()['cnpj'] . '4'
    //     ]);
    //     $this->assertDatabaseHas('pre_registros', [
    //         'contabil_id' => null
    //     ]);
    // }

    // /** @test */
    // public function cannot_update_table_contabeis_by_ajax_without_relationship()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
    //     $contabil = factory('App\Contabil')->raw();
        
    //     foreach($contabil as $key => $value)
    //         if($key != 'cnpj')
    //             $this->post(route('externo.inserir.preregistro.ajax'), [
    //                 'classe' => 'contabil',
    //                 'campo' => $key.'_contabil',
    //                 'valor' => $value
    //             ])->assertOk();
        
    //     $this->assertDatabaseMissing('contabeis', $contabil);
    //     $this->assertDatabaseHas('pre_registros', [
    //         'contabil_id' => null
    //     ]);
    // }

    // /** @test */
    // public function cannot_update_table_contabeis_by_ajax_when_remove_relationship()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
    //     $contabil = factory('App\Contabil')->raw();
        
    //     foreach($contabil as $key => $value)
    //         $this->post(route('externo.inserir.preregistro.ajax'), [
    //             'classe' => 'contabil',
    //             'campo' => $key.'_contabil',
    //             'valor' => $value
    //         ])->assertOk();
        
    //     $this->assertDatabaseHas('contabeis', $contabil);
    //     $this->assertDatabaseHas('pre_registros', [
    //         'contabil_id' => PreRegistro::first()->contabil->id
    //     ]);

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'contabil',
    //         'campo' => 'cnpj_contabil',
    //         'valor' => ''
    //     ])->assertOk();

    //     $this->assertDatabaseHas('contabeis', $contabil);
    //     $this->assertDatabaseHas('pre_registros', [
    //         'contabil_id' => null
    //     ]);

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'contabil',
    //         'campo' => 'nome_contabil',
    //         'valor' => 'Novo Teste'
    //     ])->assertOk();

    //     $this->assertDatabaseHas('contabeis', $contabil);
    //     $this->assertDatabaseHas('pre_registros', [
    //         'contabil_id' => null
    //     ]);
    // }

    // /** @test */
    // public function get_contabil_by_ajax_when_exists()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
    //     $contabil = factory('App\Contabil')->create();

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'contabil',
    //         'campo' => 'cnpj_contabil',
    //         'valor' => $contabil->cnpj
    //     ])->assertJsonFragment($contabil->toArray());
    // }

    // /** @test */
    // public function cannot_update_table_contabeis_by_ajax_when_clean_inputs()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
    //     $contabil = factory('App\Contabil')->create();

    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'contabil',
    //         'campo' => 'cnpj_contabil',
    //         'valor' => $contabil->cnpj
    //     ])->assertOk();

    //     $this->assertDatabaseHas('pre_registros', [
    //         'contabil_id' => $contabil->id
    //     ]);

    //     $dados = $contabil->makeHidden(['id', 'created_at', 'updated_at', 'deleted_at'])->toArray();
    //     foreach($dados as $key => $value)
    //         $this->post(route('externo.inserir.preregistro.ajax'), [
    //             'classe' => 'contabil',
    //             'campo' => $key . '_contabil',
    //             'valor' => ''
    //         ])->assertOk();

    //     $this->assertDatabaseHas('contabeis', $contabil->toArray());
    //     $this->assertDatabaseHas('pre_registros', [
    //         'contabil_id' => null
    //     ]);
    // }

    // /** @test */
    // public function cannot_update_table_contabeis_by_ajax_with_status_different_aguardando_correcao_or_sendo_elaborado()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $contabil = factory('App\Contabil')->create();
    //     $preRegistro = factory('App\PreRegistro')->create([
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => $contabil->id
    //     ]);

    //     $contabilAjax = $contabil->makeHidden(['id', 'created_at', 'updated_at', 'deleted_at'])->toArray();        
    //     foreach(PreRegistro::getStatus() as $status)
    //     {
    //         $preRegistro->update(['status' => $status]);
    //         if(!in_array($status, [PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO]))
    //             foreach($contabilAjax as $key => $value)
    //                 $this->post(route('externo.inserir.preregistro.ajax'), [
    //                     'classe' => 'contabil',
    //                     'campo' => $key . '_contabil',
    //                     'valor' => ''
    //                 ])->assertStatus(401);
    //     }
    // }

    // /** @test */
    // public function can_update_table_contabeis_by_ajax_with_status_aguardando_correcao_or_sendo_elaborado()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $contabil = factory('App\Contabil')->create();
    //     $preRegistro = factory('App\PreRegistro')->create([
    //         'contabil_id' => $contabil->id
    //     ]);

    //     $contabilAjax = $contabil->makeHidden(['id', 'created_at', 'updated_at'])->toArray();        
    //     foreach([PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO] as $status)
    //     {
    //         $preRegistro->update(['status' => $status]);
    //         foreach($contabilAjax as $key => $value)
    //             $this->post(route('externo.inserir.preregistro.ajax'), [
    //                 'classe' => 'contabil',
    //                 'campo' => $key . '_contabil',
    //                 'valor' => ''
    //             ])->assertStatus(200);
    //     }
    // }

    // /** 
    //  * =======================================================================================================
    //  * TESTES PRE-REGISTRO CONTABIL VIA SUBMIT - CLIENT
    //  * =======================================================================================================
    //  */

    // /** @test */
    // public function view_message_errors_when_submit_with_cnpj()
    // {
    //     $externo = $this->signInAsUserExterno();
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->states('low')->create([
    //         'pre_registro_id' => factory('App\PreRegistro')->states('low')->create([
    //             'opcional_celular' => null
    //         ]),
    //     ]);

    //     $dados = [
    //         'cnpj_contabil' => '78087976000130',
    //         'nome_contabil' => null,
    //         'email_contabil' => null,
    //         'nome_contato_contabil' => null,
    //         'telefone_contabil' => null,
    //         'path' => null,
    //     ];

    //     $final = array_merge($dados, $preRegistroCpf->preRegistro->toArray(), $preRegistroCpf->toArray());
    //     $this->put(route('externo.verifica.inserir.preregistro'), $final)->assertStatus(302);

    //     $errors = session('errors');
    //     $keys = array();
    //     foreach($errors->messages() as $key => $value)
    //         array_push($keys, '<button class="btn btn-sm btn-link erroPreRegistro" value="' . $key . '">');

    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
    //     ->assertSeeText('Foram encontrados ' . count($errors->messages()) . ' erros:')
    //     ->assertSeeInOrder($keys);
    // }
    
    // /** @test */
    // public function can_submit_pre_registro_with_cnpj_contabil_exists()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
    //     $final = $preRegistroCpf->final;
    //     $prCpf = Arr::except($preRegistroCpf->toArray(), [
    //         'final','preRegistro','contabil'
    //     ]);
    //     $dados = array_merge($prCpf, $final);
    //     $pr = Arr::except($preRegistroCpf->preRegistro, [
    //         'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
    //     ]);
    //     $cont = $preRegistroCpf->contabil;
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => [UploadedFile::fake()->create('random.pdf')]
    //     ])->assertOk();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)->assertOk();

    //     $this->put(route('externo.inserir.preregistro'), $dados)
    //     ->assertRedirect(route('externo.preregistro.view'));

    //     foreach($cont as $key => $value)
    //         $cont[$key] = $key != 'email' ? mb_strtoupper($value, 'UTF-8') : $value;
    //     $this->assertDatabaseHas('contabeis', $cont);
    //     $this->assertEquals(Contabil::count(), 1);
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_cnpj_contabil_wrong()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
    //     $final = $preRegistroCpf->final;
    //     $prCpf = Arr::except($preRegistroCpf->toArray(), [
    //         'final','preRegistro','contabil'
    //     ]);
    //     $dados = array_merge($prCpf, $final);
    //     $pr = Arr::except($preRegistroCpf->preRegistro, [
    //         'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
    //     ]);
    //     $dados['cnpj_contabil'] = '01234567891023';

    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
    //     $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('cnpj_contabil');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_without_nome_contabil()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
    //     $final = $preRegistroCpf->final;
    //     $prCpf = Arr::except($preRegistroCpf->toArray(), [
    //         'final','preRegistro','contabil'
    //     ]);
    //     $dados = array_merge($prCpf, $final);
    //     $pr = Arr::except($preRegistroCpf->preRegistro, [
    //         'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
    //     ]);
    //     $dados['nome_contabil'] = '';
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
    //     $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('nome_contabil');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_nome_contabil_less_than_5_chars()
    // {
    //     $faker = \Faker\Factory::create();
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
    //     $final = $preRegistroCpf->final;
    //     $prCpf = Arr::except($preRegistroCpf->toArray(), [
    //         'final','preRegistro','contabil'
    //     ]);
    //     $dados = array_merge($prCpf, $final);
    //     $pr = Arr::except($preRegistroCpf->preRegistro, [
    //         'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
    //     ]);
    //     $dados['nome_contabil'] = 'Nome';
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
    //     $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('nome_contabil');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_nome_contabil_more_than_191_chars()
    // {
    //     $faker = \Faker\Factory::create();
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
    //     $final = $preRegistroCpf->final;
    //     $prCpf = Arr::except($preRegistroCpf->toArray(), [
    //         'final','preRegistro','contabil'
    //     ]);
    //     $dados = array_merge($prCpf, $final);
    //     $pr = Arr::except($preRegistroCpf->preRegistro, [
    //         'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
    //     ]);
    //     $dados['nome_contabil'] = $faker->sentence(400);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
    //     $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('nome_contabil');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_without_email_contabil()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
    //     $final = $preRegistroCpf->final;
    //     $prCpf = Arr::except($preRegistroCpf->toArray(), [
    //         'final','preRegistro','contabil'
    //     ]);
    //     $dados = array_merge($prCpf, $final);
    //     $pr = Arr::except($preRegistroCpf->preRegistro, [
    //         'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
    //     ]);
    //     $dados['email_contabil'] = '';
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
    //     $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('email_contabil');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_email_contabil_less_than_10_chars()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
    //     $final = $preRegistroCpf->final;
    //     $prCpf = Arr::except($preRegistroCpf->toArray(), [
    //         'final','preRegistro','contabil'
    //     ]);
    //     $dados = array_merge($prCpf, $final);
    //     $pr = Arr::except($preRegistroCpf->preRegistro, [
    //         'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
    //     ]);
    //     $dados['email_contabil'] = 'tes@.com';
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
    //     $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('email_contabil');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_email_contabil_more_than_191_chars()
    // {
    //     $faker = \Faker\Factory::create();
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
    //     $final = $preRegistroCpf->final;
    //     $prCpf = Arr::except($preRegistroCpf->toArray(), [
    //         'final','preRegistro','contabil'
    //     ]);
    //     $dados = array_merge($prCpf, $final);
    //     $pr = Arr::except($preRegistroCpf->preRegistro, [
    //         'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
    //     ]);
    //     $dados['email_contabil'] = $faker->sentence(400);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
    //     $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('email_contabil');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_email_wrong_value()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
    //     $final = $preRegistroCpf->final;
    //     $prCpf = Arr::except($preRegistroCpf->toArray(), [
    //         'final','preRegistro','contabil'
    //     ]);
    //     $dados = array_merge($prCpf, $final);
    //     $pr = Arr::except($preRegistroCpf->preRegistro, [
    //         'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
    //     ]);
    //     $dados['email_contabil'] = 'teste@.com';
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
    //     $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('email_contabil');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_without_nome_contato_contabil()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
    //     $final = $preRegistroCpf->final;
    //     $prCpf = Arr::except($preRegistroCpf->toArray(), [
    //         'final','preRegistro','contabil'
    //     ]);
    //     $dados = array_merge($prCpf, $final);
    //     $pr = Arr::except($preRegistroCpf->preRegistro, [
    //         'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
    //     ]);
    //     $dados['nome_contato_contabil'] = '';
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
    //     $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('nome_contato_contabil');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_nome_contato_contabil_less_than_5_chars()
    // {
    //     $faker = \Faker\Factory::create();
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
    //     $final = $preRegistroCpf->final;
    //     $prCpf = Arr::except($preRegistroCpf->toArray(), [
    //         'final','preRegistro','contabil'
    //     ]);
    //     $dados = array_merge($prCpf, $final);
    //     $pr = Arr::except($preRegistroCpf->preRegistro, [
    //         'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
    //     ]);
    //     $dados['nome_contato_contabil'] = 'Nome';
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
    //     $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('nome_contato_contabil');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_nome_contato_contabil_more_than_191_chars()
    // {
    //     $faker = \Faker\Factory::create();
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
    //     $final = $preRegistroCpf->final;
    //     $prCpf = Arr::except($preRegistroCpf->toArray(), [
    //         'final','preRegistro','contabil'
    //     ]);
    //     $dados = array_merge($prCpf, $final);
    //     $pr = Arr::except($preRegistroCpf->preRegistro, [
    //         'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
    //     ]);
    //     $dados['nome_contato_contabil'] = $faker->sentence(400);
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
    //     $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('nome_contato_contabil');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_nome_contato_contabil_with_numbers()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
    //     $final = $preRegistroCpf->final;
    //     $prCpf = Arr::except($preRegistroCpf->toArray(), [
    //         'final','preRegistro','contabil'
    //     ]);
    //     $dados = array_merge($prCpf, $final);
    //     $pr = Arr::except($preRegistroCpf->preRegistro, [
    //         'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
    //     ]);
    //     $dados['nome_contato_contabil'] = 'N0me C0ntato';
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
    //     $anexo = factory('App\Anexo')->states('pre_registro')->create();
       
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('nome_contato_contabil');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_without_telefone_contabil()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
    //     $final = $preRegistroCpf->final;
    //     $prCpf = Arr::except($preRegistroCpf->toArray(), [
    //         'final','preRegistro','contabil'
    //     ]);
    //     $dados = array_merge($prCpf, $final);
    //     $pr = Arr::except($preRegistroCpf->preRegistro, [
    //         'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
    //     ]);
    //     $dados['telefone_contabil'] = '';
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
    //     $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('telefone_contabil');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_telefone_contabil_less_than_14_chars()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
    //     $final = $preRegistroCpf->final;
    //     $prCpf = Arr::except($preRegistroCpf->toArray(), [
    //         'final','preRegistro','contabil'
    //     ]);
    //     $dados = array_merge($prCpf, $final);
    //     $pr = Arr::except($preRegistroCpf->preRegistro, [
    //         'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
    //     ]);
    //     $dados['telefone_contabil'] = '(11) 9888-862';
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
    //     $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('telefone_contabil');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_telefone_contabil_more_than_15_chars()
    // {
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
    //     $final = $preRegistroCpf->final;
    //     $prCpf = Arr::except($preRegistroCpf->toArray(), [
    //         'final','preRegistro','contabil'
    //     ]);
    //     $dados = array_merge($prCpf, $final);
    //     $pr = Arr::except($preRegistroCpf->preRegistro, [
    //         'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
    //     ]);
    //     $dados['telefone_contabil'] = '(11) 98889-86265';
        
    //     $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
    //     $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
    //     $this->put(route('externo.verifica.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('telefone_contabil');
    // }

    // /** 
    //  * =======================================================================================================
    //  * TESTES PRÉ-REGISTRO CONTÁBIL - ADMIN
    //  * =======================================================================================================
    //  */

    // /** @test */
    // public function view_pre_registro_contabil()
    // {
    //     $admin = $this->signInAsAdmin();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->create();
    //     $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL]);
    //     $contabil = $preRegistroCpf->preRegistro->contabil;
        
    //     $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
    //     ->assertSeeText(formataCpfCnpj($contabil->cnpj))
    //     ->assertSeeText($contabil->nome)
    //     ->assertSeeText($contabil->nome_contato)
    //     ->assertSeeText($contabil->email)
    //     ->assertSeeText($contabil->telefone);
    // }

    // /** @test */
    // public function view_text_justificado_contabil()
    // {
    //     $admin = $this->signInAsAdmin();

    //     $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
    //     $justificativas = $preRegistroCpf->preRegistro->getJustificativaArray();

    //     $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
    //     ->assertSeeText($justificativas['cnpj_contabil'])
    //     ->assertSeeText($justificativas['nome_contabil'])
    //     ->assertSeeText($justificativas['nome_contato_contabil'])
    //     ->assertSeeText($justificativas['email_contabil'])
    //     ->assertSeeText($justificativas['telefone_contabil']);
    // }
}
