<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Permissao;
use Illuminate\Support\Facades\Password;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $user = factory('App\User')->create();
        
        $this->get(route('usuarios.lista'))->assertRedirect(route('login'));
        $this->get('/admin/usuarios/busca')->assertRedirect(route('login'));
        $this->get('/admin/usuarios/criar')->assertRedirect(route('login'));
        $this->post('/admin/usuarios/criar')->assertRedirect(route('login'));
        $this->get('/admin/usuarios/editar/'.$user->idusuario)->assertRedirect(route('login'));
        $this->put('/admin/usuarios/editar/'.$user->idusuario)->assertRedirect(route('login'));
        $this->delete('/admin/usuarios/apagar/'.$user->idusuario)->assertRedirect(route('login'));
        $this->get('/admin/usuarios/lixeira')->assertRedirect(route('login'));
        $this->get('/admin/usuarios/restore/'.$user->idusuario)->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');

        $user = factory('App\User')->create();
        
        $this->get(route('usuarios.lista'))->assertForbidden();
        $this->get('/admin/usuarios/busca')->assertForbidden();
        $this->get('/admin/usuarios/criar')->assertForbidden();
        $this->post('/admin/usuarios/criar')->assertForbidden();
        $this->get('/admin/usuarios/editar/'.$user->idusuario)->assertForbidden();
        $this->put('/admin/usuarios/editar/'.$user->idusuario)->assertForbidden();
        $this->delete('/admin/usuarios/apagar/'.$user->idusuario)->assertForbidden();
        $this->get('/admin/usuarios/lixeira')->assertForbidden();
        $this->get('/admin/usuarios/restore/'.$user->idusuario)->assertForbidden();
    }

    /** @test */
    public function admin_can_access_links()
    {
        $this->signInAsAdmin();
        $this->assertAuthenticated('web');
        
        $user = factory('App\User')->create();
        
        $this->get(route('usuarios.lista'))->assertOk();
        $this->get('/admin/usuarios/busca')->assertOk();
        $this->get('/admin/usuarios/criar')->assertOk();
        $this->post('/admin/usuarios/criar')->assertStatus(302);
        $this->get('/admin/usuarios/editar/'.$user->idusuario)->assertOk();
        $this->put('/admin/usuarios/editar/'.$user->idusuario)->assertStatus(302);
        $this->delete('/admin/usuarios/apagar/'.$user->idusuario)->assertStatus(302);
        $this->get('/admin/usuarios/lixeira')->assertOk();
        $this->get('/admin/usuarios/restore/'.$user->idusuario)->assertStatus(302);
    }

    /** @test */
    public function authorized_users_can_list_users()
    {
        $admin = $this->signInAsAdmin();
        $user = factory('App\User')->create();

        $this->get(route('usuarios.lista'))
        ->assertOk()
        ->assertSeeText($user->username)
        ->assertSeeText($user->nome)
        ->assertSee('<a href="/admin/perfil/senha/'.$user->idusuario.'" class="btn btn-sm btn-warning">Trocar senha</a> ')
        ->assertSeeText($admin->username)
        ->assertSeeText($admin->nome)
        ->assertDontSee('<a href="/admin/perfil/senha/'.$admin->idusuario.'" class="btn btn-sm btn-warning">Trocar senha</a> ');
    }

    /** @test */
    public function log_is_generated_when_login_on_admin()
    {
        $user = factory('App\User')->create([
            'password' => bcrypt('TestePorta1@')
        ]);

        $this->get('admin/login')->assertOk();
        $this->post('admin/login', ['login' => $user->username, 'password' => 'TestePorta1@'])->assertRedirect(route('admin'));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome.' (usuário '.$user->idusuario.') conectou-se ao Painel Administrativo.', $log);
    }

    /** @test */
    public function log_is_generated_when_logout_on_admin()
    {
        $user = factory('App\User')->create([
            'password' => bcrypt('TestePorta1@')
        ]);

        $this->get('admin/login')->assertOk();
        $this->post('admin/login', ['login' => $user->username, 'password' => 'TestePorta1@'])->assertRedirect(route('admin'));
        $this->post(route('logout'))->assertRedirect('/');

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $texto = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $texto .= $user->nome.' (usuário '.$user->idusuario.') desconectou-se do Painel Administrativo.';
        $this->assertStringContainsString($texto, $log);
    }

    /** @test */
    public function log_is_generated_when_expired_session_and_logout_on_admin()
    {
        $this->post(route('logout'));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $texto = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $texto .= 'Sessão expirou / não há sessão ativa ao realizar o logout do Painel Administrativo.';
        $this->assertStringContainsString($texto, $log);
    }

    /** @test */
    public function log_is_generated_when_failed_login_on_admin()
    {
        $user = factory('App\User')->create([
            'password' => bcrypt('TestePorta1@')
        ]);

        $this->get('admin/login')->assertOk();
        $this->post('admin/login', ['login' => $user->username, 'password' => 'TestePorta1'])->assertRedirect('admin/login');

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $texto = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $texto .= $user->nome.' (usuário '.$user->idusuario.') não conseguiu logar no Painel Administrativo.';
        $this->assertStringContainsString($texto, $log);
    }

    /** @test */
    public function log_is_generated_when_failed_login_on_admin_and_username_not_find()
    {
        $user = factory('App\User')->create([
            'password' => bcrypt('TestePorta1@')
        ]);

        $this->get('admin/login')->assertOk();
        $this->post('admin/login', ['login' => $user->username.'p', 'password' => 'TestePorta1@'])->assertRedirect('admin/login');

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $texto = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $texto .= 'Usuário não encontrado com o username "'.request()->login.'" não conseguiu logar no Painel Administrativo.';
        $this->assertStringContainsString($texto, $log);
    }

    /** @test */
    public function log_is_generated_when_lockout_login_on_admin()
    {
        $user = factory('App\User')->create([
            'password' => bcrypt('TestePorta1@')
        ]);

        for($i = 0; $i < 4; $i++)
        {
            $this->get('admin/login')->assertOk();
            $this->post('admin/login', ['login' => $user->username, 'username' => $user->username, 'password' => 'TestePorta1']);
        }

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $texto = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $texto .= 'Usuário com username "'.request()->login.'" foi bloqueado temporariamente por alguns segundos devido a alcançar o limite de tentativas de login no Painel Administrativo.';
        $this->assertStringContainsString($texto, $log);
    }

    /** @test */
    public function log_is_generated_when_reset_password_on_admin()
    {
        $user = factory('App\User')->create([
            'password' => bcrypt('TestePorta1@')
        ]);
        $token = Password::createToken($user);

        $this->get(route('password.reset', $token))
        ->assertSee('<label for="password-text" class="m-0 p-0">Força da senha</label>')
        ->assertSee('<div class="progress" id="password-text"></div>')
        ->assertSee('<small><em>Em caso de senha fraca ou média, considere alterá-la para sua segurança.</em></small>')
        ->assertSuccessful();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
        ])->assertRedirect(route('admin'));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $texto = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $texto .= $user->nome.' (usuário '.$user->idusuario.') resetou a senha com sucesso em "Esqueci a senha" do Painel Administrativo.';
        $this->assertStringContainsString($texto, $log);
    }

    /** @test */
    public function log_is_generated_when_bot_try_login_on_admin()
    {
        $user = factory('App\User')->create([
            'password' => bcrypt('TestePorta1@')
        ]);

        $this->get('admin/login')->assertOk();
        $this->post('admin/login', ['login' => $user->username, 'password' => 'TestePorta1@', 'email_system' => '1'])
        ->assertRedirect('admin/login');

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $texto = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $texto .= 'Possível bot tentou login com username "' .$user->username. '", mas impedido de verificar o usuário no banco de dados.';
        $this->assertStringContainsString($texto, $log);
    }

    /** @test */
    public function same_ip_when_lockout_admin_by_csrf_token_can_login_on_portal()
    {
        $user = factory('App\User')->create([
            'password' => bcrypt('TestePorta1@')
        ]);
        $representante = factory('App\Representante')->create();

        $this->get('/')->assertOk();
        $csrf = csrf_token();

        for($i = 0; $i < 4; $i++)
        {
            $this->get('admin/login')->assertOk();
            $this->assertEquals($csrf, request()->session()->get('_token'));
            $this->post('admin/login', ['login' => 'Teste', 'password' => 'TestePorta1']);
            $this->assertEquals($csrf, request()->session()->get('_token'));
        }

        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste1020']);
        $this->assertEquals($csrf, request()->session()->get('_token'));
        $this->get(route('representante.login'))
        ->assertSee('Login inválido devido à quantidade de tentativas.');
        $this->assertEquals($csrf, request()->session()->get('_token'));

        request()->session()->regenerate();

        $this->get('admin/login')->assertOk();
        $this->post('admin/login', ['login' => $user->email, 'password' => 'TestePorta1@'])
        ->assertRedirect(route('admin'));
    }

    /** @test */
    public function cannot_view_form_when_bot_try_login_on_admin()
    {
        $user = factory('App\User')->create([
            'password' => bcrypt('TestePorta1@')
        ]);

        $this->get('admin/login')->assertOk();
        $this->post('admin/login', ['login' => $user->username, 'password' => 'TestePorta1@', 'email_system' => '1'])
        ->assertRedirect('admin/login');

        $this->get('admin/login')
        ->assertDontSee('<span class="fas fa-user input-group-text" style="line-height:1.5;"></span>')
        ->assertDontSee('<span class="fa fa-lock input-group-text" style="line-height:1.5;"></span>')
        ->assertDontSee('<button type="submit" class="btn btn-primary btn-block btn-flat">Entrar</button>');
    }

    /** @test */
    public function can_view_strength_bar_password_login_on_admin()
    {
        $user = factory('App\User')->create([
            'password' => bcrypt('TestePorta1@')
        ]);

        $this->get('admin/login')
        ->assertSee('<label for="password-text" class="m-0 p-0">Força da senha</label>')
        ->assertSee('<div class="progress" id="password-text"></div>')
        ->assertSee('<small><em>Em caso de senha fraca ou média, considere alterá-la para sua segurança.</em></small>');
    }

    /** @test */
    public function can_view_strength_bar_password_when_edit_password_admin()
    {
        $this->signIn();

        $this->get('admin/perfil/senha')
        ->assertSee('<label for="password-text" class="m-0 p-0">Força da senha</label>')
        ->assertSee('<div class="progress" id="password-text"></div>')
        ->assertSee('<small><em>Em caso de senha fraca ou média, considere alterá-la para sua segurança.</em></small>')
        ->assertOk();
    }
}
