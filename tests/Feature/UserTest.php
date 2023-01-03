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
        $this->assertStringContainsString($user->nome.' (usuário '.$user->idusuario.') desconectou-se do Painel Administrativo.', $log);
    }

    /** @test */
    public function log_is_generated_when_expired_session_and_logout_on_admin()
    {
        $this->post(route('logout'));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString('Sessão expirou / não há sessão ativa ao realizar o logout do Painel Administrativo.', $log);
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
        $this->assertStringContainsString($user->nome.' (usuário '.$user->idusuario.') não conseguiu logar no Painel Administrativo.', $log);
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
        $this->assertStringContainsString('Usuário não encontrado com o username "'.request()->login.'" não conseguiu logar no Painel Administrativo.', $log);
    }

    /** @test */
    public function log_is_generated_when_lockout_login_on_admin()
    {
        $user = factory('App\User')->create([
            'password' => bcrypt('TestePorta1@')
        ]);

        for($i = 0; $i < 6; $i++)
        {
            $this->get('admin/login')->assertOk();
            $this->post('admin/login', ['login' => $user->username, 'username' => $user->username, 'password' => 'TestePorta1']);
        }

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString('Usuário com username "'.request()->login.'" foi bloqueado temporariamente por alguns segundos devido a alcançar o limite de tentativas de login no Painel Administrativo.', $log);
    }

    /** @test */
    public function log_is_generated_when_reset_password_on_admin()
    {
        $user = factory('App\User')->create([
            'password' => bcrypt('TestePorta1@')
        ]);
        $token = Password::createToken($user);

        $this->get(route('password.reset', $token))->assertSuccessful();
        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'Teste102030', 
            'password_confirmation' => 'Teste102030', 
        ])->assertRedirect(route('admin'));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome.' (usuário '.$user->idusuario.') resetou a senha com sucesso em "Esqueci a senha" do Painel Administrativo.', $log);
    }
}
