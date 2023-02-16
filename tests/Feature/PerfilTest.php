<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerfilTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $perfil = factory('App\Perfil')->create();
        
        $this->get(route('perfis.lista'))->assertRedirect(route('login'));
        $this->get('/admin/usuarios/perfis/criar')->assertRedirect(route('login'));
        $this->post('/admin/usuarios/perfis/criar')->assertRedirect(route('login'));
        $this->get('/admin/usuarios/perfis/editar/'.$perfil->idperfil)->assertRedirect(route('login'));
        $this->put('/admin/usuarios/perfis/editar/'.$perfil->idperfil)->assertRedirect(route('login'));
        $this->delete('/admin/usuarios/perfis/apagar/'.$perfil->idperfil)->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');
        
        $perfil = factory('App\Perfil')->create();
        
        $this->get(route('perfis.lista'))->assertForbidden();
        $this->get('/admin/usuarios/perfis/criar')->assertForbidden();
        $this->post('/admin/usuarios/perfis/criar')->assertForbidden();
        $this->get('/admin/usuarios/perfis/editar/'.$perfil->idperfil)->assertForbidden();
        $this->put('/admin/usuarios/perfis/editar/'.$perfil->idperfil)->assertForbidden();
        $this->delete('/admin/usuarios/perfis/apagar/'.$perfil->idperfil)->assertForbidden();
    }

    /** @test */
    public function admin_can_access_links()
    {
        $this->signInAsAdmin();
        $perfil = factory('App\Perfil')->create();
        
        $this->get(route('perfis.lista'))->assertOk();
        $this->get('/admin/usuarios/perfis/criar')->assertOk();
        $this->post('/admin/usuarios/perfis/criar')->assertStatus(302);
        $this->get('/admin/usuarios/perfis/editar/'.$perfil->idperfil)->assertOk();
        $this->put('/admin/usuarios/perfis/editar/'.$perfil->idperfil)->assertStatus(302);
        $this->delete('/admin/usuarios/perfis/apagar/'.$perfil->idperfil)->assertStatus(302);
    }

    /** @test */
    public function can_change_password()
    {
        $user = $this->signInAsAdmin();

        $senha = $user->password;
        $this->get('/admin/perfil/senha')
        ->assertSee('<label for="password-text" class="m-0 p-0">Força da senha</label>')
        ->assertSee('<div class="progress" id="password-text"></div>')
        ->assertSee('<small><em>Em caso de senha fraca ou média, considere alterá-la para sua segurança.</em></small>')
        ->assertSuccessful();

        $this->put('/admin/perfil/senha', [
            'current-password' => 'Teste102030',
            'password' => 'TestePortal123@#$%&',
            'password_confirmation' => 'TestePortal123@#$%&', 
        ])->assertRedirect(route('admin.info'));

        $this->get(route('admin.info'))
        ->assertSeeText('Senha alterada com sucesso!');

        $this->assertNotEquals($user->fresh()->password, $senha);
    }

    // Mínimo 6 caracteres, com 1 letra maiúscula, 1 minúscula e 1 número.
    /** @test */
    public function cannot_change_password_with_wrong_regex()
    {
        $user = $this->signInAsAdmin();

        $senhas = ['esteortal', 'TestePortal', 'esteortal1', 'TESTEPORTAL1', '1234561', 'Test1'];

        foreach($senhas as $senha)
            $this->put('/admin/perfil/senha', [
                'current-password' => 'Teste102030',
                'password' => $senha,
                'password_confirmation' => $senha, 
            ])->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function log_is_generated_when_change_password()
    {
        $user = $this->signInAsAdmin();

        $this->put('/admin/perfil/senha', [
            'current-password' => 'Teste102030',
            'password' => 'TestePortal123@#$%&',
            'password_confirmation' => 'TestePortal123@#$%&', 
        ]);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $texto = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $texto .= $user->nome.' (usuário '.$user->idusuario.') alterou senha *perfil* (id: ' . $user->idusuario . ')';
        $this->assertStringContainsString($texto, $log);
    }
}
